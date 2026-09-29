<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Paket/harga langganan untuk tiap sistem.
     * Satu sistem boleh punya lebih dari satu plan (mis. Bulanan, Tahunan),
     * atau cukup satu plan flat kalau sistemnya tidak bertingkat.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS lebihtersistem');
        Schema::create('lebihtersistem.plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('system_id')->constrained('lebihtersistem.systems')->cascadeOnDelete();
            $table->string('name');                    // contoh: "Bulanan", "Tahunan"
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('duration_days');   // umur langganan sekali bayar, mis. 30 / 365
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lebihtersistem.plans');
    }
};
