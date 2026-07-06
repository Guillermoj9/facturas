<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUSES = ['borrador', 'enviada', 'pagada', 'vencida'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'date',
            'subtotal' => 'decimal:2',
            'iva_percentage' => 'decimal:2',
            'iva_amount' => 'decimal:2',
            'irpf_percentage' => 'decimal:2',
            'irpf_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['borrador', 'enviada']);
    }

    /**
     * Recalcula subtotal, IVA, IRPF y total a partir de las líneas.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total');
        $iva = round($subtotal * $this->iva_percentage / 100, 2);
        $irpf = round($subtotal * $this->irpf_percentage / 100, 2);

        $this->update([
            'subtotal' => $subtotal,
            'iva_amount' => $iva,
            'irpf_amount' => $irpf,
            'total' => round($subtotal + $iva - $irpf, 2),
        ]);
    }

    /**
     * Marca como vencidas las facturas enviadas cuya fecha de vencimiento ya pasó.
     */
    public static function markOverdue(): void
    {
        static::query()
            ->where('status', 'enviada')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'vencida']);
    }

    /**
     * Siguiente número correlativo del año: prefijo + NN-AAAA.
     * Si cambia el año, la numeración empieza desde 1.
     */
    public static function nextNumber(?string $prefix = null, ?int $year = null): string
    {
        $prefix ??= Setting::current()?->invoice_prefix ?? '';
        $year ??= now()->year;

        $last = static::query()
            ->where('invoice_number', 'like', "%-{$year}")
            ->get()
            ->map(function ($invoice) use ($year) {
                if (preg_match('/(\d+)-' . $year . '$/', $invoice->invoice_number, $m)) {
                    return (int) $m[1];
                }

                return 0;
            })
            ->max() ?? 0;

        return sprintf('%s%02d-%d', $prefix, $last + 1, $year);
    }
}
