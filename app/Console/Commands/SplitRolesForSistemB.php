<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SplitRolesForSistemB extends Command
{
    protected $signature = 'permission:split-sistem-b {--dry-run : Tampilkan jumlah data saja tanpa menyimpan}';

    protected $description = 'Salin roles & permissions guard web ke guard sistem_b';

    public function handle()
    {
        $guard = 'sistem_b';
        $userModel = \App\Models\User::class;

        if (! $this->option('dry-run') && ! $this->confirm('Sudah backup database? Lanjutkan?')) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($guard, $userModel) {
            $roleMap = [];
            $permMap = [];

            // 1. Permissions
            foreach (DB::table('permissions')->where('guard_name', 'web')->get() as $p) {
                $existingId = DB::table('permissions')
                    ->where(['name' => $p->name, 'guard_name' => $guard])->value('id');

                if (! $existingId) {
                    $data = (array) $p;
                    $data['id'] = (string) Str::uuid();
                    $data['guard_name'] = $guard;
                    DB::table('permissions')->insert($data);
                    $existingId = $data['id'];
                }

                $permMap[$p->id] = $existingId;
            }

            // 2. Roles
            foreach (DB::table('roles')->where('guard_name', 'web')->get() as $r) {
                $existingId = DB::table('roles')
                    ->where(['name' => $r->name, 'guard_name' => $guard])->value('id');

                if (! $existingId) {
                    $data = (array) $r;
                    $data['id'] = (string) Str::uuid();
                    $data['guard_name'] = $guard;
                    DB::table('roles')->insert($data);
                    $existingId = $data['id'];
                }

                $roleMap[$r->id] = $existingId;
            }

            // 3. Role -> permission
            foreach (DB::table('role_has_permissions')->get() as $rp) {
                if (isset($roleMap[$rp->role_id], $permMap[$rp->permission_id])) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'role_id' => $roleMap[$rp->role_id],
                        'permission_id' => $permMap[$rp->permission_id],
                    ]);
                }
            }

            // 4. User -> role
            $userIds = (new \App\Models\User)->newQuery()->pluck('id');

            foreach (DB::table('model_has_roles')
                ->where('model_type', $userModel)
                ->whereIn('model_id', $userIds)->get() as $mr) {
                if (isset($roleMap[$mr->role_id])) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $roleMap[$mr->role_id],
                        'model_type' => $mr->model_type,
                        'model_id' => $mr->model_id,
                    ]);
                }
            }

            // 5. User -> permission langsung
            foreach (DB::table('model_has_permissions')
                ->where('model_type', $userModel)
                ->whereIn('model_id', $userIds)->get() as $mp) {
                if (isset($permMap[$mp->permission_id])) {
                    DB::table('model_has_permissions')->insertOrIgnore([
                        'permission_id' => $permMap[$mp->permission_id],
                        'model_type' => $mp->model_type,
                        'model_id' => $mp->model_id,
                    ]);
                }
            }

            $this->info('Permissions disalin: '.count($permMap));
            $this->info('Roles disalin: '.count($roleMap));

            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->warn('Dry run: semua perubahan dibatalkan.');
            }
        });

        $this->call('permission:cache-reset');

        return self::SUCCESS;
    }
}