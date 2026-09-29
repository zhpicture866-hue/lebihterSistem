<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
