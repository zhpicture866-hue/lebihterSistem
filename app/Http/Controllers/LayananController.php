<?php

namespace App\Http\Controllers;

use App\Models\ProjectType;
use Illuminate\Support\Str;

class LayananController extends Controller
{
    /**
     * Status proyek yang boleh tampil publik. null = tampilkan semua.
     * Contoh: ['selesai', 'completed'] — sesuaikan dengan nilai kolom project_status.
     */
    private const PUBLIC_STATUSES = null;

    public function show(string $code)
    {
        $layanan = ProjectType::where('is_active', true)
            ->where('code', $code)
            ->firstOrFail();

        $lainnya = ProjectType::where('is_active', true)
            ->where('id', '!=', $layanan->id)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'description']);

        // Proyek dengan jenis ini. Kolom dibatasi: data klien (customer_id dll.) tidak ikut dikirim ke view.
        $projects = $layanan->projects()
            ->whereNotNull('project_name')
            ->when(self::PUBLIC_STATUSES, fn ($q, $s) => $q->whereIn('project_status', $s))
            ->latest('start_date')
            ->select(['id', 'project_type', 'project_name', 'project_location', 'description', 'start_date', 'end_date', 'project_status'])
            ->simplePaginate(6)
            ->withQueryString();

        // SEO (artesaos/seotools) — dilewati otomatis bila paketnya tidak dipakai di ZH Picture
        if (class_exists(\Artesaos\SEOTools\Facades\SEOMeta::class)) {
            $desc = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $layanan->description))), 160);
            \Artesaos\SEOTools\Facades\SEOMeta::setTitle($layanan->name . ' - ZH Picture', false);
            \Artesaos\SEOTools\Facades\SEOMeta::setDescription($desc ?: 'Layanan ' . $layanan->name . ' dari ZH Picture.');
            \Artesaos\SEOTools\Facades\SEOMeta::setCanonical(url()->current());
        }

        // Nomor WhatsApp admin ZH Picture (format internasional tanpa +). Ganti sesuai nomor asli.
        $waNumber = config('services.zh.whatsapp');
        $waText   = urlencode('Halo ZH Picture, saya tertarik dengan layanan ' . $layanan->name . '.');
        $waUrl    = "https://wa.me/{$waNumber}?text={$waText}";

        return view('layanan.show', compact('layanan', 'lainnya', 'projects', 'waUrl'));
    }
}