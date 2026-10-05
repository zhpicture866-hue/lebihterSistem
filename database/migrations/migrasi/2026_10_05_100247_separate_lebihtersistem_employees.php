<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {

            /*
             * ==========================================================
             * 1. Pastikan schema lebihtersistem tersedia
             * ==========================================================
             */
            DB::statement(
                'CREATE SCHEMA IF NOT EXISTS lebihtersistem'
            );


            /*
             * ==========================================================
             * 2. Buat tabel lebihtersistem.employees
             * ==========================================================
             */
            if (! Schema::hasTable('lebihtersistem.employees')) {
                Schema::create('lebihtersistem.employees', function (Blueprint $table) {
                    $table->uuid('id')->primary();

                    $table->uuid('user_id');

                    $table->string('nik');

                    $table->smallInteger('marital_status')->nullable();

                    $table->string('position')->nullable();

                    $table->string('employment_status')->nullable();

                    $table->string('start_date', 10)->nullable();

                    $table->decimal('basic_salary', 15, 2)->nullable();

                    $table->decimal('allowance', 15, 2)->nullable();

                    $table->decimal('deduction', 15, 2)->nullable();

                    $table->decimal('bonus', 15, 2)->nullable();

                    $table->decimal('thr', 15, 2)->nullable();

                    $table->string('contract_letter_file')->nullable();

                    $table->string('training_certificate')->nullable();

                    $table->string('photo')->nullable();

                    $table->foreign('user_id')
                        ->references('id')
                        ->on('global.users')
                        ->restrictOnDelete();
                });
            }


            /*
             * ==========================================================
             * 3. Pastikan semua employee yang sedang dipakai oleh
             *    projects / project_levels tersedia di
             *    lebihtersistem.employees
             *
             *    UUID TIDAK DIUBAH.
             * ==========================================================
             */
            DB::statement("
                INSERT INTO lebihtersistem.employees (
                    id,
                    user_id,
                    nik,
                    marital_status,
                    position,
                    employment_status,
                    start_date,
                    basic_salary,
                    allowance,
                    deduction,
                    bonus,
                    thr,
                    contract_letter_file,
                    training_certificate,
                    photo
                )
                SELECT DISTINCT
                    e.id,
                    e.user_id,
                    e.nik,
                    e.marital_status,
                    e.position,
                    e.employment_status,
                    e.start_date,
                    e.basic_salary,
                    e.allowance,
                    e.deduction,
                    e.bonus,
                    e.thr,
                    e.contract_letter_file,
                    e.training_certificate,
                    e.photo
                FROM zhpicture.employees e
                WHERE e.id IN (
                    SELECT employee_id
                    FROM lebihtersistem.projects
                    WHERE employee_id IS NOT NULL

                    UNION

                    SELECT employee_id
                    FROM lebihtersistem.project_levels
                    WHERE employee_id IS NOT NULL
                )
                ON CONFLICT (id) DO NOTHING
            ");


            /*
             * ==========================================================
             * 4. Pastikan tidak ada employee_id Lebih Tersistem
             *    yang belum berhasil dipindahkan.
             *
             *    Kalau ada, migration DIBATALKAN.
             * ==========================================================
             */
            $missingEmployees = DB::select("
                SELECT employee_id
                FROM (
                    SELECT employee_id
                    FROM lebihtersistem.projects
                    WHERE employee_id IS NOT NULL

                    UNION

                    SELECT employee_id
                    FROM lebihtersistem.project_levels
                    WHERE employee_id IS NOT NULL
                ) AS used_employees
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM lebihtersistem.employees e
                    WHERE e.id = used_employees.employee_id
                )
            ");

            if (count($missingEmployees) > 0) {
                $ids = collect($missingEmployees)
                    ->pluck('employee_id')
                    ->implode(', ');

                throw new RuntimeException(
                    "Migration dibatalkan. Employee berikut digunakan oleh "
                    . "lebihtersistem tetapi tidak ditemukan di "
                    . "lebihtersistem.employees: {$ids}"
                );
            }


            /*
             * ==========================================================
             * 5. Hapus FK lama:
             *
             *    lebihtersistem.projects.employee_id
             *        -> zhpicture.employees.id
             *
             *    lebihtersistem.project_levels.employee_id
             *        -> zhpicture.employees.id
             * ==========================================================
             */
            DB::statement("
                ALTER TABLE lebihtersistem.projects
                DROP CONSTRAINT IF EXISTS projects_employee_id_foreign
            ");

            DB::statement("
                ALTER TABLE lebihtersistem.project_levels
                DROP CONSTRAINT IF EXISTS project_levels_employee_id_foreign
            ");


            /*
             * ==========================================================
             * 6. Buat FK baru:
             *
             *    lebihtersistem.projects.employee_id
             *        -> lebihtersistem.employees.id
             * ==========================================================
             */
            DB::statement("
                ALTER TABLE lebihtersistem.projects
                ADD CONSTRAINT projects_employee_id_foreign
                FOREIGN KEY (employee_id)
                REFERENCES lebihtersistem.employees(id)
                ON DELETE RESTRICT
            ");


            /*
             * ==========================================================
             * 7. Buat FK baru:
             *
             *    lebihtersistem.project_levels.employee_id
             *        -> lebihtersistem.employees.id
             * ==========================================================
             */
            DB::statement("
                ALTER TABLE lebihtersistem.project_levels
                ADD CONSTRAINT project_levels_employee_id_foreign
                FOREIGN KEY (employee_id)
                REFERENCES lebihtersistem.employees(id)
                ON DELETE RESTRICT
            ");
        });
    }


    public function down(): void
    {
        DB::transaction(function () {

            /*
             * Pastikan semua employee_id yang digunakan
             * Lebih Tersistem masih ada di zhpicture.employees
             * sebelum FK dikembalikan.
             */
            $missingEmployees = DB::select("
                SELECT employee_id
                FROM (
                    SELECT employee_id
                    FROM lebihtersistem.projects
                    WHERE employee_id IS NOT NULL

                    UNION

                    SELECT employee_id
                    FROM lebihtersistem.project_levels
                    WHERE employee_id IS NOT NULL
                ) AS used_employees
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM zhpicture.employees e
                    WHERE e.id = used_employees.employee_id
                )
            ");

            if (count($missingEmployees) > 0) {
                $ids = collect($missingEmployees)
                    ->pluck('employee_id')
                    ->implode(', ');

                throw new RuntimeException(
                    "Rollback dibatalkan. Employee berikut tidak ada lagi "
                    . "di zhpicture.employees: {$ids}"
                );
            }


            /*
             * Hapus FK baru.
             */
            DB::statement("
                ALTER TABLE lebihtersistem.projects
                DROP CONSTRAINT IF EXISTS projects_employee_id_foreign
            ");

            DB::statement("
                ALTER TABLE lebihtersistem.project_levels
                DROP CONSTRAINT IF EXISTS project_levels_employee_id_foreign
            ");


            /*
             * Kembalikan FK ke zhpicture.employees.
             */
            DB::statement("
                ALTER TABLE lebihtersistem.projects
                ADD CONSTRAINT projects_employee_id_foreign
                FOREIGN KEY (employee_id)
                REFERENCES zhpicture.employees(id)
                ON DELETE RESTRICT
            ");

            DB::statement("
                ALTER TABLE lebihtersistem.project_levels
                ADD CONSTRAINT project_levels_employee_id_foreign
                FOREIGN KEY (employee_id)
                REFERENCES zhpicture.employees(id)
                ON DELETE RESTRICT
            ");

            /*
             * Jangan otomatis DROP lebihtersistem.employees di rollback.
             *
             * Alasannya: setelah migration ini dijalankan,
             * Lebih Tersistem mungkin sudah menambahkan employee baru.
             *
             * Menghapus tabel tersebut bisa menyebabkan kehilangan data.
             */
        });
    }
};