<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectTypeSeeder extends Seeder
{
    public function run(): void
    {
        // PENTING: insert dengan id eksplisit supaya project_type=1/2/3 di data lama
        // tetap nyambung ke baris yang benar (Desain/RAB/Build), tidak perlu migrasi data project lama.
        $types = [
            ['id' => 1, 'name' => 'Jasa Konstruksi', 'code' => 'konstruksi'],
            ['id' => 2, 'name' => 'Klinik', 'code' => 'klinik'],
            ['id' => 3, 'name' => 'Toko', 'code' => 'toko'],
            ['id' => 4, 'name' => 'Event', 'code' => 'event'],
            ['id' => 5, 'name' => 'Company Profile', 'code' => 'company-profile'],
        ];

        foreach ($types as $type) {
            DB::table('lebihtersistem.project_types')->updateOrInsert(
                ['id' => $type['id']],
                [
                    'name'       => $type['name'],
                    'code'       => $type['code'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Step lama (Desain/RAB/Build) — copy dari match() lama di generateLevels(), sesuaikan isinya
        $legacyLevels = [
            1 => [ // Desain
                ['level_order' => 1, 'level_name' => 'Konsultasi'],
                ['level_order' => 2, 'level_name' => 'Rencana Survei'],
                ['level_order' => 3, 'level_name' => 'Survei'],
                ['level_order' => 4, 'level_name' => 'Penawaran Jasa Desain'],
                ['level_order' => 5, 'level_name' => 'Kontrak Desain'],
                ['level_order' => 6, 'level_name' => 'Invoice Desain DP'],
                ['level_order' => 7, 'level_name' => 'Proses Pengerjaan'],
                ['level_order' => 8, 'level_name' => 'Invoice Pelunasan Desain'],
                ['level_order' => 9, 'level_name' => 'Cetak & Softcopy'],
            ],
            2 => [ // RAB
                ['level_order' => 1, 'level_name' => 'Konsultasi'],
                ['level_order' => 2, 'level_name' => 'Rencana Survei'],
                ['level_order' => 3, 'level_name' => 'Survei'],
                ['level_order' => 4, 'level_name' => 'Penawaran Pembuatan RAB'],
                ['level_order' => 5, 'level_name' => 'Invoice RAB'],
                ['level_order' => 6, 'level_name' => 'Proses Pengerjaan RAB'],
            ],
            3 => [ // Build
                ['level_order' => 1, 'level_name' => 'Konsultasi'],
                ['level_order' => 2, 'level_name' => 'Rencana Survei'],
                ['level_order' => 3, 'level_name' => 'Survei'],
                ['level_order' => 4, 'level_name' => 'Penawaran Jasa Build'],
                ['level_order' => 5, 'level_name' => 'Kontrak Kerja'],
                ['level_order' => 6, 'level_name' => 'Invoice Tahap 1'],
                ['level_order' => 7, 'level_name' => 'Pelaksanaan'],
                ['level_order' => 8, 'level_name' => 'Serah Terima'],
            ],
        ];

        // Step baru (sama semua untuk sekarang), sesuai kesepakatan kamu
        $newLevels = [
            ['level_order' => 1, 'level_name' => 'Penawaran Harga'],
            ['level_order' => 2, 'level_name' => 'Setting Termin'],
        ];

        $allLevels = $legacyLevels + [
            4 => $newLevels, // Wedding Syariah
            5 => $newLevels, // Event
        ];

        foreach ($allLevels as $projectTypeId => $levels) {
            foreach ($levels as $level) {
                DB::table('lebihtersistem.project_type_levels')->updateOrInsert(
                    [
                        'project_type_id' => $projectTypeId,
                        'level_order'     => $level['level_order'],
                    ],
                    [
                        'level_name' => $level['level_name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}