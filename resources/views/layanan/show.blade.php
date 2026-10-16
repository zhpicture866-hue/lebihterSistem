{{-- resources/views/layanan/show.blade.php --}}
@extends('layouts.website')

@push('styles')
<style>
.svc-page{ background:#f8f6f2; padding:150px 0 80px; overflow-x:clip; }
.svc-wrap{ width:min(100% - 40px,1100px); margin:0 auto; }

.svc-crumb{ font-size:13px; color:#888; margin-bottom:18px; }
.svc-crumb a{ color:#888; text-decoration:none; }
.svc-crumb a:hover{ color:#b7965b; }

.svc-hero{
    position:relative; border-radius:16px; overflow:hidden; min-height:340px;
    display:flex; align-items:flex-end;
    background:linear-gradient(135deg,#1f1a12,#3d3423);
    box-shadow:0 8px 24px rgba(0,0,0,.08);
}
.svc-hero img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; display:block; }
.svc-hero::after{ content:""; position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.75), rgba(0,0,0,.1) 70%); }
.svc-hero-body{ position:relative; z-index:2; padding:40px; color:#fff; }
.svc-hero-icon{
    width:56px; height:56px; border-radius:14px; display:flex; align-items:center; justify-content:center;
    background:#fbee47; color:#000; font-size:28px; margin-bottom:16px;
}
.svc-hero h1{ margin:0; font-size:clamp(28px,4vw,42px); font-weight:700; line-height:1.2; color:#fff; }

.svc-grid{ display:grid; grid-template-columns:minmax(0,1fr) 320px; gap:28px; margin-top:28px; align-items:start; }
.svc-card{ background:#fff; border-radius:16px; padding:36px; box-shadow:0 8px 24px rgba(0,0,0,.06); }
.svc-card h2{ font-size:20px; font-weight:700; color:#111; margin:0 0 14px; }
.svc-desc{ font-size:16px; line-height:1.85; color:#444; overflow-wrap:anywhere; }
.svc-desc p{ margin-bottom:1.1rem; }
.svc-desc ul,.svc-desc ol{ margin-bottom:1.1rem; padding-left:1.3rem; }

.svc-side{ position:sticky; top:120px; display:flex; flex-direction:column; gap:20px; }
.svc-cta{ text-align:center; }
.svc-cta h3{ font-size:18px; font-weight:700; color:#111; margin:0 0 8px; }
.svc-cta p{ font-size:14px; color:#666; line-height:1.6; margin:0 0 18px; }
.svc-btn{
    display:flex; align-items:center; justify-content:center; gap:8px; height:46px; border-radius:12px;
    background:#fbee47; color:#000; font-weight:600; font-size:14px; text-decoration:none; transition:.25s;
}
.svc-btn:hover{ background:#332a1d; color:#fff; }
.svc-btn.wa{ background:#fbee47; }
.svc-btn.wa:hover{ background:#58595b; }

.svc-other{ list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:4px; }
.svc-other a{
    display:flex; align-items:center; justify-content:space-between; gap:8px; padding:10px 12px;
    border-radius:10px; color:#000; text-decoration:none; font-size:14px; font-weight:500;
}
.svc-other a:hover{ background:#58595b; color:#fff; }

.svc-proj{ margin-top:44px; scroll-margin-top:120px; }
.svc-proj-head{ margin-bottom:22px; }
.svc-proj-head h2{ font-size:24px; font-weight:700; color:#111; margin:0 0 6px; }
.svc-proj-head p{ font-size:14px; color:#666; margin:0; }
.svc-proj-grid{ display:grid; grid-template-columns:repeat(3, 1fr); gap:24px; }
.svc-proj-card{
    display:flex; flex-direction:column; gap:8px; background:#fff; border-radius:16px; padding:24px;
    box-shadow:0 8px 24px rgba(0,0,0,.08); transition:transform .25s, box-shadow .25s;
}
.svc-proj-card:hover{ transform:translateY(-4px); box-shadow:0 16px 36px rgba(0,0,0,.12); }
.svc-proj-date{ font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:#b7965b; }
.svc-proj-title{ margin:0; font-size:17px; font-weight:700; line-height:1.35; color:#111; }
.svc-proj-text{
    margin:0; font-size:14px; line-height:1.6; color:#666;
    display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;
}
.svc-proj-loc{ display:flex; align-items:center; gap:6px; margin-top:auto; padding-top:12px; border-top:1px solid #f0f0f0; font-size:13px; color:#888; }
.svc-proj-loc i{ color:#b7965b; }
.svc-proj-empty{ background:#fff; border-radius:16px; padding:40px 20px; text-align:center; color:#888; box-shadow:0 8px 24px rgba(0,0,0,.06); }
.svc-pager{ display:flex; justify-content:center; gap:10px; margin-top:28px; }
.svc-pager a{
    display:inline-flex; align-items:center; gap:6px; height:40px; padding:0 18px; border-radius:10px;
    background:#fff; color:#333; font-size:14px; font-weight:600; text-decoration:none; box-shadow:0 2px 8px rgba(0,0,0,.06);
}
.svc-pager a:hover{ background:#b7965b; color:#fff; }
@media (max-width:1100px){ .svc-proj-grid{ grid-template-columns:repeat(2, 1fr); } }
@media (max-width:760px){ .svc-proj-grid{ grid-template-columns:1fr; gap:18px; } .svc-proj-card{ padding:20px; } }
@media (max-width:991px){
    .svc-grid{ grid-template-columns:1fr; }
    .svc-side{ position:static; }
}
@media (max-width:760px){
    .svc-page{ padding-top:130px; }
    .svc-hero{ min-height:260px; }
    .svc-hero-body{ padding:24px; }
    .svc-card{ padding:24px 20px; }
}
</style>
@endpush

@section('content')
@php
    $img = data_get($layanan, 'image');
    $hasDesc = filled(trim(strip_tags((string) $layanan->description)));
@endphp
<main class="svc-page">
    <div class="svc-wrap">
        <nav class="svc-crumb">
            <a href="{{ url('/') }}">Home</a> &nbsp;/&nbsp; <a href="{{ url('/') }}#layanan">Layanan</a> &nbsp;/&nbsp; {{ $layanan->name }}
        </nav>

        <section class="svc-hero">
            @if ($img)
                <img src="{{ asset('storage/' . $img) }}" alt="{{ $layanan->name }}" fetchpriority="high">
            @endif
            <div class="svc-hero-body">
                <div class="svc-hero-icon"><i class="ti ti-camera"></i></div>
                <h1>{{ $layanan->name }}</h1>
            </div>
        </section>

        <div class="svc-grid">
            <div class="svc-card">
                <h2>Tentang Layanan</h2>
                <div class="svc-desc">
                    @if ($hasDesc)
                        {{-- deskripsi dari admin: tampil sebagai teks, baris baru dipertahankan --}}
                        {!! nl2br(e(strip_tags((string) $layanan->description))) !!}
                    @else
                        <p>Informasi lengkap layanan {{ $layanan->name }} akan segera tersedia. Hubungi kami untuk konsultasi dan penawaran.</p>
                    @endif
                </div>
            </div>

            <aside class="svc-side">
                <div class="svc-card svc-cta">
                    <h3>Tertarik dengan layanan ini?</h3>
                    <p>Konsultasikan kebutuhanmu langsung dengan tim ZH Picture.</p>
                    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="svc-btn wa">
                        <i class="ti ti-brand-whatsapp"></i> Hubungi via WhatsApp
                    </a>
                </div>

                @if ($lainnya->isNotEmpty())
                    <div class="svc-card">
                        <h3 style="font-size:16px;font-weight:700;margin:0 0 10px">Layanan Lainnya</h3>
                        <ul class="svc-other">
                            @foreach ($lainnya as $l)
                                <li><a href="{{ route('layanan.show', $l->code) }}">{{ $l->name }} <i class="ti ti-arrow-right"></i></a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>

        <section class="svc-proj" id="proyek">
            <div class="svc-proj-head">
                <h2>Proyek {{ $layanan->name }}</h2>
                <p>Beberapa proyek yang pernah kami kerjakan untuk layanan ini.</p>
            </div>

            @if ($projects->isEmpty() && $projects->currentPage() === 1)
                <div class="svc-proj-empty">Belum ada proyek untuk layanan ini.</div>
            @else
                <div class="svc-proj-grid">
                    @foreach ($projects as $p)
                        <article class="svc-proj-card">
                            @if ($p->start_date)
                                <span class="svc-proj-date">{{ $p->start_date->translatedFormat('F Y') }}</span>
                            @endif
                            <h3 class="svc-proj-title">{{ $p->project_name }}</h3>
                            @php $ptxt = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $p->description))), 140); @endphp
                            @if ($ptxt)<p class="svc-proj-text">{{ $ptxt }}</p>@endif
                            @if ($p->project_location)
                                <span class="svc-proj-loc"><i class="ti ti-map-pin"></i> {{ $p->project_location }}</span>
                            @endif
                        </article>
                    @endforeach
                </div>

                @if ($projects->hasPages())
                    <div class="svc-pager">
                        @if ($projects->previousPageUrl())
                            <a href="{{ $projects->previousPageUrl() }}#proyek"><i class="ti ti-arrow-left"></i> Sebelumnya</a>
                        @endif
                        @if ($projects->nextPageUrl())
                            <a href="{{ $projects->nextPageUrl() }}#proyek">Berikutnya <i class="ti ti-arrow-right"></i></a>
                        @endif
                    </div>
                @endif
            @endif
        </section>
    </div>
</main>
@endsection