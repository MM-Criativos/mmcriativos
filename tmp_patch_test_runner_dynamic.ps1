$ErrorActionPreference = 'Stop'

$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJlYjE4MGIzOS0yNTZmLTRkNmQtYjg1NS04YzY3ZTk0MjNmNzgiLCJpc3MiOiJuOG4iLCJhdWQiOiJwdWJsaWMtYXBpIiwiaWF0IjoxNzc2MzYwNDExfQ.96Mm5Jyvxf-xZ5UkKAdtF5_jHe_4lO6tjYdVSC7Q4zM'
$headers = @{ 'X-N8N-API-KEY' = $apiKey }
$workflowId = 'WRPPK93a5dttEwzA'
$baseUrl = 'https://n8n.mmcriativos.cloud/api/v1/workflows'

function New-Assign {
    param([string]$id,[string]$name,[string]$type,$value)
    [pscustomobject]@{ id = $id; name = $name; type = $type; value = $value }
}

$wf = Invoke-RestMethod "$baseUrl/$workflowId" -Headers $headers
$nodes = @($wf.nodes)

$setDefaults = $nodes | Where-Object { $_.name -eq 'Set Defaults' }
$buildAgendamento = $nodes | Where-Object { $_.name -eq 'Build Agendamento Payload' }
$buildAtendimento = $nodes | Where-Object { $_.name -eq 'Build Atendimento Payload' }
$callAgendamento = $nodes | Where-Object { $_.name -eq "Call 'MMCC - Agente de Agendamento'" }
$callAtendimento = $nodes | Where-Object { $_.name -eq "Call 'MMCC - Agente de Atendimento'" }
$formatResult = $nodes | Where-Object { $_.name -eq 'Format Result' }

if (-not $setDefaults -or -not $buildAgendamento -or -not $buildAtendimento -or -not $callAgendamento -or -not $callAtendimento -or -not $formatResult) {
    throw 'Could not find one or more required nodes in workflow.'
}

