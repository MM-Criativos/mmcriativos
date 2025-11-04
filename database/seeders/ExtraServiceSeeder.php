<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtraServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('extra_service')->delete();

        $extras = DB::table('extras')->pluck('id', 'name');

        $data = [];

        // Landing Page (id = 1)
        foreach (
            [
                ['name' => 'Hospedagem anual'],
                ['name' => 'Deploy e configuração', 'custom_discount' => 100],
                ['name' => 'Certificado SSL'],
                ['name' => 'Registro de domínio'],
                ['name' => 'SEO Avançado'],
                ['name' => 'Copywriting estratégico'],
                ['name' => 'Animações e microinterações'],
                ['name' => 'Design responsivo premium'],
                ['name' => 'E-mails corporativos'],
            ] as $e
        ) {
            $data[] = [
                'extra_id' => $extras[$e['name']],
                'service_id' => 1, // ✅ ID fixo
                'custom_price' => $e['custom_price'] ?? null,
                'custom_discount' => $e['custom_discount'] ?? null,
                'is_default' => false,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Site Single Page (id = 2)
        foreach (
            [
                ['name' => 'Hospedagem anual'],
                ['name' => 'Deploy e configuração'],
                ['name' => 'SEO Avançado'],
                ['name' => 'Copywriting estratégico'],
                ['name' => 'Animações e microinterações'],
                ['name' => 'Backup automatizado'],
                ['name' => 'Certificado SSL'],
            ] as $e
        ) {
            $data[] = [
                'extra_id' => $extras[$e['name']],
                'service_id' => 2,
                'custom_price' => $e['custom_price'] ?? null,
                'custom_discount' => $e['custom_discount'] ?? null,
                'is_default' => false,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Site Multipage (id = 3)
        foreach (
            [
                ['name' => 'Hospedagem anual'],
                ['name' => 'Backup automatizado'],
                ['name' => 'Deploy e configuração'],
                ['name' => 'SEO Avançado'],
                ['name' => 'Copywriting estratégico'],
                ['name' => 'Tradução multilíngue'],
                ['name' => 'Animações e microinterações'],
                ['name' => 'Design responsivo premium'],
            ] as $e
        ) {
            $data[] = [
                'extra_id' => $extras[$e['name']],
                'service_id' => 3,
                'custom_price' => $e['custom_price'] ?? null,
                'custom_discount' => $e['custom_discount'] ?? null,
                'is_default' => false,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Portal (id = 4)
        foreach (
            [
                ['name' => 'Hospedagem anual', 'custom_price' => 800],
                ['name' => 'Backup automatizado'],
                ['name' => 'Deploy e configuração'],
                ['name' => 'Integração com API externa'],
                ['name' => 'Automação via n8n ou Zapier'],
                ['name' => 'Login social (Google/LinkedIn)'],
                ['name' => 'Dashboard analítico'],
                ['name' => 'Integração com Google Data Studio ou Power BI'],
                ['name' => 'Suporte mensal'],
                ['name' => 'Treinamento do cliente'],
            ] as $e
        ) {
            $data[] = [
                'extra_id' => $extras[$e['name']],
                'service_id' => 4,
                'custom_price' => $e['custom_price'] ?? null,
                'custom_discount' => $e['custom_discount'] ?? null,
                'is_default' => false,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Sistema Personalizado (id = 5)
        foreach (
            [
                ['name' => 'Hospedagem anual', 'custom_price' => 1000],
                ['name' => 'Backup automatizado'],
                ['name' => 'Deploy e configuração'],
                ['name' => 'Integração com API externa'],
                ['name' => 'Automação via n8n ou Zapier'],
                ['name' => 'Dashboard analítico'],
                ['name' => 'Integração com Google Data Studio ou Power BI'],
                ['name' => 'Integração com gateway de pagamento'],
                ['name' => 'Suporte mensal'],
                ['name' => 'Treinamento do cliente'],
            ] as $e
        ) {
            $data[] = [
                'extra_id' => $extras[$e['name']],
                'service_id' => 5,
                'custom_price' => $e['custom_price'] ?? null,
                'custom_discount' => $e['custom_discount'] ?? null,
                'is_default' => false,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // SaaS e Integrações (id = 6)
        foreach (
            [
                ['name' => 'Hospedagem anual', 'custom_price' => 1200],
                ['name' => 'Backup automatizado'],
                ['name' => 'Deploy e configuração'],
                ['name' => 'Integração com API externa'],
                ['name' => 'Automação via n8n ou Zapier'],
                ['name' => 'Login social (Google/LinkedIn)'],
                ['name' => 'Dashboard analítico'],
                ['name' => 'Integração com Google Data Studio ou Power BI'],
                ['name' => 'Integração com gateway de pagamento'],
                ['name' => 'Domínio customizado (CNAME)'],
                ['name' => 'White Label completo'],
                ['name' => 'Suporte mensal'],
                ['name' => 'Treinamento do cliente'],
            ] as $e
        ) {
            $data[] = [
                'extra_id' => $extras[$e['name']],
                'service_id' => 6,
                'custom_price' => $e['custom_price'] ?? null,
                'custom_discount' => $e['custom_discount'] ?? null,
                'is_default' => false,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('extra_service')->insert($data);
    }
}
