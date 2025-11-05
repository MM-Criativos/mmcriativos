<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectPerceptionBriefingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clientName;
    public string $briefingLink;

    public function __construct(string $clientName, string $briefingLink)
    {
        $this->clientName = $clientName;
        $this->briefingLink = $briefingLink;
        $this->subject('🎉 Seja bem-vindo(a)! Vamos começar a construir o seu novo site 🚀');
    }

    public function build()
    {
        return $this->view('emails.project_perception_briefing')
            ->with([
                'client_name' => $this->clientName,
                'briefing_link' => $this->briefingLink,
            ]);
    }
}

