<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('email_templates')->delete();

        DB::table('email_templates')->insert([
            // ============================================================
            // 📤 ORÇAMENTO ENVIADO
            // ============================================================
            [
                'key' => 'budget_sent',
                'name' => 'Envio de Orçamento',
                'subject' => 'Seu orçamento está pronto! 🚀',
                'body' => <<<HTML
<p>Olá <strong>{{client_name}}</strong>,</p>

<p>Esperamos que esteja bem!<br>
Seu orçamento nº <strong>{{budget_id}}</strong> foi preparado com base nas necessidades da sua empresa.</p>

<p>Você pode visualizar todos os detalhes, valores e condições clicando no botão abaixo:</p>

<p style="text-align:center;">
    <a href="{{public_link}}" style="background-color:#ff8800;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">
        Ver Orçamento
    </a>
</p>

<p>Este orçamento é válido até <strong>{{valid_until}}</strong>.</p>

<p>Qualquer dúvida, estamos à disposição.<br>
Equipe <strong>MM Criativos</strong></p>
HTML,
                'footer' => '<p style="font-size:13px;color:#777;">E-mail automático — não responda diretamente.</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // 👀 ORÇAMENTO VISUALIZADO
            // ============================================================
            [
                'key' => 'budget_opened',
                'name' => 'Orçamento Visualizado',
                'subject' => 'Seu orçamento foi visualizado 👀',
                'body' => <<<HTML
<p>Olá equipe <strong>MM Criativos</strong>,</p>

<p>O cliente <strong>{{client_name}}</strong> acabou de abrir o orçamento nº <strong>{{budget_id}}</strong>.</p>

<p>Isso significa que o cliente visualizou a proposta enviada e pode entrar em contato em breve.</p>

<p><a href="{{admin_link}}">Acessar orçamento no painel</a></p>
HTML,
                'footer' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // ✅ ORÇAMENTO ACEITO
            // ============================================================
            [
                'key' => 'budget_accepted',
                'name' => 'Confirmação de Aceite',
                'subject' => 'Orçamento aprovado 🎉',
                'body' => <<<HTML
<p>Olá <strong>{{client_name}}</strong>,</p>

<p>Perfeito! Seu orçamento nº <strong>{{budget_id}}</strong> foi <strong>aprovado</strong> em <strong>{{accepted_at}}</strong>.</p>

<p>Agradecemos pela confiança em nossa equipe!<br>
Em breve, entraremos em contato para alinhar os próximos passos do projeto.</p>

<p>Você pode consultar este orçamento a qualquer momento em:</p>
<p><a href="{{public_link}}">{{public_link}}</a></p>

<p>Atenciosamente,<br>
Equipe <strong>MM Criativos</strong></p>
HTML,
                'footer' => '<p style="font-size:13px;color:#777;">Estamos animados para começar!</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // ❌ ORÇAMENTO RECUSADO OU EXPIRADO
            // ============================================================
            [
                'key' => 'budget_declined',
                'name' => 'Orçamento Recusado / Expirado',
                'subject' => 'Seu orçamento expirou ou foi recusado 😔',
                'body' => <<<HTML
<p>Olá <strong>{{client_name}}</strong>,</p>

<p>Percebemos que o orçamento nº <strong>{{budget_id}}</strong> foi <strong>recusado</strong> ou expirou em <strong>{{expired_at}}</strong>.</p>

<p>Caso queira revisar valores, condições ou ajustar algo na proposta, é só responder este e-mail ou entrar em contato conosco.</p>

<p>Estamos à disposição para encontrar a melhor solução!</p>

<p>Atenciosamente,<br>
Equipe <strong>MM Criativos</strong></p>
HTML,
                'footer' => '<p style="font-size:13px;color:#777;">Esperamos te ver em um próximo projeto 💡</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
