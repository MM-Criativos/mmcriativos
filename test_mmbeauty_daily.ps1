$wh = 'https://n8n.mmcriativos.cloud/webhook/WRPPK93a5dttEwzA/webhook/test-runner'
$h  = @{ 'X-N8N-API-KEY' = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJlYjE4MGIzOS0yNTZmLTRkNmQtYjg1NS04YzY3ZTk0MjNmNzgiLCJpc3MiOiJuOG4iLCJhdWQiOiJwdWJsaWMtYXBpIiwiaWF0IjoxNzc2MzYwNDExfQ.96Mm5Jyvxf-xZ5UkKAdtF5_jHe_4lO6tjYdVSC7Q4zM' }

$casos = @(
  @{id='M-001';scenario='Desambiguacao entre 6 profissionais';message='Quero agendar'},
  @{id='M-002';scenario='Profissional turno manha';message='Quero agendar de manhã'},
  @{id='M-003';scenario='Profissional turno tarde';message='Quero agendar de tarde'},
  @{id='M-005';scenario='Servico domicilio';message='Quero Corte e Barba em Domicílio'},
  @{id='M-009';scenario='Deadline zero - cancelamento';message='Quero cancelar meu agendamento'},
  @{id='M-010';scenario='Deadline zero - reagendamento';message='Quero remarcar meu horário'}
)

$results = @()
foreach ($c in $casos) {
  $ts = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
  Write-Host "--- [$($c.id)] $($c.scenario)" -ForegroundColor Cyan

  $body = @{
    target       = 'full'
    tenant_slug  = 'mmbeauty'
    user_message = $c.message
    remoteJid    = '11958469546@s.whatsapp.net'
    pushName     = 'Tester'
    idMessage    = 'TEST-' + $c.id + '-' + $ts
  } | ConvertTo-Json

  try {
    $null = Invoke-RestMethod $wh -Method POST -Body $body -ContentType 'application/json' -TimeoutSec 30
    Write-Host "  Webhook OK - aguardando 25s (full = Hub async)..." -ForegroundColor Green
    Start-Sleep -Seconds 25

    # Busca execucoes recentes do Agente de Atendimento (sub-workflow no target=full)
    $beforeDate = (Get-Date).ToUniversalTime().AddSeconds(-60).ToString('yyyy-MM-ddTHH:mm:ssZ')
    $execs = Invoke-RestMethod 'https://n8n.mmcriativos.cloud/api/v1/executions?workflowId=AcTwVVZ86JUuDcX_byb3B&limit=5' -Headers $h
    $exec  = $execs.data | Where-Object { $_.startedAt -ge $beforeDate } | Select-Object -First 1

    if ($exec) {
      $eid  = $exec.id
      $done = $false
      for ($i = 0; $i -lt 20; $i++) {
        Start-Sleep -Seconds 3
        $e = Invoke-RestMethod "https://n8n.mmcriativos.cloud/api/v1/executions/$eid" -Headers $h
        if ($e.status -notin @('running','waiting')) { $done = $true; break }
      }
      $st = if ($done) { $e.status } else { 'timeout' }
      Write-Host "  Status: $st  (execId=$eid)" -ForegroundColor Yellow
      $results += [PSCustomObject]@{id=$c.id; scenario=$c.scenario; status=$st; execId=$eid}
    } else {
      Write-Host "  AVISO: nenhuma execucao encontrada apos disparo" -ForegroundColor Red
      $results += [PSCustomObject]@{id=$c.id; scenario=$c.scenario; status='no-exec'; execId=''}
    }
  } catch {
    Write-Host "  ERRO: $_" -ForegroundColor Red
    $results += [PSCustomObject]@{id=$c.id; scenario=$c.scenario; status='error'; execId=''}
  }

  Start-Sleep -Seconds 3
}

Write-Host ""
Write-Host "=== RESULTADO CASOS M (daily) ===" -ForegroundColor Magenta
$results | Format-Table -AutoSize
