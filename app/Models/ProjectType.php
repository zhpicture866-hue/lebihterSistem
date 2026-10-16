<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectType extends Model
{
    protected $table = 'lebihtersistem.project_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Template step (Penawaran Harga, Invoice, dst) yang di-CRUD lewat halaman
     * pengaturan jenis proyek. Dicopy jadi ProjectLevel tiap kali project baru dibuat.
     */
    public function levelTemplates()
    {
        return $this->hasMany(ProjectTypeLevel::class)->orderBy('level_order');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'project_type');
    }


// app/Models/ProjectType.php — tambahkan method ini di dalam class ProjectType.
// Ikon dipilih otomatis dari nama/kode layanan. Kalau suatu saat ada kolom `icon`, nilainya dipakai lebih dulu.

public function getIconAttribute(): string
{
    if (!empty($this->attributes['icon'])) {
        return $this->attributes['icon'];
    }

    $key = Str::lower(($this->name ?? '') . ' ' . ($this->code ?? ''));

    return match (true) {
        Str::contains($key, ['hosting']) && Str::contains($key, ['domain']) => 'ti-cloud-computing', // Hosting & Domain
        Str::contains($key, ['domain'])                                      => 'ti-world-www',      // Domain
        Str::contains($key, ['hosting'])                                     => 'ti-server',         // Hosting
        Str::contains($key, ['support', 'helpdesk', 'maintenance'])          => 'ti-headset',        // IT Support
        Str::contains($key, ['website', 'web', 'aplikasi', 'app', 'sistem']) => 'ti-code',           // Pembuatan web/aplikasi
        Str::contains($key, ['email', 'mail'])                               => 'ti-mail',
        Str::contains($key, ['ssl', 'security', 'keamanan'])                 => 'ti-shield-lock',
        default                                                              => 'ti-box',
    };
}

// ── Pemakaian di Blade ──
// welcome.blade.php  : <div class="service-card-icon"><i class="ti {{ $t->icon }}"></i></div>
//                      (ganti  <i class="ti ti-camera"></i>)
// layanan/show       : <div class="svc-hero-icon"><i class="ti {{ $layanan->icon }}"></i></div>
//
// Pastikan query homepage / controller tidak membatasi kolom tanpa `name` dan `code`
// (accessor membutuhkan keduanya), mis. ->get(['id','name','code','description']).
}
