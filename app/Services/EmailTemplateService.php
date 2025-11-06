<?php

namespace App\Services;

use App\Mail\BudgetMail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailTemplateService
{
    public static function send($templateKey, $to, $variables = [])
    {
        $template = EmailTemplate::where('key', $templateKey)->firstOrFail();

        $parsed = [
            'subject' => self::parse($template->subject ?? '', $variables),
            'title' => self::parse($template->title ?? '', $variables),
            'body' => self::parse($template->body ?? '', $variables),
            'button_label' => self::parse($template->button_label ?? '', $variables),
            'button_url' => self::parse($template->button_url ?? '', $variables),
            'footer' => self::parse($template->footer ?? '', $variables),
            'valid_until' => $variables['valid_until'] ?? null,
        ];

        if (Str::startsWith($templateKey, 'budget_')) {
            $html = view('emails.project.budget-template', $parsed)->render();
        } else {
            $html = $parsed['body'] . $parsed['footer'];
        }

        try {
            Mail::to($to)->send(new BudgetMail($parsed['subject'], $html));
        } catch (TransportExceptionInterface $exception) {
            Log::warning('Falha ao enviar e-mail de template', [
                'template' => $templateKey,
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected static function parse($text, $variables)
    {
        $text = $text ?? '';

        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }
}
