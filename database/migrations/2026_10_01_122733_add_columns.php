<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simpan sebagai: database/migrations/2026_10_01_000000_add_subscription_columns_to_build_termins.php
 *
 * Status bayar TIDAK disimpan di sini: termin dianggap lunas kalau invoice-nya
 * (invoicebuilds.termin = termin_no) berstatus 'approved'.
 *
 * Sesuaikan nama tabel kalau berbeda dari 'lebihtersistem.build_termins'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lebihtersistem.build_termins', function (Blueprint $table) {
            // Satuan periode termin ini: monthly | annual
            $table->string('billing_period', 10)->default('monthly');

            // Masa layanan yang ditagih oleh termin ini
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            // Keputusan setelah lunas: null | continued | stopped
            $table->string('renewal_decision', 20)->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by', 36)->nullable();   // sesuaikan dengan tipe users.id

            // Satu proyek tidak boleh punya dua termin dengan nomor sama
            $table->unique(['project_id', 'termin_no']);
        });
    }

    public function down(): void
    {
        Schema::table('lebihtersistem.build_termins', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'termin_no']);

            $table->dropColumn([
                'billing_period',
                'period_start',
                'period_end',
                'renewal_decision',
                'decided_at',
                'decided_by',
            ]);
        });
    }
};