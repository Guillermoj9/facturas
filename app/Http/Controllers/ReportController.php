<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $invoices = Invoice::with('client')
            ->where('status', '!=', 'borrador')
            ->whereBetween('issue_date', [$from, $to])
            ->orderBy('issue_date')
            ->get();

        $byClient = $invoices->groupBy('client_id')
            ->map(fn ($group) => [
                'client' => $group->first()->client,
                'count' => $group->count(),
                'subtotal' => $group->sum('subtotal'),
                'total' => $group->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();

        $expenses = Expense::whereBetween('date', [$from, $to])->get();

        // Resumen trimestral del año seleccionado (para modelos 303 y 130)
        $year = (int) $request->input('year', now()->year);
        $quarters = collect([1, 2, 3, 4])->map(function ($q) use ($year) {
            $start = now()->setDate($year, ($q - 1) * 3 + 1, 1)->startOfDay();
            $end = $start->copy()->addMonths(2)->endOfMonth()->endOfDay();

            $base = Invoice::where('status', '!=', 'borrador')->whereBetween('issue_date', [$start, $end]);

            return [
                'label' => "T{$q}",
                'subtotal' => (clone $base)->sum('subtotal'),
                'iva' => (clone $base)->sum('iva_amount'),
                'irpf' => (clone $base)->sum('irpf_amount'),
                'iva_soportado' => Expense::where('deductible', true)->whereBetween('date', [$start, $end])->sum('iva_amount'),
            ];
        });

        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'year' => $year,
            'years' => $this->availableYears(),
            'invoices' => $invoices,
            'byClient' => $byClient,
            'totals' => [
                'subtotal' => $invoices->sum('subtotal'),
                'iva' => $invoices->sum('iva_amount'),
                'irpf' => $invoices->sum('irpf_amount'),
                'total' => $invoices->sum('total'),
                'expenses' => $expenses->sum('amount'),
                'profit' => $invoices->sum('subtotal') - $expenses->sum('amount'),
            ],
            'quarters' => $quarters,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $invoices = Invoice::with('client')
            ->where('status', '!=', 'borrador')
            ->whereBetween('issue_date', [$from, $to])
            ->orderBy('issue_date')
            ->get();

        $filename = sprintf('facturas_%s_%s.csv', $from->format('Y-m-d'), $to->format('Y-m-d'));

        return response()->streamDownload(function () use ($invoices) {
            $out = fopen('php://output', 'w');
            // BOM para que Excel abra el CSV en UTF-8
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Número', 'Fecha', 'Cliente', 'NIF/CIF', 'Base imponible', '% IVA', 'IVA', '% IRPF', 'IRPF', 'Total', 'Estado', 'Fecha de pago'], ';');

            foreach ($invoices as $invoice) {
                fputcsv($out, [
                    $invoice->invoice_number,
                    $invoice->issue_date->format('d/m/Y'),
                    $invoice->client->name,
                    $invoice->client->nif_cif,
                    number_format((float) $invoice->subtotal, 2, ',', ''),
                    number_format((float) $invoice->iva_percentage, 2, ',', ''),
                    number_format((float) $invoice->iva_amount, 2, ',', ''),
                    number_format((float) $invoice->irpf_percentage, 2, ',', ''),
                    number_format((float) $invoice->irpf_amount, 2, ',', ''),
                    number_format((float) $invoice->total, 2, ',', ''),
                    $invoice->status,
                    $invoice->paid_at?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $request): array
    {
        $from = $request->filled('from')
            ? \Illuminate\Support\Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfYear();

        $to = $request->filled('to')
            ? \Illuminate\Support\Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function availableYears(): array
    {
        $years = Invoice::query()
            ->pluck('issue_date')
            ->map(fn ($date) => $date->year)
            ->all();

        return collect([...$years, now()->year])->unique()->sortDesc()->values()->all();
    }
}
