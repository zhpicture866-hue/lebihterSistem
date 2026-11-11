<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Customer extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function postalCode()
{
    return $this->belongsTo(PostalCode::class, 'postal_code_id');
}

    public function projects()
{
    return $this->hasMany(Project::class);
}

public function scopeLoyalty($query, $level)
    {
        return $query->where('loyalty_level', $level);
    }

    public function getLoyaltyLevelFormattedAttribute()
    {
        return ucfirst($this->loyalty_level);
    }

    /**
     * 🔹 Accessor: Status aktif/nonaktif readable
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getDisplayNameAttribute()
    {
        return $this->user?->fullname;
    }

public static function generateNic()
{
    $lastNumber = self::where('nic', 'like', 'C-%')
        ->selectRaw("
            MAX(
                CAST(
                    REGEXP_REPLACE(nic, '[^0-9]', '', 'g')
                    AS INTEGER
                )
            ) as max_nic
        ")
        ->value('max_nic');

    $newNumber = ($lastNumber ?? 0) + 1;

    return 'C-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}


    public static function getDefaultAttributes($user)
    {
        return [
            'nic' => self::generateNic(),
            'province_id' => 15,
            'city_id' => 234,
            'district_id' => 3372,
            'sub_district_id' => 42178,
            'postal_code_id' => 42178,
            'loyalty_level' => 1,

        ];
    }

    public function getReadableLoyaltyLevelAttribute()
    {
    return [
        1 => 'Lead',
        2 => 'Prospek',
        3 => 'New Customer',
        4 => 'Silver',
        5 => 'Gold',
        6 => 'Platinum',
    ][$this->loyalty_level] ?? 'Tidak diketahui';
    }

    public function getReadableCategoryAttribute()
    {
    return [
        1 => 'Individu',
        2 => 'Perusahaan',
        3 => 'Instansi',
        4 => 'Lainnya',
    ][$this->category] ?? 'Tidak diketahui';
    }
//     public function getLoyaltyLevelAttribute()
// {
//     $totalProject = $this->projects()->count();

//     return match (true) {
//         $totalProject === 0 => 'Lead',
//         $totalProject <= 2 => 'New Customer',
//         $totalProject <= 5 => 'Silver',
//         $totalProject <= 10 => 'Gold',
//         $totalProject <= 20 => 'Platinum',
//         default => 'Lead',
//     };
// }

public const CHANNELS = [
    'instagram' => 'Instagram',
    'whatsapp'  => 'WhatsApp',
    'facebook'  => 'Facebook',
    'tiktok'    => 'TikTok',
    'google'    => 'Google / Website',
    'referral'  => 'Referensi / Teman',
    'event'     => 'Event / Pameran',
    'other'     => 'Lainnya',
];

public function getReadableChannelAttribute()
{
    return self::CHANNELS[$this->channel] ?? '-';
}
    protected function displayNameWithTitle(): Attribute
{
    return Attribute::make(
        get: function () {
            $name = $this->display_name ?? '';

            $gender = optional($this->user)->gender;

            return match ($gender) {
                1 => 'Bapak ' . $name,
                2 => 'Ibu ' . $name,
                default => $name,
            };
        }
    );
}


    use HasFactory, HasUuids;

    protected $table = 'customers';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'nic',
        'category',
        'loyalty_level',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'postal_code_id',
        'notes',
        'is_active',
        'signature',
        'channel'
    ];

}
