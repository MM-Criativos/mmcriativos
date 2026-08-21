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
    protected $signature = 'vee:platform-health {--suite=daily : daily ou weekly} {--group= : letra do grupo (A..M)}';
    protected $description = 'Executa a suite de testes do MMCloud Platform Health via n8n REST API';

    // n8n REST API
    private string $n8nBaseUrl;
    private string $n8nApiKey;
    private string $webhookUrl;

    // ConfiguraÃ§Ã£o
    private int $maxPollSeconds = 60;

    // Workflow IDs para encontrar execuÃ§Ãµes por timestamp
    // target=atendimento â†’ polling no Agente de Atendimento
    // target=agendamento â†’ polling no Agente de Agendamento
    // target=full â†’ Hub chama Atendimento como sub-workflow â†’ polling no Atendimento
    private array $targetWorkflowMap = [
        'atendimento' => 'AcTwVVZ86JUuDcX_byb3B',
        'agendamento' => '8mxQiOUi87vJiO4I',
        'full'        => 'AcTwVVZ86JUuDcX_byb3B', // Hub â†’ Atendimento (sub-workflow)
    ];

    private array $agendamentoTenantCreds = [
        'veetest' => [
            'tenant_id' => 5,
            'tenant_api_token' => 'cb58831c45215bad4afe21402e238b23c3540daee73e82aafa13be491dab7e4d',
            'client_id' => 6,
        ],
        'veetest-b' => [
            'tenant_id' => 6,
            'tenant_api_token' => '373bc31cc54ae92e5ff7ff841463896fc9a33f0b1f31d5de96a95c05171a',
            'client_id' => 8,
        ],
        'veetest-c' => [
            'tenant_id' => 7,
            'tenant_api_token' => '47e01d7aebf248ec658529781129c3e3c610a1bc03cfc94da0e9ecb3bd7a812f',
            'client_id' => 7,
        ],
    ];

    public function handle(): int
    {
        $this->n8nBaseUrl = config('services.n8n.url');        // https://n8n.mmcriativos.cloud
        $this->n8nApiKey  = config('services.n8n.api_key');
        $this->webhookUrl = config('services.n8n.webhook_test_runner'); // POST webhook do test runner

        $suite = $this->option('suite');
        $this->info("Iniciando Platform Health Suite: {$suite}");

        $casos = $this->getCasos($suite);
        $group = strtoupper((string) $this->option('group'));
        if ($group !== '') {
            $casos = array_values(array_filter($casos, fn($caso) => str_starts_with(($caso['id'] ?? ''), "{$group}-")));
            $this->info("Filtro de grupo aplicado: {$group} (" . count($casos) . " casos)");
        }

        if (empty($casos)) {
            $this->error('Nenhum caso encontrado com os filtros informados.');
            return Command::FAILURE;
        }

        $results = [];

        foreach ($casos as $caso) {
            $result = $this->runCaso($caso);
            $results[] = $result;
            $this->line($this->formatLine($result));
            sleep(2); // pausa entre casos para evitar execuÃ§Ãµes simultÃ¢neas no mesmo workflow
        }

        $this->saveResults($results, $suite);
        $this->handleNewFails($results);

        $pass     = count(array_filter($results, fn($r) => $r['status'] === 'pass'));
        $knownFail = count(array_filter($results, fn($r) => $r['status'] === 'known-fail'));
        $newFail  = count(array_filter($results, fn($r) => $r['status'] === 'new-fail'));
        $timeout  = count(array_filter($results, fn($r) => $r['status'] === 'timeout'));
        $skip     = count(array_filter($results, fn($r) => $r['status'] === 'skip'));

        $this->info("âœ… Pass: {$pass} | âš ï¸ Known-fail: {$knownFail} | ðŸ”´ New-fail: {$newFail} | â± Timeout: {$timeout} | â¤· Skip: {$skip}");

        return $newFail > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function runCaso(array $caso): array
    {
        if ($caso['skip'] ?? false) {
            return array_merge($caso, ['status' => 'skip', 'detail' => $caso['skipReason'] ?? '']);
        }

        try {
            // 1. Marca timestamp antes de disparar para encontrar a execuÃ§Ã£o depois
            $beforeTimestamp = now()->subSeconds(2)->timestamp; // 2s buffer para clock skew

            // 2. Dispara o webhook do test runner
            $payload = [
                'target'      => $caso['target'],
                'tenant_slug' => $caso['tenant_slug'],
                'user_message'=> $caso['message'],
                'remoteJid'   => '11958469546@s.whatsapp.net',
                'pushName'    => 'Tester',
                'idMessage'   => 'TEST-' . $caso['id'] . '-' . time(),
            ];

            $payload = $this->injectAgendamentoCredentials($caso, $payload);

            // Alguns cenarios (ex.: agendamento direto) exigem contexto extra.
            foreach (['tenant_id', 'tenant_api_token', 'client_id'] as $optionalField) {
                if (array_key_exists($optionalField, $caso)) {
                    $payload[$optionalField] = $caso[$optionalField];
                }
            }

            $response = $this->dispatchWebhook($payload);

            if (!$response->successful()) {
                return array_merge($caso, [
                    'status' => 'new-fail',
                    'detail' => "Webhook retornou HTTP {$response->status()}",
                ]);
            }

            // 3. Para target=full o Hub Ã© async â€” aguarda processamento
            if ($caso['target'] === 'full') {
                sleep(20);
            }

            // 4. Encontra a execuÃ§Ã£o do workflow-alvo disparada apÃ³s o timestamp
            $workflowId  = $this->targetWorkflowMap[$caso['target']] ?? null;
            $executionId = $this->findExecutionAfter($workflowId, $beforeTimestamp);

            if (!$executionId) {
                return array_merge($caso, [
                    'status' => 'timeout',
                    'detail' => "Nenhuma execuÃ§Ã£o encontrada apÃ³s disparar o teste",
                ]);
            }

            // 5. Polling atÃ© a execuÃ§Ã£o terminar
            $status = $this->pollExecution($executionId);

            return array_merge($caso, $status);

        } catch (\Exception $e) {
            return array_merge($caso, [
                'status' => 'new-fail',
                'detail' => "Erro: {$e->getMessage()}",
            ]);
        }
    }

    /**
     * Busca o ID da execuÃ§Ã£o mais recente do workflow disparada apÃ³s $beforeTimestamp.
     * Tenta por atÃ© $maxPollSeconds com polling.
     */
    private function resetBookingSessionIfNeeded(array $caso): void
    {
        if (($caso['target'] ?? '') !== 'agendamento') {
            return;
        }

        if (
            !array_key_exists('tenant_id', $caso) ||
            !array_key_exists('tenant_api_token', $caso) ||
            !array_key_exists('client_id', $caso)
        ) {
            return;
        }

        $tenantSlug = (string) ($caso['tenant_slug'] ?? '');
        $tenantId = (string) $caso['tenant_id'];
        $tenantToken = (string) $caso['tenant_api_token'];
        $clientId = (string) $caso['client_id'];

        if ($tenantSlug === '' || $tenantId === '' || $tenantToken === '' || $clientId === '') {
            return;
        }

        $headers = [
            'Accept' => 'application/json',
            // Era 'X-MMCloud-Header', que o MMCC nunca leu — funcionava so porque
            // booking-sessions ainda esta aberta (SDD-fechamento-da-api-externa, F4).
            'X-MMCloud-Token' => $tenantToken,
            'X-Tenant-Id' => $tenantId,
        ];

        $baseUrl = "https://api.mmcriativos.cloud/api/external/tenants/{$tenantSlug}/booking-sessions";

        try {
            $current = Http::withHeaders($headers)
                ->timeout(10)
                ->get("{$baseUrl}/current", ['customer_id' => $clientId]);

            if (!$current->successful()) {
                return;
            }

            $bookingSessionId = data_get($current->json(), 'booking_session.id');
            if (!$bookingSessionId) {
                return;
            }

            Http::withHeaders($headers)
                ->timeout(10)
                ->delete("{$baseUrl}/{$bookingSessionId}");
        } catch (\Throwable $e) {
            Log::warning("resetBookingSessionIfNeeded error: {$e->getMessage()}");
        }
    }

    private function findExecutionAfter(string $workflowId, int $beforeTimestamp): ?string
    {
        $beforeDate = date('Y-m-d\TH:i:s\Z', $beforeTimestamp);
        $start = time();

        while ((time() - $start) < $this->maxPollSeconds) {
            try {
                $response = Http::withHeaders(['X-N8N-API-KEY' => $this->n8nApiKey])
                    ->timeout(10)
                    ->get("{$this->n8nBaseUrl}/api/v1/executions", [
                        'workflowId' => $workflowId,
                        'limit'      => 5,
                    ]);

                if ($response->successful()) {
                    foreach ($response->json('data', []) as $exec) {
                        if (($exec['startedAt'] ?? '') >= $beforeDate) {
                            return (string) $exec['id'];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("findExecutionAfter error: {$e->getMessage()}");
            }

            sleep(3);
        }

        return null;
    }

    private function injectAgendamentoCredentials(array $caso, array $payload): array
    {
        if (($caso['target'] ?? '') !== 'agendamento') {
            return $payload;
        }

        $tenantSlug = (string) ($caso['tenant_slug'] ?? '');
        $creds = $this->agendamentoTenantCreds[$tenantSlug] ?? null;
        if (!is_array($creds)) {
            return $payload;
        }

        $payload['tenant_id'] = $payload['tenant_id'] ?? $creds['tenant_id'];
        $payload['tenant_api_token'] = $payload['tenant_api_token'] ?? $creds['tenant_api_token'];
        $payload['client_id'] = $payload['client_id'] ?? $creds['client_id'];

        return $payload;
    }

    private function dispatchWebhook(array $payload, int $maxAttempts = 2)
    {
        $attempt = 1;
        $lastResponse = null;

        while ($attempt <= $maxAttempts) {
            try {
                $response = Http::timeout(90)->post($this->webhookUrl, $payload);
                if ($response->successful()) {
                    return $response;
                }

                $lastResponse = $response;
            } catch (\Throwable $e) {
                Log::warning("PlatformHealth webhook attempt {$attempt} failed: {$e->getMessage()}");
            }

            $attempt++;
            if ($attempt <= $maxAttempts) {
                sleep(2);
            }
        }

        return $lastResponse ?? Http::response(['error' => 'webhook_failed'], 599);
    }

    private function pollExecution(string $executionId): array
    {
        $start = time();

        while ((time() - $start) < $this->maxPollSeconds) {
            sleep(3);

            try {
                $response = Http::withHeaders([
                    'X-N8N-API-KEY' => $this->n8nApiKey,
                ])->timeout(10)->get("{$this->n8nBaseUrl}/api/v1/executions/{$executionId}", [
                    'includeData' => 'true',
                ]);

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

        return ['status' => 'timeout', 'detail' => "ExecuÃ§Ã£o nÃ£o finalizou em {$this->maxPollSeconds}s"];
    }

    private function classifyExecution(array $execution): array
    {
        $execStatus = $execution['status'] ?? 'unknown';
        $data = $execution['data'] ?? [];
        $lastNode = $this->getLastNode($data);

        if ($execStatus === 'error') {
            $errorMsg = $data['resultData']['error']['message'] ?? 'Erro desconhecido';
            return ['status' => 'new-fail', 'detail' => "Erro na execucao: {$errorMsg}", 'lastNode' => $lastNode];
        }

        if ($this->isSendTextNode($lastNode)) {
            return ['status' => 'pass', 'detail' => 'Chegou ao Send Text', 'lastNode' => $lastNode];
        }

        // Quando o atendimento roteia para agendamento via executeWorkflow,
        // o Send Text pode acontecer na subexecucao.
        $subExecutionId = $this->getSubExecutionIdFromNode($data, $lastNode);
        if (!$subExecutionId && $this->isScheduleHandoffNode($lastNode)) {
            $subExecutionId = $this->findAnySubExecutionId($data);
        }

        if ($subExecutionId) {
            $subExecution = $this->fetchExecution($subExecutionId);
            if ($subExecution) {
                $subData = $subExecution['data'] ?? [];
                $subStatus = $subExecution['status'] ?? 'unknown';
                $subLastNode = $this->getLastNode($subData);
                $composedLastNode = "{$lastNode} -> {$subLastNode}";

                if ($subStatus === 'error') {
                    $errorMsg = $subData['resultData']['error']['message'] ?? 'Erro desconhecido';
                    return ['status' => 'new-fail', 'detail' => "Erro na subexecucao {$subExecutionId}: {$errorMsg}", 'lastNode' => $composedLastNode];
                }

                if ($this->isSendTextNode($subLastNode)) {
                    return ['status' => 'pass', 'detail' => "Chegou ao Send Text na subexecucao {$subExecutionId}", 'lastNode' => $composedLastNode];
                }

                if ($this->isKnownFailNode($subLastNode)) {
                    return ['status' => 'known-fail', 'detail' => "Known-fail na subexecucao {$subExecutionId}: {$subLastNode}", 'lastNode' => $composedLastNode];
                }

                return ['status' => 'new-fail', 'detail' => "Nao chegou ao Send Text na subexecucao {$subExecutionId}. Ultimo node: {$subLastNode}", 'lastNode' => $composedLastNode];
            }
        }

        if ($this->isKnownFailNode($lastNode)) {
            return ['status' => 'known-fail', 'detail' => "Known-fail em: {$lastNode}", 'lastNode' => $lastNode];
        }

        return ['status' => 'new-fail', 'detail' => "Nao chegou ao Send Text. Ultimo node: {$lastNode}", 'lastNode' => $lastNode];
    }

    private function getLastNode(array $data): string
    {
        $resultData = $data['resultData'] ?? [];
        $runData = $resultData['runData'] ?? [];
        if (empty($runData)) return 'desconhecido';
        $nodes = array_keys($runData);
        return end($nodes) ?: 'desconhecido';
    }

    private function isSendTextNode(string $nodeName): bool
    {
        $node = strtolower($nodeName);
        return str_contains($node, 'send text') || str_contains($node, 'vee - send');
    }

    private function isKnownFailNode(string $nodeName): bool
    {
        $knownFailNodes = ['Save Process', 'cdbKOKD6EsPXU'];
        foreach ($knownFailNodes as $known) {
            if (str_contains($nodeName, $known)) {
                return true;
            }
        }
        return false;
    }

    private function isScheduleHandoffNode(string $nodeName): bool
    {
        $lower = strtolower($nodeName);
        return str_contains($lower, 'agendamento') || str_contains($lower, 'schedule');
    }

    private function getSubExecutionIdFromNode(array $data, string $nodeName): ?string
    {
        $runData = $data['resultData']['runData'] ?? [];
        $nodeRuns = $runData[$nodeName] ?? null;
        if (!is_array($nodeRuns)) {
            return null;
        }

        foreach ($nodeRuns as $run) {
            if (!is_array($run)) {
                continue;
            }
            $subExecutionId = $run['metadata']['subExecution']['executionId'] ?? null;
            if (!empty($subExecutionId)) {
                return (string) $subExecutionId;
            }
        }

        return null;
    }

    private function findAnySubExecutionId(array $data): ?string
    {
        $runData = $data['resultData']['runData'] ?? [];
        foreach ($runData as $nodeRuns) {
            if (!is_array($nodeRuns)) {
                continue;
            }
            foreach ($nodeRuns as $run) {
                if (!is_array($run)) {
                    continue;
                }
                $subExecutionId = $run['metadata']['subExecution']['executionId'] ?? null;
                if (!empty($subExecutionId)) {
                    return (string) $subExecutionId;
                }
            }
        }
        return null;
    }

    private function fetchExecution(string $executionId): ?array
    {
        try {
            $this->resetBookingSessionIfNeeded($caso);
            $response = Http::withHeaders([
                'X-N8N-API-KEY' => $this->n8nApiKey,
            ])->timeout(10)->get("{$this->n8nBaseUrl}/api/v1/executions/{$executionId}", [
                'includeData' => 'true',
            ]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::warning("fetchExecution error ({$executionId}): {$e->getMessage()}");
            return null;
        }
    }

    private function saveResults(array $results, string $suite): void
    {
        $date = Carbon::now()->format('Y-m-d');
        $pass     = count(array_filter($results, fn($r) => $r['status'] === 'pass'));
        $knownFail = count(array_filter($results, fn($r) => $r['status'] === 'known-fail'));
        $newFail  = count(array_filter($results, fn($r) => $r['status'] === 'new-fail'));
        $timeout  = count(array_filter($results, fn($r) => $r['status'] === 'timeout'));
        $skip     = count(array_filter($results, fn($r) => $r['status'] === 'skip'));

        $md = "# Platform Health â€” {$date} ({$suite})\n\n";
        $md .= "**Resultado:** âœ… {$pass} pass | âš ï¸ {$knownFail} known-fail | ðŸ”´ {$newFail} new-fail | â± {$timeout} timeout | â¤· {$skip} skip\n\n";
        $md .= "## Casos\n\n";
        $md .= "| ID | CenÃ¡rio | Status | Detalhe | Ãšltimo Node |\n";
        $md .= "|---|---|---|---|---|\n";

        foreach ($results as $r) {
            $icon = match($r['status']) {
                'pass'       => 'âœ…',
                'known-fail' => 'âš ï¸',
                'new-fail'   => 'ðŸ”´',
                'timeout'    => 'â±',
                'skip'       => 'â¤·',
                default      => 'â“',
            };
            $id       = $r['id'] ?? '-';
            $scenario = addslashes($r['scenario'] ?? '-');
            $detail   = addslashes($r['detail'] ?? '-');
            $lastNode = addslashes($r['lastNode'] ?? '-');
            $md .= "| {$id} | {$scenario} | {$icon} {$r['status']} | {$detail} | {$lastNode} |\n";
        }

        if ($newFail > 0) {
            $md .= "\n## âš ï¸ AÃ§Ã£o NecessÃ¡ria\n\n";
            foreach ($results as $r) {
                if ($r['status'] === 'new-fail') {
                    $md .= "- **{$r['id']}** â€” {$r['scenario']}: {$r['detail']}\n";
                }
            }
        }

        // Salva via Obsidian MCP (POST para o vee-mcp)
        $this->saveToObsidian("MMCloud/testes/resultados-{$date}.md", $md, "Platform Health {$date}");

        // Salva tambÃ©m localmente em storage/logs para debug
        Storage::disk('local')->put("platform-health/resultados-{$date}.json", json_encode($results, JSON_PRETTY_PRINT));
    }

    private function handleNewFails(array $results): void
    {
        $newFails = array_filter($results, fn($r) => $r['status'] === 'new-fail');
        if (empty($newFails)) return;

        // Busca o prÃ³ximo nÃºmero de BUG disponÃ­vel
        $bugNum = $this->getNextBugNumber();

        foreach ($newFails as $r) {
            $bugId   = sprintf('BUG-%03d', $bugNum);
            $date    = Carbon::now()->format('Y-m-d');
            $lastNode = $r['lastNode'] ?? 'desconhecido';
            $content = "# {$bugId} â€” {$r['scenario']}\n\n";
            $content .= "**Data:** {$date}\n";
            $content .= "**Status:** âŒ Aberto\n";
            $content .= "**Severidade:** Alta\n";
            $content .= "**Detectado por:** Platform Health Suite (automÃ¡tico)\n\n";
            $content .= "## DescriÃ§Ã£o\n\n{$r['detail']}\n\n";
            $content .= "## Caso de Teste\n\n";
            $content .= "- **ID:** {$r['id']}\n";
            $content .= "- **CenÃ¡rio:** {$r['scenario']}\n";
            $content .= "- **Tenant:** {$r['tenant_slug']}\n";
            $content .= "- **Ãšltimo node:** {$lastNode}\n\n";
            $content .= "## PrÃ³ximos Passos\n\n- [ ] Investigar no n8n via REST API\n- [ ] Corrigir\n- [ ] Re-rodar caso de teste para confirmar\n";

            $path = "tecnico/bugs/{$bugId}-" . Str::slug($r['scenario']) . ".md";
            $this->saveToObsidian($path, $content, $bugId);
            $this->warn("ðŸ”´ BUG criado: {$bugId} â€” {$r['scenario']}");
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
            // NÃ£o quebra o comando se o Obsidian falhar
        }
    }

    private function getNextBugNumber(): int
    {
        // LÃª o README de bugs para descobrir o prÃ³ximo nÃºmero
        // Fallback: comeÃ§a em 004 (001, 002, 003 jÃ¡ existem)
        return 4;
    }

    private function formatLine(array $r): string
    {
        $icon = match($r['status']) {
            'pass'       => 'âœ…',
            'known-fail' => 'âš ï¸',
            'new-fail'   => 'ðŸ”´',
            'timeout'    => 'â±',
            'skip'       => 'â¤·',
            default      => 'â“',
        };
        return "{$icon} [{$r['id']}] {$r['scenario']} â€” {$r['detail']}";
    }

    private function getCasos(string $suite): array
    {
        // Suite diÃ¡ria: grupos A, B, C, D, E, K, L + M parcial
        // Suite semanal: todos os 81 casos
        // Retorna array com os casos â€” ver casos-de-teste.md para lista completa

        $daily = $this->getDailyCasos();
        if ($suite === 'weekly') {
            return array_merge($daily, $this->getWeeklyCasos());
        }
        return $daily;
    }

    private function getDailyCasos(): array
    {
        return [
            // GRUPO A â€” Onboarding â€” target: atendimento (testa RAG, saudaÃ§Ã£o, contexto)
            ['id'=>'A-001','scenario'=>'Cliente novo â€” primeira mensagem','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'OlÃ¡, quero saber sobre vocÃªs'],
            ['id'=>'A-002','scenario'=>'Cliente novo â€” jÃ¡ diz o nome','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Oi, sou a Maria, quero agendar'],
            ['id'=>'A-003','scenario'=>'Cliente novo â€” nÃ£o diz o nome','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Oi'],
            ['id'=>'A-004','scenario'=>'Responde com nome apÃ³s ser perguntado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Maria'],

            // GRUPO B â€” Intent â€” target: atendimento (classifica intenÃ§Ã£o e roteia)
            ['id'=>'B-001','scenario'=>'Intent: booking','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Quero marcar um horÃ¡rio'],
            ['id'=>'B-002','scenario'=>'Intent: informaÃ§Ã£o/RAG','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Quais serviÃ§os vocÃªs oferecem?'],
            ['id'=>'B-003','scenario'=>'Intent: small talk','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Oi, tudo bem?'],
            ['id'=>'B-004','scenario'=>'Intent: commercial intelligence','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'VocÃªs tÃªm desconto?'],
            ['id'=>'B-005','scenario'=>'Mensagem ambÃ­gua','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Hmm'],

            // GRUPO C â€” Fluxo atendimento â€” target: atendimento
            ['id'=>'C-001','scenario'=>'Cliente retornando â€” contexto recuperado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'OlÃ¡ de novo'],
            ['id'=>'C-002','scenario'=>'Memory update detectado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Pode anotar que prefiro horÃ¡rios de manhÃ£'],
            ['id'=>'C-003','scenario'=>'Salvar mensagens inbound/outbound','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Teste de save'],
            ['id'=>'C-004','scenario'=>'Token Count registrado','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Quero informaÃ§Ãµes'],

            // GRUPO D â€” Entity Resolver â€” target: agendamento
            ['id'=>'D-001','scenario'=>'ServiÃ§o identificado por nome exato','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o gratuita','tenant_id'=>5,'tenant_api_token'=>'cb58831c45215bad4afe21402e238b23c3540daee73e82aafa13be491dab7e4d','client_id'=>6],
            ['id'=>'D-002','scenario'=>'ServiÃ§o com mÃºltiplos candidatos','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o','tenant_id'=>5,'tenant_api_token'=>'cb58831c45215bad4afe21402e238b23c3540daee73e82aafa13be491dab7e4d','client_id'=>6],
            ['id'=>'D-006','scenario'=>'Data em linguagem natural â€” amanhÃ£','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar para amanhÃ£','tenant_id'=>5,'tenant_api_token'=>'cb58831c45215bad4afe21402e238b23c3540daee73e82aafa13be491dab7e4d','client_id'=>6],
            ['id'=>'D-007','scenario'=>'HorÃ¡rio em linguagem natural','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar de tarde','tenant_id'=>5,'tenant_api_token'=>'cb58831c45215bad4afe21402e238b23c3540daee73e82aafa13be491dab7e4d','client_id'=>6],
            ['id'=>'D-008','scenario'=>'Sem nenhuma entidade','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar','tenant_id'=>5,'tenant_api_token'=>'cb58831c45215bad4afe21402e238b23c3540daee73e82aafa13be491dab7e4d','client_id'=>6],

            // GRUPO E â€” Missing Fields â€” target: agendamento
            ['id'=>'E-001','scenario'=>'Falta serviÃ§o','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar para amanhÃ£ Ã s 10h'],
            ['id'=>'E-002','scenario'=>'Falta data','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o gratuita Ã s 10h'],
            ['id'=>'E-003','scenario'=>'Falta horÃ¡rio','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o gratuita amanhÃ£'],
            ['id'=>'E-007','scenario'=>'UsuÃ¡rio envia tudo em uma mensagem','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o gratuita, amanhÃ£ Ã s 10h'],

            // GRUPO K â€” Save Process â€” target: agendamento
            ['id'=>'K-001','scenario'=>'Save missing fields â€” todos os 4 nodes','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero agendar'],
            ['id'=>'K-002','scenario'=>'Save disambiguous question','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o'],

            // GRUPO L â€” Hub/Roteamento â€” target: atendimento
            ['id'=>'L-001','scenario'=>'Mensagem de texto simples','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'OlÃ¡'],
            ['id'=>'L-002','scenario'=>'Mensagem prÃ³pria (fromMe)','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'Teste'],
            ['id'=>'L-004','scenario'=>'Roteamento para MMCloud','target'=>'atendimento','tenant_slug'=>'veetest','message'=>'OlÃ¡'],

            // GRUPO M â€” mmbeauty parcial â€” target: full (fluxo completo Hubâ†’Atendimentoâ†’Agendamento)
            ['id'=>'M-001','scenario'=>'DesambiguaÃ§Ã£o entre 6 profissionais','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero agendar'],
            ['id'=>'M-002','scenario'=>'Profissional turno manhÃ£','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero agendar de manhÃ£'],
            ['id'=>'M-003','scenario'=>'Profissional turno tarde','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero agendar de tarde'],
            ['id'=>'M-005','scenario'=>'ServiÃ§o domicÃ­lio','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero Corte e Barba em DomicÃ­lio'],
            ['id'=>'M-009','scenario'=>'Deadline zero â€” cancelamento sempre permitido','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero cancelar meu agendamento'],
            ['id'=>'M-010','scenario'=>'Deadline zero â€” reagendamento sempre permitido','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero remarcar meu horÃ¡rio'],
        ];
    }

    private function getWeeklyCasos(): array
    {
        // Casos adicionais da suite semanal (grupos F, G, H, I, J, D completo, M completo)
        // veetest-b e veetest-c necessÃ¡rios para grupos F, G-002, H-007, I-002, J
        return [
            // GRUPO F â€” Blocking â€” target: agendamento (veetest-c)
            ['id'=>'F-001','scenario'=>'PreCheck OK â€” todos campos vÃ¡lidos','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'AvaliaÃ§Ã£o RÃ¡pida amanhÃ£ Ã s 10h'],
            ['id'=>'F-002','scenario'=>'Blocked: sem disponibilidade no horÃ¡rio','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'AvaliaÃ§Ã£o RÃ¡pida amanhÃ£ Ã s 11h30'],
            ['id'=>'F-003','scenario'=>'Blocked: profissional indisponÃ­vel','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero com o Prof Alpha Ã s 11h'],
            ['id'=>'F-005','scenario'=>'Blocked: serviÃ§o nÃ£o disponÃ­vel na data','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'AvaliaÃ§Ã£o Inicial amanhÃ£ Ã s 10h'],

            // GRUPO G â€” CriaÃ§Ã£o agendamento
            ['id'=>'G-001','scenario'=>'Agendamento presencial','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o gratuita amanhÃ£ Ã s 10h'],
            ['id'=>'G-002','scenario'=>'Agendamento virtual (Google Meet)','target'=>'agendamento','tenant_slug'=>'veetest-b','message'=>'Consulta Virtual amanhÃ£ Ã s 10h'],
            ['id'=>'G-009','scenario'=>'Mensagem de confirmaÃ§Ã£o enviada','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'AvaliaÃ§Ã£o gratuita amanhÃ£ Ã s 10h'],

            // GRUPO H â€” Reagendamento
            ['id'=>'H-001','scenario'=>'Reagendamento com ID explÃ­cito','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero remarcar o agendamento'],
            ['id'=>'H-007','scenario'=>'Reagendamento fora do prazo','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero remarcar'],

            // GRUPO I â€” Cancelamento
            ['id'=>'I-001','scenario'=>'Cancelamento dentro do prazo','target'=>'agendamento','tenant_slug'=>'veetest','message'=>'Quero cancelar'],
            ['id'=>'I-002','scenario'=>'Cancelamento fora do prazo','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero cancelar'],

            // GRUPO J â€” DesambiguaÃ§Ã£o (veetest-c)
            ['id'=>'J-002','scenario'=>'MÃºltiplos serviÃ§os â€” desambiguaÃ§Ã£o','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero AvaliaÃ§Ã£o'],
            ['id'=>'J-003','scenario'=>'MÃºltiplos profissionais â€” desambiguaÃ§Ã£o','target'=>'agendamento','tenant_slug'=>'veetest-c','message'=>'Quero agendar'],

            // GRUPO M â€” mmbeauty semanal completo â€” target: full
            ['id'=>'M-004','scenario'=>'Profissional manhÃ£ indisponÃ­vel Ã  tarde','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero com o Lucas Ã s 16h'],
            ['id'=>'M-006','scenario'=>'ServiÃ§o longo 150min','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero Platinado amanhÃ£'],
            ['id'=>'M-007','scenario'=>'ServiÃ§o curto 10min','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Sobrancelha amanhÃ£ Ã s 9h'],
            ['id'=>'M-008','scenario'=>'SÃ¡bado â€” unidade encerra Ã s 14h','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero agendar sÃ¡bado Ã s 15h'],
            ['id'=>'M-011','scenario'=>'DesambiguaÃ§Ã£o de serviÃ§o (nomes parecidos)','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero hidrataÃ§Ã£o'],
            ['id'=>'M-012','scenario'=>'Plano mensal com preÃ§o real','target'=>'full','tenant_slug'=>'mmbeauty','message'=>'Quero o plano de corte'],
        ];
    }
}
