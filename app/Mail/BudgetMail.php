<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class BudgetMail extends Mailable
{
    public $subjectText;
    public $bodyContent;

    public function __construct($subjectText, $bodyContent)
    {
        $this->subjectText = $subjectText;
        $this->bodyContent = $bodyContent;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->html($this->bodyContent);
    }
}
