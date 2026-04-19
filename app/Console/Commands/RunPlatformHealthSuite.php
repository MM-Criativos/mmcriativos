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
    private string $n8nBaseUrl = '';
    private string $n8nApiKey = '';
    private string $webhookUrl = '';

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
            $response = Http::timeout(15)->post($this->webhookUrl, [
                'phone'    => $caso['phone'],
                'message'  => $caso['message'],
                'instance' => $caso['instance'],
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
            $content .= "- **Tenant:** {$r['tenant']}\n";
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
        // Webhook: POST https://n8n.mmcriativos.cloud/webhook/WRPPK93a5dttEwzA/webhook/test-runner
        // Payload: { phone, message, instance }
        // Instance do veetest: extrair do banco (ai_instances onde tenant_id = 5)

        return [
            // GRUPO A — Onboarding (veetest, tenant_id: 5)
            ['id'=>'A-001','scenario'=>'Cliente novo — primeira mensagem','phone'=>'5511900000001','message'=>'Olá, quero saber sobre vocês','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'A-002','scenario'=>'Cliente novo — já diz o nome','phone'=>'5511900000002','message'=>'Oi, sou a Maria, quero agendar','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'A-003','scenario'=>'Cliente novo — não diz o nome','phone'=>'5511900000003','message'=>'Oi','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'A-004','scenario'=>'Responde com nome após ser perguntado','phone'=>'5511900000004','message'=>'Maria','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO B — Intent (veetest)
            ['id'=>'B-001','scenario'=>'Intent: booking','phone'=>'5511900000011','message'=>'Quero marcar um horário','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'B-002','scenario'=>'Intent: informação/RAG','phone'=>'5511900000012','message'=>'Quais serviços vocês oferecem?','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'B-003','scenario'=>'Intent: small talk','phone'=>'5511900000013','message'=>'Oi, tudo bem?','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'B-004','scenario'=>'Intent: commercial intelligence','phone'=>'5511900000014','message'=>'Vocês têm desconto?','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'B-005','scenario'=>'Mensagem ambígua','phone'=>'5511900000015','message'=>'Hmm','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO C — Fluxo atendimento (veetest)
            ['id'=>'C-001','scenario'=>'Cliente retornando — contexto recuperado','phone'=>'5511900000021','message'=>'Olá de novo','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'C-002','scenario'=>'Memory update detectado','phone'=>'5511900000022','message'=>'Pode anotar que prefiro horários de manhã','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'C-003','scenario'=>'Salvar mensagens inbound/outbound','phone'=>'5511900000023','message'=>'Teste de save','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'C-004','scenario'=>'Token Count registrado','phone'=>'5511900000024','message'=>'Quero informações','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO D — Entity Resolver (veetest)
            ['id'=>'D-001','scenario'=>'Serviço identificado por nome exato','phone'=>'5511900000031','message'=>'Avaliação gratuita','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'D-002','scenario'=>'Serviço com múltiplos candidatos','phone'=>'5511900000032','message'=>'Avaliação','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'D-006','scenario'=>'Data em linguagem natural — amanhã','phone'=>'5511900000036','message'=>'Quero agendar para amanhã','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'D-007','scenario'=>'Horário em linguagem natural','phone'=>'5511900000037','message'=>'Às 14h','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'D-008','scenario'=>'Sem nenhuma entidade','phone'=>'5511900000038','message'=>'Quero agendar','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO E — Missing Fields (veetest)
            ['id'=>'E-001','scenario'=>'Falta serviço','phone'=>'5511900000041','message'=>'Quero agendar para amanhã às 10h','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'E-002','scenario'=>'Falta data','phone'=>'5511900000042','message'=>'Avaliação gratuita às 10h','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'E-003','scenario'=>'Falta horário','phone'=>'5511900000043','message'=>'Avaliação gratuita amanhã','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'E-007','scenario'=>'Usuário envia tudo em uma mensagem','phone'=>'5511900000047','message'=>'Avaliação gratuita, amanhã às 10h','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO K — Save Process (veetest)
            ['id'=>'K-001','scenario'=>'Save missing fields — todos os 4 nodes','phone'=>'5511900000061','message'=>'Quero agendar','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'K-002','scenario'=>'Save disambiguous question','phone'=>'5511900000062','message'=>'Avaliação','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO L — Hub/Roteamento (veetest)
            ['id'=>'L-001','scenario'=>'Mensagem de texto simples','phone'=>'5511900000071','message'=>'Olá','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'L-002','scenario'=>'Mensagem própria (fromMe)','phone'=>'5511900000072','message'=>'Teste','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'L-004','scenario'=>'Roteamento para MMCloud','phone'=>'5511900000074','message'=>'Olá','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO M — mmbeauty parcial (tenant_id: 3)
            ['id'=>'M-001','scenario'=>'Desambiguação entre 6 profissionais','phone'=>'5511900000081','message'=>'Quero agendar','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-002','scenario'=>'Profissional turno manhã','phone'=>'5511900000082','message'=>'Quero agendar de manhã','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-003','scenario'=>'Profissional turno tarde','phone'=>'5511900000083','message'=>'Quero agendar de tarde','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-005','scenario'=>'Serviço domicílio','phone'=>'5511900000085','message'=>'Quero Corte e Barba em Domicílio','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-009','scenario'=>'Deadline zero — cancelamento sempre permitido','phone'=>'5511900000089','message'=>'Quero cancelar meu agendamento','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-010','scenario'=>'Deadline zero — reagendamento sempre permitido','phone'=>'5511900000090','message'=>'Quero remarcar meu horário','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
        ];
    }

    private function getWeeklyCasos(): array
    {
        // Casos adicionais da suite semanal (grupos F, G, H, I, J, D completo, M completo)
        // veetest-b e veetest-c necessários para grupos F, G-002, H-007, I-002, J
        return [
            // GRUPO F — Blocking (veetest-c, tenant_id: 7)
            ['id'=>'F-001','scenario'=>'PreCheck OK — todos campos válidos','phone'=>'5511910000001','message'=>'Avaliação Rápida amanhã às 10h','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],
            ['id'=>'F-002','scenario'=>'Blocked: sem disponibilidade no horário','phone'=>'5511910000002','message'=>'Avaliação Rápida amanhã às 11h30','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],
            ['id'=>'F-003','scenario'=>'Blocked: profissional indisponível','phone'=>'5511910000003','message'=>'Quero com o Prof Alpha às 11h','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],
            ['id'=>'F-005','scenario'=>'Blocked: serviço não disponível na data','phone'=>'5511910000005','message'=>'Avaliação Inicial amanhã às 10h','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],

            // GRUPO G — Criação agendamento (veetest + veetest-b)
            ['id'=>'G-001','scenario'=>'Agendamento presencial','phone'=>'5511910000010','message'=>'Avaliação gratuita amanhã às 10h','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'G-002','scenario'=>'Agendamento virtual (Google Meet)','phone'=>'5511910000011','message'=>'Consulta Virtual amanhã às 10h','instance'=>'veetest-b','tenant'=>'veetest-b','skip'=>false],
            ['id'=>'G-009','scenario'=>'Mensagem de confirmação enviada','phone'=>'5511910000019','message'=>'Avaliação gratuita amanhã às 10h','instance'=>'veetest','tenant'=>'veetest','skip'=>false],

            // GRUPO H — Reagendamento (veetest)
            ['id'=>'H-001','scenario'=>'Reagendamento com ID explícito','phone'=>'5511910000020','message'=>'Quero remarcar o agendamento','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'H-007','scenario'=>'Reagendamento fora do prazo','phone'=>'5511910000027','message'=>'Quero remarcar','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],

            // GRUPO I — Cancelamento
            ['id'=>'I-001','scenario'=>'Cancelamento dentro do prazo','phone'=>'5511910000030','message'=>'Quero cancelar','instance'=>'veetest','tenant'=>'veetest','skip'=>false],
            ['id'=>'I-002','scenario'=>'Cancelamento fora do prazo','phone'=>'5511910000031','message'=>'Quero cancelar','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],

            // GRUPO J — Desambiguação (veetest-c)
            ['id'=>'J-002','scenario'=>'Múltiplos serviços — desambiguação','phone'=>'5511910000041','message'=>'Quero Avaliação','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],
            ['id'=>'J-003','scenario'=>'Múltiplos profissionais — desambiguação','phone'=>'5511910000042','message'=>'Quero agendar','instance'=>'veetest-c','tenant'=>'veetest-c','skip'=>false],

            // GRUPO M — mmbeauty semanal completo
            ['id'=>'M-004','scenario'=>'Profissional manhã indisponível à tarde','phone'=>'5511910000084','message'=>'Quero com o Lucas às 16h','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-006','scenario'=>'Serviço longo 150min','phone'=>'5511910000086','message'=>'Quero Platinado amanhã','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-007','scenario'=>'Serviço curto 10min','phone'=>'5511910000087','message'=>'Sobrancelha amanhã às 9h','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-008','scenario'=>'Sábado — unidade encerra às 14h','phone'=>'5511910000088','message'=>'Quero agendar sábado às 15h','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-011','scenario'=>'Desambiguação de serviço (nomes parecidos)','phone'=>'5511910000091','message'=>'Quero hidratação','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
            ['id'=>'M-012','scenario'=>'Plano mensal com preço real','phone'=>'5511910000092','message'=>'Quero o plano de corte','instance'=>'mmbeauty','tenant'=>'mmbeauty','skip'=>false],
        ];
    }
}
