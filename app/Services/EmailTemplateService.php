<?php

namespace App\Services;

use App\Mail\BudgetMail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    public static function send($templateKey, $to, $variables = [])
    {
        $template = EmailTemplate::where('key', $templateKey)->firstOrFail();

        $subject = self::parse($template->subject, $variables);
        $body = self::parse($template->body, $variables);
        $footer = self::parse($template->footer ?? '', $variables);

        $html = $body . $footer;

        Mail::to($to)->send(new BudgetMail($subject, $html));
    }

    protected static function parse($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }
}
