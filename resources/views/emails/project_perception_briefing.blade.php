<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Briefing de Percepção</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;
            color: #111827;
            background: #f3f4f6;
        }

        .wrapper {
            width: 100%;
            padding: 32px 0;
            text-align: center;
        }

        .container {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 32px 24px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .logo {
            display: inline-block;
            margin-bottom: 24px;
            height: 60px;
        }

        h1 {
            font-size: 24px;
            margin: 0 0 16px 0;
            line-height: 1.4;
        }

        h2 {
            font-size: 20px;
            margin: 32px 0 12px 0;
        }

        p {
            line-height: 1.7;
            margin: 12px 0;
            color: #1f2937;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 16px 0;
        }

        ul li {
            margin: 8px 0;
        }

        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #ea580c;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 999px;
            font-weight: 600;
            margin: 16px 0 8px 0;
        }

        .muted {
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <img src="{{ asset('assets/images/mmsite.png') }}" alt="MM Criativos" class="logo">

            <h1>🎉 Seja bem-vindo(a)!<br>Vamos começar a construir o seu novo site 🚀</h1>

            <p>Olá, <strong>{{ $client_name }}</strong> 👋</p>

            <p>É um prazer enorme ter você conosco! 💛<br>
                A partir de agora, damos início à criação do seu novo site, e essa primeira etapa é essencial para
                garantirmos que o resultado final traduza exatamente o que você imagina — e o que a sua marca precisa
                transmitir.</p>

            <p>Antes de começarmos o design, queremos entender melhor a personalidade e o estilo que mais combinam com
                o seu projeto. Para isso, preparamos um briefing de percepção rápido e visual — nada técnico, prometemos
                😄</p>

            <h2>💡 Como funciona</h2>
            <ul>
                <li>• Você verá algumas escalas com pontos, cada uma com dois conceitos opostos (por exemplo: tradicional ↔️ inovador, formal ↔️ descontraído).</li>
                <li>• Basta clicar no ponto que melhor representa o que você quer para o seu site:</li>
                <li> ◦ O ponto do meio indica equilíbrio entre os conceitos.</li>
                <li> ◦ Um ponto mais à esquerda ou à direita mostra qual estilo você quer que a gente priorize.</li>
                <li> ◦ Comentário opcional: se quiser, você pode deixar observações em relação àquele item.</li>
            </ul>

            <h2>✨ Vamos começar?</h2>
            <p>Clique no botão abaixo para acessar o briefing de percepção e nos contar mais sobre o estilo do seu
                projeto:</p>

            <p>
                <a class="btn" href="{!! $briefing_link !!}" target="_blank" rel="noopener">Responder ao Briefing</a>
            </p>

            <p class="muted">
                Se o botão não funcionar, copie e cole este link no seu navegador:<br>
                <span style="word-break: break-all;">{{ $briefing_link }}</span>
            </p>

            <p class="muted">
                Agradecemos mais uma vez por confiar na MM Criativos. Estamos empolgados para transformar suas ideias
                em uma experiência digital única. ✨
            </p>

            <p class="muted">
                Um abraço,<br>
                Equipe MM Criativos
            </p>
        </div>
    </div>
</body>

</html>
