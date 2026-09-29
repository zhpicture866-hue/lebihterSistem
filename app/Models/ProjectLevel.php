<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class ProjectLevel  extends Model
{
    use HasFactory;

    protected $table = 'lebihtersistem.project_levels';
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'level_order',
        'level_name',
        'is_completed',
        'is_started',
    ];

public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'project_level_employee');
    }

}
