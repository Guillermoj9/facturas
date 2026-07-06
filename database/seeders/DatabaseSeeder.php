<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'company_name' => 'María García López',
            'nif' => '12345678Z',
            'address' => 'Calle Mayor 15, 3ºB',
            'city' => 'Madrid',
            'postal_code' => '28013',
            'province' => 'Madrid',
            'phone' => '600 123 456',
            'email' => 'maria@ejemplo.es',
            'iban' => 'ES91 2100 0418 4502 0005 1332',
            'irpf_default' => 15,
            'iva_default' => 21,
            'invoice_prefix' => '',
        ]);

        $clients = collect([
            [
                'name' => 'Tecnologías Acme S.L.',
                'nif_cif' => 'B87654321',
                'address' => 'Avenida de la Innovación 42',
                'city' => 'Barcelona',
                'postal_code' => '08018',
                'province' => 'Barcelona',
                'email' => 'facturacion@acme.es',
                'phone' => '931 234 567',
            ],
            [
                'name' => 'Estudio Creativo Norte',
                'nif_cif' => 'B12398745',
                'address' => 'Calle Gran Vía 8, 2º',
                'city' => 'Bilbao',
                'postal_code' => '48001',
                'province' => 'Vizcaya',
                'email' => 'admin@estudionorte.es',
                'phone' => '944 567 890',
            ],
            [
                'name' => 'Juan Pérez Consultores',
                'nif_cif' => '87654321X',
                'address' => 'Plaza del Ayuntamiento 3',
                'city' => 'Valencia',
                'postal_code' => '46002',
                'province' => 'Valencia',
                'email' => 'juan@jpconsultores.es',
                'phone' => null,
            ],
        ])->map(fn ($data) => Client::create($data));

        $invoices = [
            [
                'client' => 0,
                'issue_date' => now()->subMonths(4)->startOfMonth()->addDays(9),
                'status' => 'pagada',
                'items' => [
                    ['description' => 'Desarrollo web corporativa', 'quantity' => 1, 'unit_price' => 2400],
                    ['description' => 'Mantenimiento mensual', 'quantity' => 3, 'unit_price' => 150],
                ],
            ],
            [
                'client' => 1,
                'issue_date' => now()->subMonths(2)->startOfMonth()->addDays(14),
                'status' => 'pagada',
                'items' => [
                    ['description' => 'Diseño de identidad visual', 'quantity' => 1, 'unit_price' => 1800],
                ],
            ],
            [
                'client' => 2,
                'issue_date' => now()->subMonth()->startOfMonth()->addDays(4),
                'status' => 'pagada',
                'items' => [
                    ['description' => 'Consultoría técnica', 'quantity' => 12, 'unit_price' => 65],
                ],
            ],
            [
                'client' => 0,
                'issue_date' => now()->subDays(20),
                'status' => 'enviada',
                'items' => [
                    ['description' => 'Desarrollo módulo de reservas', 'quantity' => 1, 'unit_price' => 1500],
                    ['description' => 'Horas extra de soporte', 'quantity' => 5, 'unit_price' => 55],
                ],
            ],
            [
                'client' => 1,
                'issue_date' => now()->subDays(3),
                'status' => 'borrador',
                'items' => [
                    ['description' => 'Sesión de fotografía de producto', 'quantity' => 1, 'unit_price' => 450],
                ],
            ],
        ];

        foreach ($invoices as $data) {
            $issueDate = $data['issue_date'];

            $invoice = Invoice::create([
                'invoice_number' => Invoice::nextNumber(year: $issueDate->year),
                'client_id' => $clients[$data['client']]->id,
                'issue_date' => $issueDate,
                'due_date' => $issueDate->copy()->addDays(30),
                'status' => $data['status'],
                'iva_percentage' => 21,
                'irpf_percentage' => 15,
                'payment_method' => 'Transferencia bancaria',
                'paid_at' => $data['status'] === 'pagada' ? $issueDate->copy()->addDays(15) : null,
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    ...$item,
                    'total' => round($item['quantity'] * $item['unit_price'], 2),
                ]);
            }

            $invoice->recalculateTotals();
        }

        $expenses = [
            ['description' => 'Hosting anual VPS', 'amount' => 240, 'iva_amount' => 50.40, 'date' => now()->subMonths(3), 'category' => 'hosting'],
            ['description' => 'Licencia Adobe Creative Cloud', 'amount' => 60.49, 'iva_amount' => 12.70, 'date' => now()->subMonths(1), 'category' => 'software'],
            ['description' => 'Monitor 27" para oficina', 'amount' => 289, 'iva_amount' => 60.69, 'date' => now()->subDays(45), 'category' => 'material'],
            ['description' => 'Tren Madrid-Barcelona reunión cliente', 'amount' => 85.50, 'iva_amount' => 8.55, 'date' => now()->subDays(12), 'category' => 'transporte'],
            ['description' => 'Curso online de Laravel avanzado', 'amount' => 149, 'iva_amount' => 31.29, 'date' => now()->subDays(5), 'category' => 'formación'],
        ];

        foreach ($expenses as $expense) {
            Expense::create([...$expense, 'deductible' => true]);
        }
    }
}
