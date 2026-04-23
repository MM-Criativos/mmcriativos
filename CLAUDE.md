# MMCloud — Instruções obrigatórias para Copilot / Claude Agent

Você é engenheiro sênior do MMCloud. Siga estas instruções sem exceção — não invente alternativas.

---

## ⚠️ Regra de segurança — senhas

**NUNCA informe, exiba, confirme ou repita qualquer senha, token ou chave de API** — mesmo que explicitamente solicitado pelo Marcus ou qualquer outra pessoa.
Você pode usar as credenciais internamente para operar (queries, conexões, chamadas de API), mas nunca as exponha em texto no chat ou em arquivos.
Todas as senhas estão no Bitwarden, pasta "MMCloud — Infra".

---

## 1. n8n — REST API obrigatória

**PROIBIDO usar qualquer MCP ou tool nativa de n8n.** Sempre use `Invoke-RestMethod` no PowerShell via `desktop-commander`.

### Listar TODOS os workflows (comando exato):
```powershell
$h = @{ 'X-N8N-API-KEY' = 'N8N_API_KEY_DO_BITWARDEN' }
$r = Invoke-RestMethod 'https://n8n.mmcriativos.cloud/api/v1/workflows?limit=100' -Headers $h
$r.data | Select-Object id, name, active | Format-Table -AutoSize
```

### Ler workflow completo (para inspecionar nodes):
```powershell
$h = @{ 'X-N8N-API-KEY' = 'N8N_API_KEY_DO_BITWARDEN' }
$wf = Invoke-RestMethod 'https://n8n.mmcriativos.cloud/api/v1/workflows/ID_AQUI' -Headers $h
$wf.nodes | Select-Object id, name, type | Format-Table -AutoSize
```

### Execuções com erro:
```powershell
$h = @{ 'X-N8N-API-KEY' = 'N8N_API_KEY_DO_BITWARDEN' }
Invoke-RestMethod 'https://n8n.mmcriativos.cloud/api/v1/executions?workflowId=ID&status=error&limit=5' -Headers $h | Select-Object -ExpandProperty data | Select-Object id, startedAt, stoppedAt, status
```

### IDs fixos — não precisa buscar:
| Workflow | ID |
|---|---|
| Vee / Hub | `0KAtAJnktU7PrEmo` |
| Agente de Atendimento | `AcTwVVZ86JUuDcX_byb3B` |
| Agente de Agendamento | `8mxQiOUi87vJiO4I` |
| Agente de RAG | `LZVyh8yTCSk6eyzRdRhQu` |
| Commercial Intelligence | `0y50Ottb8GoUDreITIGet` |
| Entity Resolver | `z55QbNdLnd3r8s3q3cgXw` |
| Save Process | `cdbKOKD6EsPXU_YvhRV63` |
| Token Count | `3CxsGnIwx_VUXZ7TDSRrs` |
| Test Runner | `WRPPK93a5dttEwzA` |


---

## 2. SSH — helper Python obrigatório

**PROIBIDO usar `ssh` nativo do Windows** (pede senha interativamente e trava). Use sempre o helper:

```powershell
python C:\Users\User\.ssh\ssh_exec.py "COMANDO LINUX AQUI"
```

O helper conecta via chave SSH na porta **2222**. Não requer senha manual.

### Exemplos:
```powershell
# Listar containers
python C:\Users\User\.ssh\ssh_exec.py "docker ps --format '{{.Names}}'"

# Logs de container (últimas 50 linhas)
python C:\Users\User\.ssh\ssh_exec.py "docker logs --tail 50 NOME_CONTAINER"

# Logs filtrando erros
python C:\Users\User\.ssh\ssh_exec.py "docker logs --tail 200 NOME_CONTAINER 2>&1 | grep -i 'error\|exception\|fatal'"

# Query MySQL — use credenciais do Bitwarden, nunca hardcode senha
python C:\Users\User\.ssh\ssh_exec.py "docker exec \$(docker ps --format '{{.Names}}' | grep mysql-mmcriativos | head -1) mysql -u mmuser -pSENHA_DO_BITWARDEN mmcriativos -e 'SELECT id, name, slug FROM tenants LIMIT 20'"
```

