<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactFormController extends Controller
{
    public function send(Request $request)
    {
        // 🔹 1️⃣ Validação do formulário + reCAPTCHA
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255'],
            'whatsapp'  => ['nullable', 'string', 'max:255'],
            'service'   => ['nullable', 'string', 'max:255'],
            'message'   => ['required', 'string', 'max:5000'],
            'g-recaptcha-response' => ['required', 'string'],
        ]);

        Log::info('📨 Novo envio de formulário de contato recebido', [
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'] ?? null,
            'service' => $data['service'] ?? null,
        ]);

        // 🔹 2️⃣ Verificação do token junto ao Google
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => config('services.recaptcha.secret_key'),
                'response' => $data['g-recaptcha-response'],
                'remoteip' => $request->ip(),
            ])->json();

            Log::info('🧠 Resposta do Google reCAPTCHA', $response);

            // caso falhe ou score baixo (<0.5)
            if (!($response['success'] ?? false) || ($response['score'] ?? 0) < 0.5) {
                Log::warning('⚠️ Falha na verificação do reCAPTCHA', [
                    'score' => $response['score'] ?? null,
                    'success' => $response['success'] ?? false,
                ]);

                return back()
                    ->withErrors(['captcha' => 'Falha na verificação de segurança. Tente novamente.'])
                    ->withInput();
            }
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao validar reCAPTCHA', ['message' => $e->getMessage()]);

            return back()
                ->withErrors(['captcha' => 'Erro ao validar reCAPTCHA. Tente novamente.'])
                ->withInput();
        }

        // 🔹 3️⃣ Monta e envia o e-mail
        $to = env('CONTACT_TO', env('MAIL_FROM_ADDRESS', 'contato@mmcriativos.com.br'));
        $subject = 'Nova mensagem de contato - MM Criativos';
        $html = view('emails.contact', ['data' => $data])->render();

        Log::info('📧 Enviando e-mail de contato', [
            'to' => $to,
            'from' => $data['email'],
            'subject' => $subject,
        ]);

        try {
            Mail::html($html, function ($m) use ($to, $subject, $data) {
                $m->to($to)->subject($subject);
                $m->replyTo($data['email'], $data['name']);
            });

            Log::info('✅ E-mail enviado com sucesso para ' . $to);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar e-mail de contato', ['message' => $e->getMessage()]);

            return back()
                ->with('status', 'Não foi possível enviar sua mensagem agora.')
                ->withInput();
        }

        // 🔹 4️⃣ Retorno de sucesso
        Log::info('🏁 Fluxo de contato finalizado com sucesso', ['email' => $data['email']]);
        return back()->with('status', 'Mensagem enviada com sucesso! Em breve retornaremos.');
    }
}
