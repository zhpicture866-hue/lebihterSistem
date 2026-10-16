@extends('layouts.website')
@section('content')
<style>
.hero{
    position: relative;
    margin-top: 0;
    height: 100vh;
    overflow: hidden;
}
.hero-caption{
    position:absolute;
    left:12%;
    bottom:22%;
    z-index:3;
    color:#fff;
    text-align:left;
    display:block;
    padding:0;
}

.hero-caption h3{
    margin:0;
    font-family: "Montserrat", serif;
    font-size:38px;
    font-weight:400;
    line-height:1.05;
    letter-spacing:-0.5px;
    color:#fff;
    max-width:720px;
}
.carousel,
.carousel-inner,
.carousel-item{
    height:100vh;
}

.carousel-item img{
    width:100%;
    height:100%;
    object-fit:cover;
    object-position:center;
}
.carousel-control-prev,
.carousel-control-next{

    width:90px;

    z-index:4;

}
.carousel-indicators{

    bottom:40px;

    z-index:4;

}

.carousel-indicators button{

    width:120px !important;

    height:4px !important;

    border-radius:20px;

    margin:0 8px !important;
}
.website-header{
    position: fixed;
    top: 20px;
    left: 0;
    width: 100%;
    z-index:9999;
}
.event-section{
    padding:100px 5% 80px;
    max-width:1500px;
    margin:auto;
}

.event-section-header{
    text-align:center;
    margin-bottom:48px;
}

.event-section-header h2{
    font-size:32px;
    font-weight:700;
    color:#111;
    margin-bottom:10px;
}

.event-section-header p{
    color:#666;
    font-size:15px;
}

.event-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:28px;
}

.event-card{
    display:flex;
    flex-direction:column;
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    text-decoration:none;
    color:inherit;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    transition:transform .25s, box-shadow .25s;
}

.event-card:hover{
    transform:translateY(-6px);
    box-shadow:0 16px 36px rgba(0,0,0,.14);
}

.event-card-thumb{
    position:relative;
    height:180px;
    background:#f2f2f2;
}

.event-card-thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.event-card-thumb-placeholder{
    width:100%;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    color:#bbb;
}

.event-card-badge{
    position:absolute;
    top:12px;
    left:12px;
    padding:5px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
    color:#fff;
    background:#b7965b;
}

