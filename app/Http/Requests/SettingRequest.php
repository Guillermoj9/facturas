<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'nif' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'province' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'iban' => ['nullable', 'string', 'max:40'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'irpf_default' => ['required', 'numeric', 'min:0', 'max:100'],
            'iva_default' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'nombre completo',
            'nif' => 'DNI/NIF',
            'address' => 'dirección',
            'city' => 'ciudad',
            'postal_code' => 'código postal',
            'province' => 'provincia',
            'phone' => 'teléfono',
            'iban' => 'IBAN',
            'logo' => 'logo',
            'irpf_default' => 'IRPF',
            'iva_default' => 'IVA por defecto',
            'invoice_prefix' => 'prefijo de facturas',
        ];
    }

    /**
     * Datos validados listos para guardar (gestiona la subida del logo).
     */
    public function settingData(?string $currentLogo = null): array
    {
        $data = collect($this->validated())->except('logo')->all();
        $data['invoice_prefix'] ??= '';

        if ($this->hasFile('logo')) {
            $data['logo_path'] = $this->file('logo')->store('logos', 'public');
        } else {
            $data['logo_path'] = $currentLogo;
        }

        return $data;
    }
}
