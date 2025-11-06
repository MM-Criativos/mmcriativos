<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? ($title ?? config('app.name')) }}</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;
           background: #f3f4f6;
           color: #111827;
       }

       .wrapper {
           width: 100%;
           padding: 32px 0;
           text-align: center;
       }

       .card {
           max-width: 640px;
           margin: 0 auto;
           background: #ffffff;
           border-radius: 20px;
           padding: 38px 32px;
           box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
           text-align: center;
       }

       .logo {
           height: 60px;
           margin-bottom: 24px;
       }

       h1 {
           font-size: 24px;
           margin: 0 0 18px;
           color: #0f172a;
       }

       p {
           line-height: 1.7;
           margin: 14px 0;
           color: #1f2937;
       }

       .btn {
           display: inline-block;
           padding: 14px 32px;
           background: #ea580c;
           color: #ffffff !important;
           text-decoration: none;
           border-radius: 999px;
           font-weight: 600;
           margin-top: 22px;
       }

       .muted {
           color: #6b7280;
           font-size: 14px;
           margin-top: 22px;
       }

        .body-copy {
            margin: 12px 0 26px;
        }

       .body-copy p {
           text-align: center;
       }

       .footer-note {
           font-size: 12px;
           color: #9ca3af;
           margin-top: 12px;
       }
   </style>
</head>

<body>
    <div class="wrapper">
        <div class="card">
            <img src="{{ asset('assets/images/mmsite.png') }}" alt="{{ config('app.name') }}" class="logo">

            @if (!empty($title))
                <h1>{{ $title }}</h1>
            @endif

            @if (!empty($body))
                <div class="body-copy">
                    {!! $body !!}
                </div>
            @endif

            @if (!empty($button_url) && !empty($button_label))
                <a class="btn" href="{{ $button_url }}" target="_blank" rel="noopener">{{ $button_label }}</a>
            @endif

            @if (!empty($valid_until))
                <p class="muted">Este orçamento é válido até {{ $valid_until }}.</p>
            @endif

            @if (!empty($footer))
                <div class="body-copy">
                    {!! $footer !!}
                </div>
            @endif
        </div>
    </div>
</body>

</html>