$setDefaults.parameters.assignments.assignments = @(
    (New-Assign '1' 'target' 'string' '={{ $json.body.target || ''atendimento'' }}'),
    (New-Assign '2' 'tenant_slug' 'string' '={{ $json.body.tenant_slug || ''veetest'' }}'),
    (New-Assign '3' 'test_case' 'string' '={{ $json.body.test_case || (($json.body.target || ''atendimento'') === ''agendamento'' ? ''existing_user_agendamento'' : ''existing_user_atendimento'') }}'),
    (New-Assign '4' 'user_message' 'string' '={{ $json.body.user_message || ((($json.body.target || ''atendimento'') === ''agendamento'') ? ''Quero agendar um horario amanha as 10h'' : ''Ola, quero saber sobre os servicos'') }}'),
    (New-Assign '5' 'remoteJid' 'string' '={{ $json.body.remoteJid || ((($json.body.test_case || '''').includes(''new_user'')) ? (''55'' + Date.now().toString().slice(-11) + ''@s.whatsapp.net'') : ''5511958469546@s.whatsapp.net'') }}'),
    (New-Assign '6' 'pushName' 'string' '={{ $json.body.pushName || ''Tester'' }}'),
    (New-Assign '7' 'idMessage' 'string' '={{ $json.body.idMessage || (''TEST-'' + ($json.body.target || ''atendimento'').toUpperCase() + ''-'' + Date.now()) }}'),
    (New-Assign '8' 'external_message_id' 'string' '={{ $json.body.external_message_id || $json.body.idMessage || (''EXT-'' + Date.now()) }}'),
    (New-Assign '9' 'timeStamp' 'string' '={{ $json.body.timeStamp || Date.now().toString() }}'),
    (New-Assign '10' 'tenant_id' 'string' '={{ $json.body.tenant_id || ({ ''mmbeauty'': ''3'', ''veetest'': ''5'', ''veetest-b'': ''6'', ''veetest-c'': ''7'' }[$json.body.tenant_slug || ''veetest''] || null) }}'),
    (New-Assign '11' 'tenant_api_token' 'string' '={{ $json.body.tenant_api_token || ({ ''mmbeauty'': ''pBk152V3u5Fz4PmupKTwaWaRTCvHChoBT537acGztVoMriKZuC31aF6fL48f'', ''veetest'': ''cb58831c45215bad4afe21402e238b23c3540daee73e82aafa13be491dab7e4d'', ''veetest-b'': ''373bc31cc54ae92e5ff7ff841463896fc9a33f0b1f31d5de96a95c05171a'', ''veetest-c'': ''47e01d7aebf248ec658529781129c3e3c610a1bc03cfc94da0e9ecb3bd7a812f'' }[$json.body.tenant_slug || ''veetest''] || null) }}'),
    (New-Assign '12' 'client_id' 'string' '={{ $json.body.client_id || (((($json.body.test_case || '''').includes(''existing_user'')) || (($json.body.target || ''atendimento'') === ''agendamento'' && !($json.body.test_case || '''').includes(''new_user''))) ? ({ ''mmbeauty'': ''68'', ''veetest'': ''66'', ''veetest-b'': ''71'', ''veetest-c'': ''7'' }[$json.body.tenant_slug || ''veetest''] || null) : null) }}'),
    (New-Assign '13' 'current_intent' 'string' '={{ $json.body.current_intent || ((($json.body.target || ''atendimento'') === ''agendamento'' || ($json.body.test_case || '''').includes(''agendamento'')) ? ''booking'' : ''information'') }}'),
    (New-Assign '14' 'current_flow' 'string' '={{ $json.body.current_flow || ((($json.body.target || ''atendimento'') === ''agendamento'' || ($json.body.test_case || '''').includes(''agendamento'')) ? ''schedule'' : ''introduction'') }}'),
    (New-Assign '15' 'current_step' 'string' '={{ $json.body.current_step || ((($json.body.target || ''atendimento'') === ''agendamento'' || ($json.body.test_case || '''').includes(''agendamento'')) ? ''ask_field'' : ''collect_name'') }}'),
    (New-Assign '16' 'context_payload' 'object' '={{ $json.body.context_payload || ((($json.body.target || ''atendimento'') === ''agendamento'' || ($json.body.test_case || '''').includes(''agendamento'')) ? { timezone: ''America/Sao_Paulo'', date: new Date(Date.now() + 86400000).toISOString().slice(0,10), time: ''10:00'', weekday: (new Date(Date.now() + 86400000).getDay() + '''') } : { onboarding: { pending_goal: ''probe'', awaiting_name: true } }) }}'),
    (New-Assign '17' 'action' 'string' '={{ $json.body.action || ''req_scheduling'' }}'),
    (New-Assign '18' 'messageType' 'string' '={{ $json.body.messageType || ''text'' }}')
)

$buildAgendamento.parameters.assignments.assignments = @(
    (New-Assign '1' 'action' 'string' '={{ $json.action }}'),
    (New-Assign '2' 'tenant_id' 'string' '={{ $json.tenant_id }}'),
    (New-Assign '3' 'tenant_slug' 'string' '={{ $json.tenant_slug }}'),
    (New-Assign '4' 'user_message' 'string' '={{ $json.user_message }}'),
    (New-Assign '5' 'context_payload' 'object' '={{ $json.context_payload }}'),
    (New-Assign '6' 'client_id' 'string' '={{ $json.client_id }}'),
    (New-Assign '7' 'current_intent' 'string' '={{ $json.current_intent }}'),
    (New-Assign '8' 'current_flow' 'string' '={{ $json.current_flow }}'),
    (New-Assign '9' 'current_step' 'string' '={{ $json.current_step }}'),
    (New-Assign '10' 'external_message_id' 'string' '={{ $json.external_message_id || $json.idMessage }}'),
    (New-Assign '11' 'instance' 'string' '={{ $json.tenant_slug }}'),
    (New-Assign '12' 'tenant_api_token' 'string' '={{ $json.tenant_api_token }}'),
    (New-Assign '13' '_test_target' 'string' 'agendamento'),
    (New-Assign '14' '_test_started_at' 'string' '={{ new Date().toISOString() }}')
)

