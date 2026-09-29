<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Project extends Model
{
    use HasUuids;

    protected $table = 'lebihtersistem.projects';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // kalau tabel tidak punya created_at / updated_at

    protected $fillable = [
        'project_code',
        'parent_project_id',
        'project_name',
        'project_type',
        'project_location',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'postal_code_id',
        'customer_id',
        'employee_id',
        'affiliator_id',
        'end_date',
        'start_date',
        'project_status',
        'base_url',
        'description'
    ];

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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function affiliator()
    {
        return $this->belongsTo(Affiliator::class, 'affiliator_id');
    }

    public function levels()
    {
        return $this->hasMany(ProjectLevel::class);
    }


    public function offer()
    {
        return $this->hasOne(Offer::class);
    }

        public function offerBuild()
    {
        return $this->hasOne(OfferBuild::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

        public function invoicebuilds()
    {
        return $this->hasMany(InvoiceBuild::class);
    }

    public function tasks()
{
    return $this->hasMany(ProjectTask::class);
}

    public function rab()
{
    return $this->hasOne(OfferProcess::class);
}

    public function finalDocument()
{
    return $this->hasOne(FinalProject::class);
}

    public function finalBuild()
{
    return $this->hasOne(FinalBuild::class);
}

public function buildItems()
{
    return $this->hasMany(BuildProcessItem::class);
}

public function dailyReports()
{
    return $this->hasMany(BuildDailyReport::class,'project_id')
                ->orderBy('tanggal','desc');
}

public function weeklyPlans()
{
    return $this->hasMany(BuildPlans::class);
}

public function weeklyReports()
{
    return $this->hasMany(WeeklyReport::class);
}

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class, 'project_type');
    }
public function buildTermins()
{
    return $this->hasMany(BuildTermin::class)
        ->orderBy('termin_no');
}

public function progressSnapshots()
{
    return $this->hasMany(BuildProgressSnapshot::class);
}

    public function getCurrentLevelAttribute()
    {
        return $this->levels()
            ->where('is_completed', false)
            ->orderBy('level_order')
            ->first();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {

            if (!$project->id) {
                $project->id = (string) Str::uuid();
            }

            if (!$project->project_code) {
                $project->project_code = 'PRJ-' . strtoupper(Str::random(6));
            }
        });
    }

    public function getCustomerNameAttribute()
{
    return $this->customer?->display_name ?? '-';
}

public function getEmployeeNameAttribute()
{
    return $this->employee?->display_name ?? '-';
}

public function latestSurveyInvoice()
{
    return $this->invoices()
        ->where('invoice_type', Invoice::TYPE_SURVEY)
        ->where('status', '!=', 'obsolete')
        ->latest() 
        ->first();
}

public function generateLevels()
{
    $template = ProjectTypeLevel::where('project_type_id', $this->project_type) // <- kolom FK di project_type_levels tetap 'project_type_id', tapi VALUE-nya diambil dari $this->project_type (kolom asli di tabel projects)
        ->orderBy('level_order')
        ->get(['level_order', 'level_name']);
 
    if ($template->isEmpty()) {
        throw new \Exception(
            'Step untuk jenis proyek ini belum diatur di pengaturan Jenis Proyek.'
        );
    }
 
    $this->levels()->createMany(
        $template->map(fn ($lvl) => [
            'level_order' => $lvl->level_order,
            'level_name'  => $lvl->level_name,
        ])->toArray()
    );
}

protected $casts = [
    'start_date' => 'date',
    'end_date'   => 'date',
];

}
