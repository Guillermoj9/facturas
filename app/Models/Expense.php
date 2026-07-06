<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const CATEGORIES = ['hosting', 'software', 'material', 'transporte', 'formación', 'otros'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'iva_amount' => 'decimal:2',
            'deductible' => 'boolean',
        ];
    }
}