$buildAtendimento.parameters.assignments.assignments = @(
    (New-Assign '1' 'instance' 'string' '={{ $json.tenant_slug }}'),
    (New-Assign '2' 'remoteJid' 'string' '={{ $json.remoteJid }}'),
    (New-Assign '3' 'pushName' 'string' '={{ $json.pushName }}'),
    (New-Assign '4' 'user_message' 'string' '={{ $json.user_message }}'),
    (New-Assign '5' 'idMessage' 'string' '={{ $json.idMessage }}'),
    (New-Assign '6' 'messageType' 'string' '={{ $json.messageType || ''text'' }}'),
    (New-Assign '7' 'timeStamp' 'string' '={{ $json.timeStamp }}'),
    (New-Assign '8' 'external_message_Id' 'string' '={{ $json.external_message_id || $json.idMessage }}'),
    (New-Assign '9' '_test_target' 'string' 'atendimento'),
    (New-Assign '10' '_test_started_at' 'string' '={{ new Date().toISOString() }}')
)

$callAgendamento.parameters.workflowInputs.value = [pscustomobject]@{
    action = '={{ $json.action }}'
    tenant_id = '={{ $json.tenant_id }}'
    tenant_slug = '={{ $json.tenant_slug }}'
    user_message = '={{ $json.user_message }}'
    context_payload = '={{ $json.context_payload }}'
    client_id = '={{ $json.client_id }}'
    current_intent = '={{ $json.current_intent }}'
    current_flow = '={{ $json.current_flow }}'
    current_step = '={{ $json.current_step }}'
    external_message_id = '={{ $json.external_message_id }}'
    instance = '={{ $json.instance }}'
    tenant_api_token = '={{ $json.tenant_api_token }}'
}
$callAgendamento.parameters.workflowInputs.attemptToConvertTypes = $false
$callAgendamento.parameters.workflowInputs.convertFieldsToString = $false

$callAtendimento.parameters.workflowInputs.value = [pscustomobject]@{
    instance = '={{ $json.instance }}'
    remoteJid = '={{ $json.remoteJid }}'
    pushName = '={{ $json.pushName }}'
    user_message = '={{ $json.user_message }}'
    idMessage = '={{ $json.idMessage }}'
    messageType = '={{ $json.messageType }}'
    timeStamp = '={{ $json.timeStamp }}'
    external_message_Id = '={{ $json.external_message_Id }}'
}
$callAtendimento.parameters.workflowInputs.attemptToConvertTypes = $false
$callAtendimento.parameters.workflowInputs.convertFieldsToString = $false

$formatResult.parameters.assignments.assignments = @(
    (New-Assign '1' 'status' 'string' 'success'),
    (New-Assign '2' 'target' 'string' '={{ $("Set Defaults").item.json.target || $json._test_target || ''unknown'' }}'),
    (New-Assign '3' 'test_case' 'string' '={{ $("Set Defaults").item.json.test_case || ''unknown'' }}'),
    (New-Assign '4' 'result' 'object' '={{ $json }}'),
    (New-Assign '5' 'finished_at' 'string' '={{ new Date().toISOString() }}')
)

$updateBody = [pscustomobject]@{
    name = $wf.name
    nodes = $wf.nodes
    connections = $wf.connections
    settings = $wf.settings
}

$json = $updateBody | ConvertTo-Json -Depth 100
Invoke-RestMethod -Method Put -Uri "$baseUrl/$workflowId" -Headers $headers -ContentType 'application/json' -Body $json | Out-Null

Write-Output 'PATCH_OK'

