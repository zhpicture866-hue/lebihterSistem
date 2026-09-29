<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat tagihan tiap kali langganan dibuat/diperpanjang.
     * Mirip "Histori pembayaran" di hPanel Hostinger.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS lebihtersistem');
        Schema::create('lebihtersistem.subscription_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('lebihtersistem.subscriptions')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 12, 2);

            // draft -> waiting -> paid | failed | expired
            $table->string('status')->default('draft');

            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lebihtersistem.subscription_invoices');
    }
};
