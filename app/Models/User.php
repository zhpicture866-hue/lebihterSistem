<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
use App\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuid;

    protected $guard_name = 'web';
    protected $table = 'global.users';

    public $incrementing = false;
    protected $keyType = 'string';

    public function uploadedProjectTaskFiles()
    {
        return $this->hasMany(ProjectTaskFile::class, 'uploaded_by');
    }

public function employee()
{
    return $this->hasOne(Employee::class);
}

    public function customer()
{
    return $this->hasOne(Customer::class);
}

    public function investor()
{
    return $this->hasOne(Investor::class);
}

    public function supplier()
{
    return $this->hasOne(Supplier::class);
}

    public function contractor()
{
    return $this->hasOne(Contractor::class);
}

    public function worker()
{
    return $this->hasOne(Worker::class);
}

    public function architect()
{
    return $this->hasOne(Architect::class);
}

    public function affiliator()
{
    return $this->hasOne(Affiliator::class);
}

public function activeRole()
{
    return $this->belongsTo(Role::class, 'active_role');
}

    public function notifications()
{
    return $this->morphMany(
        DatabaseNotification::class,
        'notifiable'
    )->latest();
}
    protected $fillable = [
        'fullname',
        'nickname',
        'email',
        'password',
        'gender',
        'birth_place',
        'birth_date',
        'religion_id',
        'address',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'postal_code_id',
        'phone',
        'photo',
        'identity_number',
        'npwp',
        'bank_id',
        'account_number',
        'account_holder',
        'active_role',
        'identity_photo',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'identity_number',
        'remember_token',
        'account_number',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function religion() {
        return $this->belongsTo(Religion::class);
    }

    public function province() {
        return $this->belongsTo(Province::class);
    }

    public function city() {
        return $this->belongsTo(City::class);
    }

    public function district() {
        return $this->belongsTo(District::class);
    }

    public function subDistrict() {
        return $this->belongsTo(SubDistrict::class);
    }

    public function postalCode() {
        return $this->belongsTo(PostalCode::class);
    }

    public function bank()
{
    return $this->belongsTo(Bank::class);
}

    public function getPhotoUrlAttribute()
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : null;
    }

    public function getBirthDateFormattedAttribute()
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->format('d/m/Y') : '-';
    }

    public function getReadableGenderAttribute()
    {
    return [
        1 => 'Laki-Laki',
        2 => 'Perempuan',
    ][$this->gender] ?? 'Tidak diketahui';
    }

public function setActiveRoleAttribute($value)
{
    // Larangan nilai angka
    if (is_numeric($value)) {
        $this->attributes['active_role'] = null;
        return;
    }

    $this->attributes['active_role'] = $value;
}
public function getShortNameAttribute()
{
    return collect(explode(' ', $this->fullname))
        ->take(2)
        ->implode(' ');
}
public function isInternal(): bool
{
    return $this->roles()
        ->where('role_group', 'Internal')
        ->exists();
}

public function isExternal(): bool
{
    return $this->roles()
        ->where('role_group', 'External')
        ->exists();
}
}