<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RunPlatformHealthSuite extends Command
{
    protected $signature = 'vee:platform-health {--suite=daily : daily ou weekly}';
    protected $description = 'Executa a suite de testes do MMCloud Platform Health via n8n REST API';

    // n8n REST API
    private string $n8nBaseUrl;
    private string $n8nApiKey;
    private string $webhookUrl;

    // Configuração
    private int $pollIntervalMs = 3000;
    private int $maxPollSeconds = 60;

    public function handle(): int
    {
        $this->n8nBaseUrl = config('services.n8n.url');        // https://n8n.mmcriativos.cloud
        $this->n8nApiKey  = config('services.n8n.api_key');
        $this->webhookUrl = config('services.n8n.webhook_test_runner'); // POST webhook do test runner

        $suite = $this->option('suite');
        $this->info("Iniciando Platform Health Suite: {$suite}");

        $casos = $this->getCasos($suite);
        $results = [];

        foreach ($casos as $caso) {
            $result = $this->runCaso($caso);
            $results[] = $result;
            $this->line($this->formatLine($result));
            usleep($this->pollIntervalMs * 1000);
        }

        $this->saveResults($results, $suite);
        $this->handleNewFails($results);

        $pass     = count(array_filter($results, fn($r) => $r['status'] === 'pass'));
        $knownFail = count(array_filter($results, fn($r) => $r['status'] === 'known-fail'));
        $newFail  = count(array_filter($results, fn($r) => $r['status'] === 'new-fail'));
        $timeout  = count(array_filter($results, fn($r) => $r['status'] === 'timeout'));
        $skip     = count(array_filter($results, fn($r) => $r['status'] === 'skip'));

        $this->info("✅ Pass: {$pass} | ⚠️ Known-fail: {$knownFail} | 🔴 New-fail: {$newFail} | ⏱ Timeout: {$timeout} | ⤷ Skip: {$skip}");

        return $newFail > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function runCaso(array $caso): array
    {
        if ($caso['skip'] ?? false) {
            return array_merge($caso, ['status' => 'skip', 'detail' => $caso['skipReason'] ?? '']);
        }

        try {
            // 1. Dispara o webhook do test runner
            // Payload esperado pelo Set Defaults do Test Runner:
            // target, tenant_slug, user_message, remoteJid, pushName, idMessage
            $response = Http::timeout(15)->post($this->webhookUrl, [
                'target'      => $caso['target'],
                'tenant_slug' => $caso['tenant_slug'],
                'user_message'=> $caso['message'],
                'remoteJid'   => '11958469546@s.whatsapp.net',
                'pushName'    => 'Tester',
                'idMessage'   => 'TEST-' . $caso['id'] . '-' . time(),
            ]);

            if (!$response->successful()) {
                return array_merge($caso, [
                    'status' => 'new-fail',
                    'detail' => "Webhook retornou HTTP {$response->status()}",
                ]);
            }

            $executionId = $response->json('executionId') ?? null;

            if (!$executionId) {
                return array_merge($caso, [
                    'status' => 'new-fail',
                    'detail' => 'Webhook não retornou executionId',
                ]);
            }

            // 2. Polling da execução via REST API
            $status = $this->pollExecution($executionId);

            return array_merge($caso, $status);

        } catch (\Exception $e) {
            return array_merge($caso, [
                'status' => 'new-fail',
                'detail' => "Erro: {$e->getMessage()}",
            ]);
        }
    }

    private function pollExecution(string $executionId): array
    {
        $start = time();

        while ((time() - $start) < $this->maxPollSeconds) {
            sleep(intval($this->pollIntervalMs / 1000));

            try {
                $response = Http::withHeaders([
                    'X-N8N-API-KEY' => $this->n8nApiKey,
                ])->timeout(10)->get("{$this->n8nBaseUrl}/api/v1/executions/{$executionId}");

                if (!$response->successful()) {
                    continue;
                }

                $execution = $response->json();
                $execStatus = $execution['status'] ?? 'unknown';

                if (!in_array($execStatus, ['running', 'waiting'])) {
                    return $this->classifyExecution($execution);
                }

            } catch (\Exception $e) {
                Log::warning("PlatformHealth polling error: {$e->getMessage()}");
            }
        }

        return ['status' => 'timeout', 'detail' => "Execução não finalizou em {$this->maxPollSeconds}s"];
    }

    private function classifyExecution(array $execution): array
    {
        $execStatus = $execution['status'] ?? 'unknown';
        $data = $execution['data'] ?? [];

        // Critério de sucesso: chegou ao node "Call 'Vee - Send Text'" (qualquer variante)
        $lastNode = $this->getLastNode($data);

        if ($execStatus === 'error') {
            $errorMsg = $data['resultData']['error']['message'] ?? 'Erro desconhecido';
            return ['status' => 'new-fail', 'detail' => "Erro na execução: {$errorMsg}", 'lastNode' => $lastNode];
        }

        if (str_contains(strtolower($lastNode), 'send text') || str_contains(strtolower($lastNode), 'vee - send')) {
            return ['status' => 'pass', 'detail' => "Chegou ao Send Text", 'lastNode' => $lastNode];
        }

        // Verifica known-fails
        $knownFailNodes = ['Save Process', 'cdbKOKD6EsPXU'];
        foreach ($knownFailNodes as $known) {
            if (str_contains($lastNode, $known)) {
                return ['status' => 'known-fail', 'detail' => "Known-fail em: {$lastNode}", 'lastNode' => $lastNode];
            }
        }

        return ['status' => 'new-fail', 'detail' => "Não chegou ao Send Text. Último node: {$lastNode}", 'lastNode' => $lastNode];
    }

    private function getLastNode(array $data): string
    {
        $resultData = $data['resultData'] ?? [];
        $runData = $resultData['runData'] ?? [];
        if (empty($runData)) return 'desconhecido';
        $nodes = array_keys($runData);
        return end($nodes) ?: 'desconhecido';
    }

    private function saveResults(array $results, string $suite): void
    {
        $date = Carbon::now()->format('Y-m-d');
        $pass     = count(array_filter($results, fn($r) => $r['status'] === 'pass'));
        $knownFail = count(array_filter($results, fn($r) => $r['status'] === 'known-fail'));
        $newFail  = count(array_filter($results, fn($r) => $r['status'] === 'new-fail'));
        $timeout  = count(array_filter($results, fn($r) => $r['status'] === 'timeout'));
        $skip     = count(array_filter($results, fn($r) => $r['status'] === 'skip'));

        $md = "# Platform Health — {$date} ({$suite})\n\n";
        $md .= "**Resultado:** ✅ {$pass} pass | ⚠️ {$knownFail} known-fail | 🔴 {$newFail} new-fail | ⏱ {$timeout} timeout | ⤷ {$skip} skip\n\n";
        $md .= "## Casos\n\n";
        $md .= "| ID | Cenário | Status | Detalhe | Último Node |\n";
        $md .= "|---|---|---|---|---|\n";

        foreach ($results as $r) {
            $icon = match($r['status']) {
                'pass'       => '✅',
                'known-fail' => '⚠️',
                'new-fail'   => '🔴',
                'timeout'    => '⏱',
                'skip'       => '⤷',
                default      => '❓',
            };
            $id       = $r['id'] ?? '-';
            $scenario = addslashes($r['scenario'] ?? '-');
            $detail   = addslashes($r['detail'] ?? '-');
            $lastNode = addslashes($r['lastNode'] ?? '-');
            $md .= "| {$id} | {$scenario} | {$icon} {$r['status']} | {$detail} | {$lastNode} |\n";
        }

        if ($newFail > 0) {
            $md .= "\n## ⚠️ Ação Necessária\n\n";
            foreach ($results as $r) {
                if ($r['status'] === 'new-fail') {
                    $md .= "- **{$r['id']}** — {$r['scenario']}: {$r['detail']}\n";
                }
            }
        }

        // Salva via Obsidian MCP (POST para o vee-mcp)
        $this->saveToObsidian("MMCloud/testes/resultados-{$date}.md", $md, "Platform Health {$date}");

        // Salva também localmente em storage/logs para debug
        Storage::disk('local')->put("platform-health/resultados-{$date}.json", json_encode($results, JSON_PRETTY_PRINT));
    }

    private function handleNewFails(array $results): void
    {
        $newFails = array_filter($results, fn($r) => $r['status'] === 'new-fail');
        if (empty($newFails)) return;

        // Busca o próximo número de BUG disponível
        $bugNum = $this->getNextBugNumber();

        foreach ($newFails as $r) {
            $bugId   = sprintf('BUG-%03d', $bugNum);
            $date    = Carbon::now()->format('Y-m-d');
            $lastNode = $r['lastNode'] ?? 'desconhecido';
            $content = "# {$bugId} — {$r['scenario']}\n\n";
            $content .= "**Data:** {$date}\n";
            $content .= "**Status:** ❌ Aberto\n";
            $content .= "**Severidade:** Alta\n";
            $content .= "**Detectado por:** Platform Health Suite (automático)\n\n";
            $content .= "## Descrição\n\n{$r['detail']}\n\n";
            $content .= "## Caso de Teste\n\n";
            $content .= "- **ID:** {$r['id']}\n";
            $content .= "- **Cenário:** {$r['scenario']}\n";
            $content .= "- **Tenant:** {$r['tenant_slug']}\n";
            $content .= "- **Último node:** {$lastNode}\n\n";
            $content .= "## Próximos Passos\n\n- [ ] Investigar no n8n via REST API\n- [ ] Corrigir\n- [ ] Re-rodar caso de teste para confirmar\n";

            $path = "tecnico/bugs/{$bugId}-" . Str::slug($r['scenario']) . ".md";
            $this->saveToObsidian($path, $content, $bugId);
            $this->warn("🔴 BUG criado: {$bugId} — {$r['scenario']}");
            $bugNum++;
        }
    }

    private function saveToObsidian(string $path, string $content, string $title): void
    {
        try {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.vee_mcp.token'),
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post(config('services.vee_mcp.url') . '/mcp', [
                'tool'   => 'vee.obsidian.create_note',
                'params' => compact('path', 'content', 'title'),
            ]);
        } catch (\Exception $e) {
            Log::warning("Obsidian save failed: {$e->getMessage()}");
            // Não quebra o comando se o Obsidian falhar
        }
    }

    private function getNextBugNumber(): int
    {
        // Lê o README de bugs para descobrir o próximo número
        // Fallback: começa em 004 (001, 002, 003 já existem)
        return 4;
    }

    private function formatLine(array $r): string
    {
        $icon = match($r['status']) {
            'pass'       => '✅',
            'known-fail' => '⚠️',
            'new-fail'   => '🔴',
            'timeout'    => '⏱',
            'skip'       => '⤷',
            default      => '❓',
        };
        return "{$icon} [{$r['id']}] {$r['scenario']} — {$r['detail']}";
    }

    private function getCasos(string $suite): array
    {
        // Suite diária: grupos A, B, C, D, E, K, L + M parcial
        // Suite semanal: todos os 81 casos
        // Retorna array com os casos — ver casos-de-teste.md para lista completa

        $daily = $this->getDailyCasos();
        if ($suite === 'weekly') {
            return array_merge($daily, $this->getWeeklyCasos());
        }
        return $daily;
    }

    private function getDailyCasos(): array
    {
        return [
            // GRUPO A — Onboarding — target: atendimento (testa RAG, saudação, contexto)
            ['id'=>'A-001','scenario'=>'Cliente novo — primeira mensagem','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Olá, quero saber sobre vocês'],
            ['id'=>'A-002','scenario'=>'Cliente novo — já diz o nome','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Oi, sou a Maria, quero agendar'],
            ['id'=>'A-003','scenario'=>'Cliente novo — não diz o nome','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Oi'],
            ['id'=>'A-004','scenario'=>'Responde com nome após ser perguntado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Maria'],

            // GRUPO B — Intent — target: atendimento (classifica intenção e roteia)
            ['id'=>'B-001','scenario'=>'Intent: booking','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Quero marcar um horário'],
            ['id'=>'B-002','scenario'=>'Intent: informação/RAG','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Quais serviços vocês oferecem?'],
            ['id'=>'B-003','scenario'=>'Intent: small talk','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Oi, tudo bem?'],
            ['id'=>'B-004','scenario'=>'Intent: commercial intelligence','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Vocês têm desconto?'],
            ['id'=>'B-005','scenario'=>'Mensagem ambígua','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Hmm'],

            // GRUPO C — Fluxo atendimento — target: atendimento
            ['id'=>'C-001','scenario'=>'Cliente retornando — contexto recuperado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Olá de novo'],
            ['id'=>'C-002','scenario'=>'Memory update detectado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Pode anotar que prefiro horários de manhã'],
            ['id'=>'C-003','scenario'=>'Salvar mensagens inbound/outbound','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Teste de save'],
            ['id'=>'C-004','scenario'=>'Token Count registrado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Quero informações'],

            // GRUPO D — Entity Resolver — target: agendamento
            ['id'=>'D-001','scenario'=>'Serviço identificado por nome exato','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação gratuita'],
            ['id'=>'D-002','scenario'=>'Serviço com múltiplos candidatos','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação'],
            ['id'=>'D-006','scenario'=>'Data em linguagem natural — amanhã','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar para amanhã'],
            ['id'=>'D-007','scenario'=>'Horário em linguagem natural','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Às 14h'],
            ['id'=>'D-008','scenario'=>'Sem nenhuma entidade','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar'],

            // GRUPO E — Missing Fields — target: agendamento
            ['id'=>'E-001','scenario'=>'Falta serviço','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar para amanhã às 10h'],
            ['id'=>'E-002','scenario'=>'Falta data','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação gratuita às 10h'],
            ['id'=>'E-003','scenario'=>'Falta horário','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação gratuita amanhã'],
            ['id'=>'E-007','scenario'=>'Usuário envia tudo em uma mensagem','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação gratuita, amanhã às 10h'],

            // GRUPO K — Save Process — target: agendamento
            ['id'=>'K-001','scenario'=>'Save missing fields — todos os 4 nodes','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar'],
            ['id'=>'K-002','scenario'=>'Save disambiguous question','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação'],

            // GRUPO L — Hub/Roteamento — target: atendimento
            ['id'=>'L-001','scenario'=>'Mensagem de texto simples','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Olá'],
            ['id'=>'L-002','scenario'=>'Mensagem própria (fromMe)','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Teste'],
            ['id'=>'L-004','scenario'=>'Roteamento para MMCloud','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Olá'],

            // GRUPO M — mmbeauty parcial — target: agendamento
            ['id'=>'M-001','scenario'=>'Desambiguação entre 6 profissionais','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero agendar'],
            ['id'=>'M-002','scenario'=>'Profissional turno manhã','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero agendar de manhã'],
            ['id'=>'M-003','scenario'=>'Profissional turno tarde','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero agendar de tarde'],
            ['id'=>'M-005','scenario'=>'Serviço domicílio','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero Corte e Barba em Domicílio'],
            ['id'=>'M-009','scenario'=>'Deadline zero — cancelamento sempre permitido','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero cancelar meu agendamento'],
            ['id'=>'M-010','scenario'=>'Deadline zero — reagendamento sempre permitido','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero remarcar meu horário'],
        ];
    }

    private function getWeeklyCasos(): array
    {
        // Casos adicionais da suite semanal (grupos F, G, H, I, J, D completo, M completo)
        // veetest-b e veetest-c necessários para grupos F, G-002, H-007, I-002, J
        return [
            // GRUPO F — Blocking — target: agendamento (veetest-c)
            ['id'=>'F-001','scenario'=>'PreCheck OK — todos campos válidos','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Avaliação Rápida amanhã às 10h'],
            ['id'=>'F-002','scenario'=>'Blocked: sem disponibilidade no horário','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Avaliação Rápida amanhã às 11h30'],
            ['id'=>'F-003','scenario'=>'Blocked: profissional indisponível','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero com o Prof Alpha às 11h'],
            ['id'=>'F-005','scenario'=>'Blocked: serviço não disponível na data','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Avaliação Inicial amanhã às 10h'],

            // GRUPO G — Criação agendamento
            ['id'=>'G-001','scenario'=>'Agendamento presencial','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação gratuita amanhã às 10h'],
            ['id'=>'G-002','scenario'=>'Agendamento virtual (Google Meet)','target'=>'agendamento','tenant_slug'=>'veetest-b','message'=>'Consulta Virtual amanhã às 10h'],
            ['id'=>'G-009','scenario'=>'Mensagem de confirmação enviada','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Avaliação gratuita amanhã às 10h'],

            // GRUPO H — Reagendamento
            ['id'=>'H-001','scenario'=>'Reagendamento com ID explícito','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero remarcar o agendamento'],
            ['id'=>'H-007','scenario'=>'Reagendamento fora do prazo','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero remarcar'],

            // GRUPO I — Cancelamento
            ['id'=>'I-001','scenario'=>'Cancelamento dentro do prazo','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero cancelar'],
            ['id'=>'I-002','scenario'=>'Cancelamento fora do prazo','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero cancelar'],

            // GRUPO J — Desambiguação (veetest-c)
            ['id'=>'J-002','scenario'=>'Múltiplos serviços — desambiguação','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero Avaliação'],
            ['id'=>'J-003','scenario'=>'Múltiplos profissionais — desambiguação','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero agendar'],

            // GRUPO M — mmbeauty semanal completo
            ['id'=>'M-004','scenario'=>'Profissional manhã indisponível à tarde','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero com o Lucas às 16h'],
            ['id'=>'M-006','scenario'=>'Serviço longo 150min','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero Platinado amanhã'],
            ['id'=>'M-007','scenario'=>'Serviço curto 10min','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Sobrancelha amanhã às 9h'],
            ['id'=>'M-008','scenario'=>'Sábado — unidade encerra às 14h','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero agendar sábado às 15h'],
            ['id'=>'M-011','scenario'=>'Desambiguação de serviço (nomes parecidos)','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero hidratação'],
            ['id'=>'M-012','scenario'=>'Plano mensal com preço real','target'=>'agendamento','tenant_slug'=>'mmbeauty','message'=>'Quero o plano de corte'],
        ];
    }
}
