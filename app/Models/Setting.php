<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'irpf_default' => 'decimal:2',
            'iva_default' => 'decimal:2',
        ];
    }

    /**
     * La app trabaja con un único registro de configuración.
     */
    public static function current(): ?self
    {
        return static::query()->first();
    }
}
