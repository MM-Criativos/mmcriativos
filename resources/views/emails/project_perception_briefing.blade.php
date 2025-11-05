<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Régua de Percepção</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif; color:#111827; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .btn { display:inline-block; padding:12px 18px; background:#ea580c; color:#fff !important; text-decoration:none; border-radius:8px; }
        .muted { color:#6b7280; }
        .card { border:1px solid #e5e7eb; border-radius:12px; padding:20px; }
        h1 { font-size:22px; margin: 0 0 8px 0; }
        h2 { font-size:18px; margin: 24px 0 8px 0; }
        p { line-height:1.6; }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>🎉 Seja bem-vindo(a)! Vamos começar a construir o seu novo site 🚀</h1>
      <p>Olá, <strong>{{ $client_name }}</strong> 👋</p>
      <p>É um prazer enorme ter você conosco! 🧡<br>
        A partir de agora, damos início à criação do seu novo site, e essa primeira etapa é essencial para garantirmos que o resultado final traduza exatamente o que você imagina — e o que a sua marca precisa transmitir.</p>
      <p>Antes de começarmos o design, queremos entender melhor a personalidade e o estilo que mais combinam com o seu projeto. Para isso, preparamos um briefing de percepção rápido e visual — nada técnico, prometemos 😄</p>

      <h2>💡 Como funciona</h2>
      <ul>
        <li>Você verá algumas escalas com pontos, cada uma com dois conceitos opostos (por exemplo: tradicional ↔ inovador, formal ↔ descontraído).</li>
        <li>Basta clicar no ponto que melhor representa o que você quer para o seu site:</li>
      </ul>
      <ul>
        <li>⚖️ O ponto do meio: indica equilíbrio entre os dois conceitos</li>
        <li>🎯 Um ponto mais à esquerda ou à direita: mostra que você quer que a gente priorize aquele estilo</li>
        <li>💬 Comentário opcional: se quiser, você pode deixar observações ou até enviar uma referência visual que represente esse estilo</li>
      </ul>
      <p>Mas não se preocupe! Caso não envie referências agora, teremos outra oportunidade de fazer isso na próxima etapa do briefing, focada nas inspirações visuais e no conteúdo. 😉</p>

      <h2>🚀 Vamos começar?</h2>
      <p>Clique no botão abaixo para acessar o briefing de percepção e nos contar mais sobre o estilo do seu projeto:</p>
      <p>
        <a class="btn" href="{!! $briefing_link !!}" target="_blank" rel="noopener">👉 Responder ao Briefing</a>
      </p>
      <p class="muted" style="font-size:12px;">
        Se o botão não funcionar, copie e cole este link no seu navegador:<br>
        <span style="word-break:break-all;">{{ $briefing_link }}</span>
      </p>

      <p class="muted">Agradecemos mais uma vez por confiar na MM Criativos. Estamos empolgados para transformar suas ideias em uma experiência digital única. ✨</p>
      <p class="muted">Um abraço,<br>Equipe MM Criativos</p>
    </div>
  </body>
</html>
