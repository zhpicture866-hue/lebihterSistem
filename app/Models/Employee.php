<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory, HasUuid, Notifiable;

    protected $table = 'lebihtersistem.employees';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $casts = [
        'position' => 'array',
    ];


    protected $fillable = [
    'user_id',
    'nik',
    'marital_status',
    'position',
    'employment_status',
    'start_date',
    'basic_salary',
    'allowance',
    'deduction',
    'bonus',
    'thr',
    'contract_letter_file',
    'training_certificate',
    'signature',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function province()
{
    return $this->belongsTo(Province::class);
}

       public function city()
{
    return $this->belongsTo(City::class);
}

       public function district()
{
    return $this->belongsTo(District::class);
}

       public function subDistrict()
{
    return $this->belongsTo(SubDistrict::class);
}

    public function postalCode()
{
    return $this->belongsTo(PostalCode::class, 'postal_code_id');
}

    public function religion()
{
    return $this->belongsTo(Religion::class, 'religion_id');
}

public function bank()
{
    return $this->belongsTo(Bank::class);
}


    public function projects()
{
    return $this->hasMany(Project::class);
}

    public function levels()
{
    return $this->hasMany(ProjectLevel::class);
}

    public function plannings()
{
    return $this->belongsToMany(Planning::class);
}

    public function surveys()
{
    return $this->belongsToMany(Survey::class);
}

     public function workers()
{
    return $this->hasMany(EmployeeWorkExperience::class);
}

    public function projectLevels()
    {
        return $this->belongsToMany(ProjectLevel::class, 'project_level_employee');
    }

        public function licenses()
    {
        return $this->belongsToMany(License::class, 'employee_license', 'employee_id', 'license_id');
    }

    public function attendances()
{
    return $this->hasMany(Attendance::class);
}

     public function getMarriedDateFormattedAttribute()
    {
        return $this->married_date ? Carbon::parse($this->married_date)->format('d/m/Y') : '-';
    }

    public function getDisplayNameAttribute()
    {
        return $this->user?->fullname;
    }
    
    public function getFullnameAttribute($value)
{
    return Str::title($value);
}

public static function generateNik()
{
    $lastEmployee = self::orderBy('id', 'desc')->first();

    // Ambil angka terakhir dari NIK sebelumnya (misal E-0007 → 7)
    $lastNumber = 0;
    if ($lastEmployee && preg_match('/E-(\d+)/', $lastEmployee->nik, $matches)) {
        $lastNumber = (int) $matches[1];
    }

    // Tambah 1 dan format jadi E-0001
    $newNumber = $lastNumber + 1;
    return 'E-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}


}
