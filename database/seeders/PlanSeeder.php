<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Service;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // 🟠 Presença Digital
            [
                'service' => 'Landing Page',
                'category' => 'Presença Digital',
                'price' => 3000,
                'description' => 'Foco em venda imediata, conversão de campanhas',
            ],
            [
                'service' => 'Site Single Page',
                'category' => 'Presença Digital',
                'price' => 4000,
                'description' => 'Foco em autoridade e apresentação compacta',
            ],
            [
                'service' => 'Site MultiPage',
                'category' => 'Presença Digital',
                'price' => 5000,
                'description' => 'Foco em profundidade e conteúdo contínuo',
            ],

            // 🟣 Soluções Inteligentes
            [
                'service' => 'Portal', // Plataforma Básica
                'category' => 'Soluções Inteligentes',
                'price' => 15000,
                'description' => 'Foco em gestão dinâmica e atualização contínua de conteúdo',
            ],
            [
                'service' => 'Sistema Personalizado', // Sistema Empresarial
                'category' => 'Soluções Inteligentes',
                'price' => 40000,
                'description' => 'Foco em automatizar processos e centralizar operações internas',
            ],
            [
                'service' => 'SaaS e Integrações',
                'category' => 'Soluções Inteligentes',
                'price' => 100000,
                'description' => 'Foco em criar sua plataforma como um produto digital escalável',
            ],
        ];

        foreach ($plans as $data) {
            $service = Service::where('name', $data['service'])->first();

            if (!$service) {
                $this->command->warn("Serviço não encontrado: {$data['service']}");
                continue;
            }

            Plan::updateOrCreate(
                ['service_id' => $service->id],
                [
                    'category' => $data['category'],
                    'price' => $data['price'],
                    'description' => $data['description'],
                ]
            );
        }
    }
}
