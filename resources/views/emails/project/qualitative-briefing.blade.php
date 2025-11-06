@component('mail::message')
    # Vamos continuar nossa jornada criativa! 🚀

    Olá {{ $project->client->name }},

    Muito obrigado por compartilhar suas percepções no formulário anterior!
    Agora, para deixar tudo ainda mais alinhado com suas necessidades, temos algumas perguntas mais específicas.

    São questões super importantes que vão nos ajudar a criar algo realmente incrível para você.
    É rapidinho, prometo! 😉

    @component('mail::button', ['url' => $url, 'color' => 'primary'])
        Responder Questionário
    @endcomponent

    Se tiver qualquer dúvida, pode nos chamar — estamos aqui para ajudar! 💬

    **Abraço criativo,**
    {{ config('app.name') }}
@endcomponent
