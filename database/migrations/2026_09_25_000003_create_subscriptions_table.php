<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu baris = satu langganan customer terhadap satu sistem.
     *
     * ASUMSI: sudah ada tabel `customers` di database "lebihtersistem" (induk).
     * Kalau nama/struktur tabel pelanggan kamu beda, tinggal ganti baris
     * foreignId('customer_id') di bawah sesuai nama tabelnya.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS lebihtersistem');
        Schema::create('lebihtersistem.subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('system_id')->constrained('lebihtersistem.systems')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('lebihtersistem.plans')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();

            // active         -> berjalan normal
            // segera_berakhir -> sudah masuk periode notifikasi (H-7/H-3/H-1)
            // expired        -> lewat end_date & belum diperpanjang -> layanan distop
            // stopped        -> dihentikan manual (mis. oleh admin/customer)
            $table->string('status')->default('active');

            $table->date('start_date');
            $table->date('end_date');

            $table->boolean('auto_renew')->default(false);
            $table->unsignedBigInteger('payment_method_id')->nullable();

            // dipakai job notifikasi supaya tidak kirim notif berulang di hari yang sama
            $table->string('last_notified_stage')->nullable(); // 'h7' | 'h3' | 'h1' | 'expired'
            $table->timestamp('last_notified_at')->nullable();

            $table->timestamp('stopped_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lebihtersistem.subscriptions');
    }
};
