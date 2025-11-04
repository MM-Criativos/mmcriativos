<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Nova mensagem de contato</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; line-height:1.5; color:#111;">
    <h2>Nova mensagem de contato</h2>
    <p><strong>Nome:</strong> {{ $data['name'] }}</p>
    <p><strong>E-mail:</strong> {{ $data['email'] }}</p>
    @if(!empty($data['whatsapp']))
        <p><strong>WhatsApp:</strong> {{ $data['whatsapp'] }}</p>
    @endif
    @if(!empty($data['service']))
        <p><strong>Serviço:</strong> {{ $data['service'] }}</p>
    @endif
    <p><strong>Mensagem:</strong><br>{{ nl2br(e($data['message'])) }}</p>
</body>
</html>

