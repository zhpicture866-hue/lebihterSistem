<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

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
    public const LEVEL_SILVER   = 2;
public const LEVEL_GOLD     = 4;
public const LEVEL_PLATINUM = 7;

public static function hasProjectSql(): string
{
    $projects = (new Project)->getTable();   // 'zhpicture.projects'

    return "EXISTS (SELECT 1 FROM {$projects} p WHERE p.customer_id = customers.id)";
}

public static function levelSql(): string
{
    $projects = (new Project)->getTable();
    $builds   = (new InvoiceBuild)->getTable();   // 'zhpicture.invoice_builds'

    $qualified = "(SELECT COUNT(*) FROM {$projects} p
        WHERE p.customer_id = customers.id
          AND EXISTS (SELECT 1 FROM {$builds} b
                      WHERE b.project_id = p.id
                        AND b.approved_at IS NOT NULL))";

    return "CASE
        WHEN $qualified >= " . self::LEVEL_PLATINUM . " THEN 6
        WHEN $qualified >= " . self::LEVEL_GOLD     . " THEN 5
        WHEN $qualified >= " . self::LEVEL_SILVER   . " THEN 4
        WHEN $qualified >= 1 THEN 3
        WHEN " . self::hasProjectSql() . " THEN 2
        ELSE 1
    END";
}

public function getEffectiveLoyaltyLevelAttribute(): int
{
    if (isset($this->attributes['auto_level'])) {
        return (int) $this->attributes['auto_level'];
    }

    $ids = $this->projects()->pluck('id');

    $qualified = $ids->filter(fn ($id) =>
        InvoiceBuild::where('project_id', $id)
            ->whereNotNull('approved_at')
            ->exists()
    )->count();

    return match (true) {
        $qualified >= self::LEVEL_PLATINUM => 6,
        $qualified >= self::LEVEL_GOLD     => 5,
        $qualified >= self::LEVEL_SILVER   => 4,
        $qualified >= 1                    => 3,
        $ids->isNotEmpty()                 => 2,
        default                            => 1,
    };
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
    ][$this->effective_loyalty_level] ?? 'Tidak diketahui';
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
public static function lastTransactionSql(): string
{
    $projects = (new Project)->getTable();        // 'zhpicture.projects'
    $builds   = (new InvoiceBuild)->getTable();   // 'zhpicture.invoice_builds'

    return "(SELECT MAX(b.approved_at)
             FROM {$builds} b
             JOIN {$projects} p ON p.id = b.project_id
             WHERE p.customer_id = customers.id
               AND b.approved_at IS NOT NULL)";
}

public static function lastContactSql(): string
{
    // TODO: kalau CRM/follow-up sudah ada, taruh di depan COALESCE
    return "COALESCE(" . self::lastTransactionSql() . ", customers.created_at)";
}

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

    protected $table = 'lebihtersistem.customers';
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