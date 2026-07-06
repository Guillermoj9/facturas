<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('nif');
            $table->string('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('province');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('iban')->nullable();
            $table->string('logo_path')->nullable();
            $table->decimal('irpf_default', 5, 2)->default(15);
            $table->decimal('iva_default', 5, 2)->default(21);
            $table->string('invoice_prefix')->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
