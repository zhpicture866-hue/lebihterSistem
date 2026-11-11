<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Isi created_at yang masih kosong dengan tanggal akun user dibuat (perkiraan).
        // Data yang sudah terisi TIDAK ditimpa. (PostgreSQL)
        DB::statement('
            UPDATE customers
            SET created_at = users.created_at
            FROM users
            WHERE users.id = customers.user_id
              AND customers.created_at IS NULL
              AND users.created_at IS NOT NULL
        ');

        // Sisanya diisi waktu sekarang
        DB::table('customers')->whereNull('created_at')->update(['created_at' => now()]);

        // updated_at yang kosong disamakan dengan created_at
        DB::statement('UPDATE customers SET updated_at = created_at WHERE updated_at IS NULL');
    }

    public function down(): void
    {
        // Backfill data, tidak ada yang perlu dikembalikan.
    }
};