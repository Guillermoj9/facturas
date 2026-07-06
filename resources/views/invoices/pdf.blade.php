<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1f2937; padding: 40px 45px; }

        .header { width: 100%; margin-bottom: 35px; }
        .header td { vertical-align: top; }
        .logo { max-height: 60px; max-width: 200px; }
        .doc-title { font-size: 22pt; font-weight: bold; color: #111827; text-align: right; }
        .doc-number { font-size: 12pt; color: #3b82f6; font-weight: bold; text-align: right; margin-top: 2px; }
        .doc-dates { font-size: 9pt; color: #6b7280; text-align: right; margin-top: 8px; line-height: 1.5; }

        .parties { width: 100%; margin-bottom: 30px; }
        .parties td { width: 50%; vertical-align: top; padding-right: 20px; }
        .party-label { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: bold; margin-bottom: 6px; }
        .party-name { font-size: 11pt; font-weight: bold; color: #111827; margin-bottom: 3px; }
        .party-line { font-size: 9pt; color: #4b5563; line-height: 1.5; }

        .items { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items thead th { background: #111827; color: #ffffff; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 10px; text-align: left; }
        .items thead th.num, .items tbody td.num { text-align: right; }
        .items tbody td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 9.5pt; }

        .totals-wrap { width: 100%; }
        .totals { width: 260px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 5px 10px; font-size: 9.5pt; }
        .totals .amount { text-align: right; }
        .totals .muted { color: #6b7280; }
        .totals .irpf { color: #dc2626; }
        .totals .grand td { border-top: 2px solid #111827; font-size: 11.5pt; font-weight: bold; padding-top: 8px; }
        .totals .grand .amount { color: #3b82f6; }

        .payment { margin-top: 35px; padding: 14px 16px; background: #f3f4f6; border-radius: 6px; }
        .payment-label { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: bold; margin-bottom: 4px; }
        .payment p { font-size: 9.5pt; color: #374151; line-height: 1.6; }
        .iban { font-weight: bold; letter-spacing: 0.5px; }

        .notes { margin-top: 20px; font-size: 8.5pt; color: #6b7280; line-height: 1.5; }

        .footer { position: fixed; bottom: 20px; left: 45px; right: 45px; text-align: center; font-size: 7.5pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                @if ($setting->logo_path && file_exists(storage_path('app/public/' . $setting->logo_path)))
                    <img class="logo" src="{{ storage_path('app/public/' . $setting->logo_path) }}" alt="Logo">
                @else
                    <div class="party-name" style="font-size: 14pt;">{{ $setting->company_name }}</div>
                @endif
            </td>
            <td>
                <div class="doc-title">FACTURA</div>
                <div class="doc-number">Nº {{ $invoice->invoice_number }}</div>
                <div class="doc-dates">
                    Fecha de emisión: {{ $invoice->issue_date->format('d/m/Y') }}<br>
                    @if ($invoice->due_date)
                        Vencimiento: {{ $invoice->due_date->format('d/m/Y') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td>
                <div class="party-label">Emisor</div>
                <div class="party-name">{{ $setting->company_name }}</div>
                <div class="party-line">
                    NIF: {{ $setting->nif }}<br>
                    {{ $setting->address }}<br>
                    {{ $setting->postal_code }} {{ $setting->city }} ({{ $setting->province }})<br>
                    @if ($setting->phone) Tel: {{ $setting->phone }}<br> @endif
                    @if ($setting->email) {{ $setting->email }} @endif
                </div>
            </td>
            <td>
                <div class="party-label">Cliente</div>
                <div class="party-name">{{ $invoice->client->name }}</div>
                <div class="party-line">
                    NIF/CIF: {{ $invoice->client->nif_cif }}<br>
                    {{ $invoice->client->address }}<br>
                    {{ $invoice->client->postal_code }} {{ $invoice->client->city }} ({{ $invoice->client->province }})
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 50%;">Concepto</th>
                <th class="num" style="width: 13%;">Cantidad</th>
                <th class="num" style="width: 17%;">Precio ud.</th>
                <th class="num" style="width: 20%;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                    <td class="num">{{ euro($item->unit_price) }}</td>
                    <td class="num">{{ euro($item->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-wrap">
        <table class="totals">
            <tr>
                <td class="muted">Base imponible</td>
                <td class="amount">{{ euro($invoice->subtotal) }}</td>
            </tr>
            <tr>
                <td class="muted">IVA ({{ rtrim(rtrim(number_format((float) $invoice->iva_percentage, 2, ',', ''), '0'), ',') }}%)</td>
                <td class="amount">{{ euro($invoice->iva_amount) }}</td>
            </tr>
            <tr>
                <td class="muted">Retención IRPF ({{ rtrim(rtrim(number_format((float) $invoice->irpf_percentage, 2, ',', ''), '0'), ',') }}%)</td>
                <td class="amount irpf">−{{ euro($invoice->irpf_amount) }}</td>
            </tr>
            <tr class="grand">
                <td>TOTAL</td>
                <td class="amount">{{ euro($invoice->total) }}</td>
            </tr>
        </table>
    </div>

    <div class="payment">
        <div class="payment-label">Forma de pago</div>
        <p>
            {{ $invoice->payment_method ?? 'Transferencia bancaria' }}
            @if ($setting->iban)
                <br>IBAN: <span class="iban">{{ $setting->iban }}</span>
            @endif
        </p>
    </div>

    @if ($invoice->tax_note)
        <div class="notes"><strong>Nota fiscal:</strong> {{ $invoice->tax_note }}</div>
    @endif

    @if ($invoice->notes)
        <div class="notes">{{ $invoice->notes }}</div>
    @endif

    <div class="footer">
        {{ $setting->company_name }} · NIF {{ $setting->nif }} · {{ $setting->address }}, {{ $setting->postal_code }} {{ $setting->city }}
    </div>
</body>
</html>
