<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class InvoiceBuild extends Model
{
    use HasUuid;

    const TYPE_WEDDING = 'wedding';
    const TYPE_EVENT = 'event';
    const STATUS_DRAFT    = 'draft';
    const STATUS_WAITING  = 'waiting_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID     = 'paid';
    protected $table = 'zhpicture.invoice_builds';
    protected $casts = [
        'invoice_date' => 'date',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'bukti_pembayaran_uploaded_at' => 'datetime',
    ];

    protected $fillable = [
        'project_id',
        'invoice_number',
        'invoice_date',
        'invoice_type',
        'amount',
        'status',
        'approved_at',
        'approved_by',
        'approval_token',
        'rejected_at',
        'rejected_by',
        'reject_note',
        'downloaded_at',
        'approve_by_name',
        'approved_ip',
        'termin',
        'progress_start',
        'progress_end',
        'payment_percentage',
        'paid_at',
        'note',
        'nominal',
        'bukti_pembayaran',
        'bukti_pembayaran_uploaded_at',
        'kwitansi_number',
        'kwitansi_path',
        'kwitansi_generated_at'
    ];

        public function project()
    {
        return $this->belongsTo(Project::class);
    }

        public function scopeOrderByTermin($query)
    {
        return $query->orderBy('termin');
    }
}
