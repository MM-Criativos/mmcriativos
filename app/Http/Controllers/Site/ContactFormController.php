<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'whatsapp' => ['nullable','string','max:255'],
            'service' => ['nullable','string','max:255'],
            'message' => ['required','string','max:5000'],
        ]);

        $to = env('CONTACT_TO', env('MAIL_FROM_ADDRESS', 'contato@mmcriativos.com.br'));
        $subject = 'Nova mensagem de contato - MM Criativos';

        $html = view('emails.contact', [ 'data' => $data ])->render();

        try {
            Mail::html($html, function ($m) use ($to, $subject, $data) {
                $m->to($to)->subject($subject);
                $m->replyTo($data['email'], $data['name']);
            });
        } catch (\Throwable $e) {
            return back()->with('status', 'Não foi possível enviar sua mensagem agora.')->withInput();
        }

        return back()->with('status', 'Mensagem enviada com sucesso! Em breve retornaremos.');
    }
}