.event-card-badge.status-coming-soon{ background:#8a8a8a; }
.event-card-badge.status-pendaftaran{ background:#2f9e44; }
.event-card-badge.status-sold-out{ background:#c92a2a; }
.event-card-badge.status-sedang-berlangsung{ background:#1971c2; }
.event-card-badge.status-selesai{ background:#868e96; }

.event-card-body{
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:8px;
}

.event-card-category{
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#b7965b;
}

.event-card-title{
    font-size:17px;
    font-weight:700;
    color:#111;
    line-height:1.35;
}

.event-card-meta{
    display:flex;
    flex-direction:column;
    gap:4px;
    font-size:13px;
    color:#666;
    margin:4px 0 10px;
}

.event-card-meta span{
    display:flex;
    align-items:center;
    gap:6px;
}

.event-card-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding-top:12px;
    border-top:1px solid #f0f0f0;
}

.event-card-price{
    font-weight:700;
    color:#111;
    font-size:14px;
}

.event-card-cta{
    display:flex;
    align-items:center;
    gap:4px;
    font-size:13px;
    font-weight:600;
    color:#b7965b;
}

.event-empty{
    grid-column:1 / -1;
    text-align:center;
    color:#888;
    padding:40px 0;
}

.join-section{
    padding:0 5% 100px;
    max-width:1500px;
    margin:auto;
}

.join-card{
    background:linear-gradient(135deg, #DCCBA8, #C9AF7C);
    border-radius:24px;
    padding:70px 40px;
    text-align:center;
    color:#2b2318;
}

.join-card h2{
    font-size:30px;
    font-weight:700;
    margin-bottom:14px;
    color:#1f1a12;
}

.join-card p{
    font-size:15px;
    color:#3d3423;
    max-width:520px;
    margin:0 auto 28px;
    line-height:1.6;
}

.btn-join{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:#1f1a12;
    color:#fff;
    font-weight:600;
    font-size:14px;
    padding:14px 32px;
    border-radius:12px;
    text-decoration:none;
    transition:.25s;
}

.btn-join:hover{
    background:#332a1d;
    color:#fff;
}

@media (min-width: 992px) and (max-width: 1200px) {
    .hero-content {
        gap: 40px;
    }
    .brand-logo {
        width: 130px;
    }
    .btn-hero {
        min-width: 180px;
    }
    .event-grid{
        grid-template-columns:repeat(2, 1fr);
    }
}

@media (max-width:768px){
    .hero {
        background:
            linear-gradient(
                to bottom,
                rgba(0,0,0,0.65),
                rgba(0,0,0,0.35)
            ),
            url('{{ asset("images/lebihtersistem.jpeg") }}');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: 85% top;
        margin-top:0;
        height:100vh;
        position:relative;
    }
    .hero-caption{
        position: absolute;
        z-index: 10;
        color: #fff;
    }
    .hero-caption h3{
        font-size:28px;
        line-height:1.2;
    }
    .brand-logo {
        width: 120px;
        margin-top: -25px;
    }
    .button-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        gap: 16px;
    }
    .btn-hero {
        width: 100%;
        max-width: 270px;
        min-width: unset;
        padding: 13px 20px;
        font-size: 15px;
        border-radius: 12px;
    }
    .footer-text {
        font-size: 11px;
        bottom: 18px;
        padding: 0 18px;
    }
    .carousel{
        display:none;
    }
    .carousel,
    .carousel-inner,
    .carousel-item{
        height:100%;
    }

    .carousel-item img{
        width:100%;
        height:100%;
        object-fit:cover;
    }
    .event-grid{
        grid-template-columns:1fr;
    }

    .event-section{
        padding:70px 5% 50px;
    }
    .footer-info-grid{
        grid-template-columns:1fr;
    }
    .join-card{
        padding:50px 24px;
    }

    .join-card h2{
        font-size:24px;
    }
}

/* ===== Artikel Terbaru ===== */
.article-section{
    padding:0 5% 80px;
    max-width:1500px;
    margin:auto;
}
.article-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:28px;
}
.article-card{
    display:flex;
    flex-direction:column;
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    text-decoration:none;
    color:inherit;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    transition:transform .25s, box-shadow .25s;
}
.article-card:hover{
    transform:translateY(-6px);
    box-shadow:0 16px 36px rgba(0,0,0,.14);
}
.article-card-thumb{
    position:relative;
    aspect-ratio:16/10;
    background:#f2f2f2;
    overflow:hidden;
}
.article-card-thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
    transition:transform .5s;
}
.article-card:hover .article-card-thumb img{ transform:scale(1.05); }
.article-card-thumb-placeholder{
    width:100%;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    color:#bbb;
}
.article-card-body{
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:8px;
    flex:1;
}
.article-card-category{
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#b7965b;
}
.article-card-title{
    font-size:17px;
    font-weight:700;
    color:#111;
    line-height:1.35;
    margin:0;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}
.article-card-excerpt{
    font-size:14px;
    color:#666;
    line-height:1.6;
    margin:0;
    display:-webkit-box;
    -webkit-line-clamp:3;
    -webkit-box-orient:vertical;
    overflow:hidden;
}
.article-card-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-top:auto;
    padding-top:12px;
    border-top:1px solid #f0f0f0;
}
.article-card-date{
    display:flex;
    align-items:center;
    gap:6px;
    font-size:13px;
    color:#888;
}
.article-card-cta{
    display:flex;
    align-items:center;
    gap:4px;
    font-size:13px;
    font-weight:600;
    color:#b7965b;
    white-space:nowrap;
}
.article-more{
    text-align:center;
    margin-top:36px;
}
.btn-article-more{
    display:inline-flex;
    align-items:center;
    gap:8px;
    border:1.5px solid #b7965b;
    color:#8a6d36;
    font-weight:600;
    font-size:14px;
    padding:12px 28px;
    border-radius:12px;
    text-decoration:none;
    transition:.25s;
}
.btn-article-more:hover{
    background:#b7965b;
    color:#fff;
}
@media (max-width:1100px){
    .article-grid{ grid-template-columns:repeat(2, 1fr); }
}
@media (max-width:768px){
    .article-section{ padding:0 5% 50px; }
    .article-grid{ grid-template-columns:1fr; gap:20px; }
    .article-card-body{ padding:16px; }
    .article-more{ margin-top:28px; }
    .btn-article-more{ width:100%; justify-content:center; }
}

/* ===== Layanan (data dari tabel projects) ===== */
.service-section{ padding:0 5% 80px; max-width:1500px; margin:auto; }
.service-grid{ display:grid; grid-template-columns:repeat(3, 1fr); gap:28px; }
.service-card{
    display:flex; flex-direction:column; gap:10px;
    background:#fff; border-radius:16px; padding:28px;
    color:inherit; text-decoration:none;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    transition:transform .25s, box-shadow .25s;
}
a.service-card:hover{ transform:translateY(-6px); box-shadow:0 16px 36px rgba(0,0,0,.14); color:inherit; }
.service-card-icon{
    width:52px; height:52px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    background:#fbee47; color:#b7965b; font-size:26px; margin-bottom:6px;
}
.service-card-category{
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.5px; color:#b7965b;
}
.service-card-title{ margin:0; font-size:19px; font-weight:700; line-height:1.35; color:#111; }
.service-card-text{
    margin:0; font-size:14px; line-height:1.6; color:#666;
    display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;
}
.service-card-meta{ display:flex; align-items:center; gap:6px; font-size:13px; color:#888; }
.service-card-cta{
    display:flex; align-items:center; gap:4px; margin-top:auto; padding-top:14px;
    border-top:1px solid #f0f0f0; font-size:13px; font-weight:600; color:#000;
}
@media (max-width:1100px){ .service-grid{ grid-template-columns:repeat(2, 1fr); } }
@media (max-width:768px){
    .service-section{ padding:0 5% 50px; }
    .service-grid{ grid-template-columns:1fr; gap:20px; }
    .service-card{ padding:22px; }
}
</style>
<section class="hero">
    <div id="heroSlider" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('images/lebihtersistem.jpeg') }}">
            </div>
            {{-- <div class="carousel-item">
                <img src="{{ asset('images/coba-hero.jpeg') }}">
            </div> --}}
        </div>
        <div class="hero-overlay"></div>

        {{-- <button class="carousel-control-prev"
                data-bs-target="#heroSlider"
                data-bs-slide="prev">

            <span class="carousel-control-prev-icon"></span>

        </button>

        <button class="carousel-control-next"
                data-bs-target="#heroSlider"
                data-bs-slide="next">

            <span class="carousel-control-next-icon"></span>

        </button> --}}

        {{-- <div class="carousel-indicators">

            <button data-bs-target="#heroSlider"
                    data-bs-slide-to="0"
                    class="active"></button>

            <button data-bs-target="#heroSlider"
                    data-bs-slide-to="1"></button>

        </div> --}}

    </div>
        {{-- <div class="hero-caption">

            <h3>
                Sebuah Ruang untuk Bertumbuh
                <br>
                Sebuah Ruang untuk Kembali
            </h3>

        </div> --}}
</section>

@php
    $projectTypes = collect($projectTypes ?? []);
    $svcShowName  = collect(['layanan.show'])->first(fn ($r) => Route::has($r));
@endphp
@if ($projectTypes->isNotEmpty())
<section class="event-section" id="layanan" style="padding-bottom:50px">
    <div class="event-section-header" style="margin-bottom:0">
        <h2>Layanan</h2>
        <p>Pilihan layanan fotografi dan videografi untuk setiap momen berhargamu</p>
    </div>
</section>
<section class="service-section">
    <div class="service-grid">
        @foreach ($projectTypes as $t)
            @php $txt = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $t->description))), 130); @endphp
            <{{ $svcShowName ? 'a' : 'div' }} @if ($svcShowName) href="{{ route($svcShowName, $t->code) }}" @endif class="service-card">
                <div class="service-card-icon"><i class="ti ti-camera"></i></div>
                <h3 class="service-card-title">{{ $t->name }}</h3>
                @if ($txt)<p class="service-card-text">{{ $txt }}</p>@endif
                @if ($svcShowName)<span class="service-card-cta">Lihat Detail <i class="ti ti-arrow-right"></i></span>@endif
            </{{ $svcShowName ? 'a' : 'div' }}>
        @endforeach
    </div>
