<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function index()
    {
        Invoice::markOverdue();

        $now = now();
        $quarter = $now->quarter;
        $quarterStart = $now->copy()->firstOfQuarter()->startOfDay();
        $quarterEnd = $now->copy()->lastOfQuarter()->endOfDay();

        $paid = Invoice::query()->where('status', 'pagada');

        $stats = [
            'month' => (clone $paid)->whereBetween('issue_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('total'),
            'quarter' => (clone $paid)->whereBetween('issue_date', [$quarterStart, $quarterEnd])->sum('total'),
            'year' => (clone $paid)->whereBetween('issue_date', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->sum('total'),
            'pending' => Invoice::whereIn('status', ['enviada', 'vencida'])->sum('total'),
            'invoices_month' => Invoice::whereBetween('issue_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count(),
        ];

        // IVA repercutido e IRPF retenido del trimestre (facturas emitidas, criterio de devengo)
        $quarterInvoices = Invoice::where('status', '!=', 'borrador')
            ->whereBetween('issue_date', [$quarterStart, $quarterEnd]);

        $taxes = [
            'iva_repercutido' => (clone $quarterInvoices)->sum('iva_amount'),
            'irpf_retenido' => (clone $quarterInvoices)->sum('irpf_amount'),
            'iva_soportado' => Expense::where('deductible', true)
                ->whereBetween('date', [$quarterStart, $quarterEnd])
                ->sum('iva_amount'),
        ];
        $taxes['iva_a_pagar'] = $taxes['iva_repercutido'] - $taxes['iva_soportado'];

        // Facturación mensual de los últimos 12 meses (facturas no borrador)
        $monthlyChart = collect(range(11, 0))->map(function ($i) {
            $month = now()->copy()->subMonths($i);

            return [
                'label' => ucfirst($month->translatedFormat('M y')),
                'total' => (float) Invoice::where('status', '!=', 'borrador')
                    ->whereBetween('issue_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->sum('total'),
            ];
        });

        // Facturación por trimestre del año actual
        $quarterlyChart = collect([1, 2, 3, 4])->map(function ($q) use ($now) {
            $start = $now->copy()->month(($q - 1) * 3 + 1)->startOfMonth()->startOfDay();
            $end = $start->copy()->addMonths(2)->endOfMonth()->endOfDay();

            return [
                'label' => "T{$q} {$now->year}",
                'total' => (float) Invoice::where('status', '!=', 'borrador')
                    ->whereBetween('issue_date', [$start, $end])
                    ->sum('total'),
            ];
        });

        return view('dashboard', [
            'stats' => $stats,
            'taxes' => $taxes,
            'quarter' => $quarter,
            'monthlyChart' => $monthlyChart,
            'quarterlyChart' => $quarterlyChart,
            'latestInvoices' => Invoice::with('client')->latest('issue_date')->latest('id')->limit(5)->get(),
            'pendingInvoices' => Invoice::with('client')->whereIn('status', ['enviada', 'vencida'])->oldest('due_date')->get(),
        ]);
    }
}
