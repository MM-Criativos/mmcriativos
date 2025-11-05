<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectQualitativeBriefingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct(Project $project, string $url)
    {
        $this->project = $project;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->markdown('emails.project.qualitative-briefing')
            ->subject('Continuação do Briefing - MM Criativos');
    }
}
