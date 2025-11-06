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
            [
                'key' => 'budget_sent',
                'name' => 'Envio de Orçamento',
                'subject' => 'Proposta comercial disponível',
                'title' => 'Proposta comercial disponível',
                'body' => <<<HTML
<p>Olá <strong>{{client_name}}</strong>,</p>
<p>Esperamos que esteja bem!</p>
<p>Seu orçamento nº <strong>{{budget_id}}</strong> foi preparado com base nas necessidades da sua empresa.</p>
<p>Você pode visualizar todos os detalhes, valores e condições clicando no botão abaixo.</p>
HTML,
                'button_label' => 'Ver orçamento',
                'button_url' => '{{public_link}}',
                'footer' => '<p>Qualquer dúvida, estamos à disposição.<br>Equipe <strong>{{company_name}}</strong></p><p class="footer-note">E-mail automático — não responda diretamente.</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'budget_opened',
                'name' => 'Orçamento Visualizado',
                'subject' => 'Seu orçamento foi visualizado',
                'title' => 'Orçamento visualizado',
                'body' => <<<HTML
<p>Olá equipe <strong>{{company_name}}</strong>,</p>
<p>O cliente <strong>{{client_name}}</strong> acabou de abrir o orçamento nº <strong>{{budget_id}}</strong>.</p>
<p>Isso significa que o cliente visualizou a proposta enviada e pode entrar em contato em breve.</p>
HTML,
                'button_label' => 'Ver no painel',
                'button_url' => '{{admin_link}}',
                'footer' => '<p>Atenciosamente,<br><strong>{{company_name}}</strong></p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'budget_accepted',
                'name' => 'Confirmação de Aceite',
                'subject' => 'Orçamento aprovado',
                'title' => 'Orçamento aprovado',
                'body' => <<<HTML
<p>Olá <strong>{{client_name}}</strong>,</p>
<p>Perfeito! Seu orçamento nº <strong>{{budget_id}}</strong> foi <strong>aprovado</strong> em <strong>{{accepted_at}}</strong>.</p>
<p>Agradecemos pela confiança em nossa equipe! Em breve, entraremos em contato para alinhar os próximos passos do projeto.</p>
HTML,
                'button_label' => 'Ver orçamento',
                'button_url' => '{{public_link}}',
                'footer' => '<p>Estamos animados para começar!<br><strong>{{company_name}}</strong></p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'budget_declined',
                'name' => 'Orçamento Recusado / Expirado',
                'subject' => 'Seu orçamento expirou ou foi recusado',
                'title' => 'Orçamento expirado ou recusado',
                'body' => <<<HTML
<p>Olá <strong>{{client_name}}</strong>,</p>
<p>Percebemos que o orçamento nº <strong>{{budget_id}}</strong> foi <strong>recusado</strong> em <strong>{{declined_at}}</strong>.</p>
<p>Caso queira revisar valores, condições ou ajustar algo na proposta, é só entrar em contato conosco.</p>
<p>Estamos à disposição para encontrar a melhor solução!</p>
HTML,
                'button_label' => 'Solicitar novo orçamento',
                'button_url' => '{{contact_link}}',
                'footer' => '<p>Esperamos te ver em um próximo projeto.<br><strong>{{company_name}}</strong></p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'budget_expired',
                'name' => 'Orçamento Expirado',
                'subject' => 'Seu orçamento expirou',
                'title' => 'Orçamento expirado',
                'body' => <<<HTML
<p>Olá <strong>{{client_name}}</strong>,</p>
<p>O orçamento nº <strong>{{budget_id}}</strong> expirou em <strong>{{declined_at}}</strong>.</p>
<p>Caso deseje uma nova proposta ou ajustar condições, responda a este e-mail ou fale conosco.</p>
HTML,
                'button_label' => 'Solicitar novo orçamento',
                'button_url' => '{{contact_link}}',
                'footer' => '<p>Estamos prontos para ajudar.<br><strong>{{company_name}}</strong></p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