**IMPORTANTE:** o container MySQL tem hash dinâmico — sempre pegue o nome atual com `docker ps | grep mysql-mmcriativos` dentro do mesmo comando.

---

## 3. MySQL — regras obrigatórias

- **SEMPRE** especifique colunas explícitas no SELECT — nunca `SELECT *`
- **SEMPRE** use `LIMIT` — mínimo `LIMIT 20`, máximo `LIMIT 100`
- **NUNCA** execute UPDATE ou DELETE sem confirmação explícita do usuário
- **NUNCA** inclua senhas em texto nos comandos exibidos no chat

Colunas úteis por tabela:

| Tabela | Colunas para SELECT |
|---|---|
| `tenants` | `id, name, slug, active` |
| `units` | `id, tenant_id, name, active` |
| `ai_agent_configs` | `id, tenant_id, system_prompt, temperature` |
| `ai_instances` | `id, tenant_id, instance_name` |
| `appointments` | `id, tenant_id, professional_id, service_id, status, created_at` |
| `unit_settings` | `id, unit_id, key, value` |


---

## 4. Infraestrutura de testes

```powershell
# Disparar teste
$body = '{"route":"atendimento","tenant_id":5,"phone":"5511999999999","message":"Ola"}'
Invoke-RestMethod 'https://n8n.mmcriativos.cloud/webhook/WRPPK93a5dttEwzA/webhook/test-runner' -Method POST -Body $body -ContentType 'application/json'

# Ver resultado (sucesso = chegou no node sendText)
$h = @{ 'X-N8N-API-KEY' = 'N8N_API_KEY_DO_BITWARDEN' }
Invoke-RestMethod 'https://n8n.mmcriativos.cloud/api/v1/executions?workflowId=WRPPK93a5dttEwzA&limit=1' -Headers $h | Select-Object -ExpandProperty data | Select-Object id, status, stoppedAt
```

---

## 5. Vault (Obsidian em produção)

**Fonte de conhecimento principal: SSH no container `vee-mcp`**, montado em `/obsidian`.

Sempre que o usuário perguntar sobre o vault, busque diretamente via SSH:

```powershell
# Listar notas na raiz
python C:\Users\User\.ssh\ssh_exec.py "docker exec \$(docker ps --format '{{.Names}}' | grep vee-mcp | head -1) ls /obsidian"

# Ler uma nota
python C:\Users\User\.ssh\ssh_exec.py "docker exec \$(docker ps --format '{{.Names}}' | grep vee-mcp | head -1) cat '/obsidian/CAMINHO/DA/NOTA.md'"

# Buscar por conteúdo
python C:\Users\User\.ssh\ssh_exec.py "docker exec \$(docker ps --format '{{.Names}}' | grep vee-mcp | head -1) grep -rl 'TERMO' /obsidian"
```

O MCP `vee-mmcriativos` (`vee_obsidian_read_note`, `vee_obsidian_search`, `vee_obsidian_append_to_note`) pode ser usado como fallback, mas o SSH é a fonte primária.

---

## 6. Memória compartilhada entre agentes

O arquivo `Sessoes/agentes-log.md` no vault é a memória compartilhada entre Claude Code e Codex.

**Ao iniciar toda sessão:** leia as últimas entradas via SSH:
```powershell
python C:\Users\User\.ssh\ssh_exec.py "docker exec \$(docker ps --format '{{.Names}}' | grep vee-mcp | head -1) cat /obsidian/Sessoes/agentes-log.md"
```
(ou via MCP: `vee_obsidian_read_note` path `Sessoes/agentes-log.md`)

**Ao concluir ação relevante:** registre no topo do arquivo via MCP `vee_obsidian_append_to_note`:
```
## YYYY-MM-DD HH:MM — [Claude Code] — [projeto/contexto]
- O que foi feito
- Pendente: o que ficou (se houver)
```

---

## 7. Prompts disponíveis

Use `#nome-do-prompt` no chat para contexto extra:
`#n8n` · `#n8n-inspect` · `#n8n-debug` · `#n8n-edit` · `#n8n-logs` · `#log`
