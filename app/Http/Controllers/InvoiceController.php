<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        Invoice::markOverdue();

        $invoices = Invoice::query()
            ->with('client')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q');
                $query->where(fn ($w) => $w
                    ->where('invoice_number', 'like', "%{$q}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$q}%")));
            })
            ->latest('issue_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $setting = Setting::current();

        return view('invoices.create', [
            'clients' => Client::orderBy('name')->get(),
            'nextNumber' => Invoice::nextNumber(),
            'ivaDefault' => $setting->iva_default,
            'irpfDefault' => $setting->irpf_default,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $invoice = DB::transaction(function () use ($data, $request) {
            $clientId = $this->resolveClient($request, $data);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::nextNumber(),
                'client_id' => $clientId,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'iva_percentage' => $data['iva_percentage'],
                'irpf_percentage' => $data['irpf_percentage'],
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
            ]);

            $this->syncItems($invoice, $data['items']);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Factura {$invoice->invoice_number} creada.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('client', 'items');

        return view('invoices.show', ['invoice' => $invoice, 'setting' => Setting::current()]);
    }

    public function edit(Invoice $invoice)
    {
        if (! $invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Solo se pueden editar facturas en borrador o enviadas.');
        }

        return view('invoices.edit', [
            'invoice' => $invoice->load('items'),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (! $invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Solo se pueden editar facturas en borrador o enviadas.');
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($invoice, $data, $request) {
            $invoice->update([
                'client_id' => $this->resolveClient($request, $data),
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'iva_percentage' => $data['iva_percentage'],
                'irpf_percentage' => $data['irpf_percentage'],
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
            ]);

            $invoice->items()->delete();
            $this->syncItems($invoice, $data['items']);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Factura actualizada.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden eliminar facturas en borrador.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Borrador eliminado.');
    }

    public function duplicate(Invoice $invoice)
    {
        $copy = DB::transaction(function () use ($invoice) {
            $copy = $invoice->replicate(['invoice_number', 'status', 'paid_at']);
            $copy->invoice_number = Invoice::nextNumber();
            $copy->status = 'borrador';
            $copy->issue_date = today();
            $copy->due_date = $invoice->due_date
                ? today()->addDays((int) $invoice->issue_date->diffInDays($invoice->due_date))
                : null;
            $copy->paid_at = null;
            $copy->save();

            foreach ($invoice->items as $item) {
                $copy->items()->create($item->only(['description', 'quantity', 'unit_price', 'total']));
            }

            return $copy;
        });

        return redirect()->route('invoices.edit', $copy)
            ->with('success', "Factura duplicada como {$copy->invoice_number} (borrador).");
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Invoice::STATUSES)],
        ]);

        $invoice->update([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'pagada' ? ($invoice->paid_at ?? today()) : null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => $invoice->status, 'paid_at' => $invoice->paid_at?->format('d/m/Y')]);
        }

        return back()->with('success', "Factura marcada como {$invoice->status}.");
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('client', 'items');

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'setting' => Setting::current(),
        ])->setPaper('a4');

        $filename = 'factura_' . str_replace(['/', '\\'], '-', $invoice->invoice_number) . '.pdf';

        return request()->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required'],
            'new_client.name' => ['required_if:client_id,new', 'nullable', 'string', 'max:255'],
            'new_client.nif_cif' => ['required_if:client_id,new', 'nullable', 'string', 'max:20'],
            'new_client.address' => ['required_if:client_id,new', 'nullable', 'string', 'max:255'],
            'new_client.city' => ['required_if:client_id,new', 'nullable', 'string', 'max:100'],
            'new_client.postal_code' => ['required_if:client_id,new', 'nullable', 'string', 'max:10'],
            'new_client.province' => ['required_if:client_id,new', 'nullable', 'string', 'max:100'],
            'new_client.email' => ['nullable', 'email', 'max:255'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'iva_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'irpf_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Añade al menos una línea de concepto.',
            'items.*.description.required' => 'Cada línea necesita una descripción.',
            'items.*.quantity.gt' => 'La cantidad debe ser mayor que 0.',
        ], [
            'client_id' => 'cliente',
            'issue_date' => 'fecha de emisión',
            'due_date' => 'fecha de vencimiento',
            'iva_percentage' => 'IVA',
            'irpf_percentage' => 'IRPF',
            'payment_method' => 'forma de pago',
            'new_client.name' => 'nombre del cliente',
            'new_client.nif_cif' => 'NIF/CIF del cliente',
            'new_client.address' => 'dirección del cliente',
            'new_client.city' => 'ciudad del cliente',
            'new_client.postal_code' => 'código postal del cliente',
            'new_client.province' => 'provincia del cliente',
        ]);
    }

    /**
     * Devuelve el id del cliente seleccionado, creándolo si se rellenó inline.
     */
    private function resolveClient(Request $request, array $data): int
    {
        if ($data['client_id'] === 'new') {
            return Client::create($data['new_client'])->id;
        }

        return Client::findOrFail($data['client_id'])->id;
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => round($item['quantity'] * $item['unit_price'], 2),
            ]);
        }

        $invoice->recalculateTotals();
    }
}