</section>
@endif

@php
    $articles = collect($articles ?? []);
    $artIndexUrl = collect(['artikel.index', 'articles.index'])->first(fn ($r) => Route::has($r));
    $artShowName = collect(['articles.show', 'artikel.show'])->first(fn ($r) => Route::has($r));
    $artTitle = fn ($a) => data_get($a, 'title') ?? data_get($a, 'judul');
    $artImg   = fn ($a) => data_get($a, 'featured_image') ?? data_get($a, 'thumbnail') ?? data_get($a, 'image') ?? data_get($a, 'cover');
    $artUrl   = fn ($a) => $artShowName ? route($artShowName, $a) : '#';
    $artCat   = fn ($a) => collect(data_get($a, 'categories', []))->pluck('name')->first() ?: data_get($a, 'category.name');
    $artText  = fn ($a) => data_get($a, 'excerpt') ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<', ' <', (string) data_get($a, 'content'))))), 140);
@endphp
@if ($articles->isNotEmpty())
<section class="event-section" style="padding-bottom:50px">
    <div class="event-section-header" style="margin-bottom:0">
        <h2>Artikel Terbaru</h2>
        <p>Bacaan pilihan untuk menemani langkahmu bertumbuh</p>
    </div>
</section>
<section class="article-section" id="artikel">
    <div class="article-grid">
        @foreach ($articles->take(3) as $a)
            @php $img = $artImg($a); $cat = $artCat($a); $date = data_get($a, 'published_at'); @endphp
            <a href="{{ $artUrl($a) }}" class="article-card">
                <div class="article-card-thumb">
                    @if ($img)
                        <img src="{{ asset('storage/' . $img) }}" alt="{{ $artTitle($a) }}" loading="lazy">
                    @else
                        <div class="article-card-thumb-placeholder"><i class="ti ti-article"></i></div>
                    @endif
                </div>
                <div class="article-card-body">
                    @if ($cat)
                        <span class="article-card-category">{{ $cat }}</span>
                    @endif
                    <h3 class="article-card-title">{{ $artTitle($a) }}</h3>
                    <p class="article-card-excerpt">{{ $artText($a) }}</p>
                    <div class="article-card-footer">
                        <span class="article-card-date">
                            @if ($date)
                                <i class="ti ti-calendar"></i> {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y') }}
                            @endif
                        </span>
                        <span class="article-card-cta">Baca <i class="ti ti-arrow-right"></i></span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    @if ($artIndexUrl)
        <div class="article-more">
            <a href="{{ route($artIndexUrl) }}" class="btn-article-more">Lihat Semua Artikel <i class="ti ti-arrow-right"></i></a>
        </div>
    @endif
</section>
@endif
{{-- <section class="join-section">
    <div class="join-card">
        <h2>Bergabung Bersama Kami</h2>
        <p>
            Jadilah bagian dari Ruang Kembali — ikuti kajian, event, dan program
            kebaikan yang membantu kamu terus bertumbuh.
        </p>
        <a href="{{ route('register') }}" class="btn-join">
            Bergabung Sekarang <i class="ti ti-arrow-right"></i>
        </a>
    </div>
</section> --}}
@endsection