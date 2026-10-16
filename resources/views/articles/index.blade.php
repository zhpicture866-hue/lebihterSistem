@extends('layouts.website')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
.art{ font-family:'Poppins',sans-serif; color:#222; background:#f8f6f2; padding:150px 0 90px; overflow-x:clip; }
.art-wrap{ width:min(100% - 40px,1200px); margin:0 auto; }

.art-banner{ width:100%; aspect-ratio:16/6; border-radius:16px; overflow:hidden; background:#e4e0d8; margin-bottom:8px; }
.art-banner img{ width:100%; height:100%; object-fit:cover; display:block; }

.art-title{ font-family:'Montserrat',sans-serif; font-weight:700; color:#111; font-size:clamp(26px,2.4vw,38px); line-height:1.25; margin:18px 0 8px; text-align:center; }
.art-title::after{ content:""; display:block; width:64px; height:3px; background:#b7965b; border-radius:3px; margin:14px auto 0; }
.art-sub{ margin:14px 0 34px; font-size:15px; color:#666; text-align:center; }

.art-list{ display:flex; flex-direction:column; gap:22px; margin-top:34px; }
.art-row{
    display:grid; grid-template-columns:340px minmax(0,1fr); column-gap:28px; align-items:center;
    background:#fff; padding:20px; border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,.08);
    transition:box-shadow .25s, transform .25s;
}
.art-row:hover{ box-shadow:0 14px 32px rgba(0,0,0,.12); transform:translateY(-3px); }
.art-thumb{ display:block; aspect-ratio:4/3; overflow:hidden; background:#e9e5dc; border-radius:12px; }
.art-thumb img{ width:100%; height:100%; object-fit:cover; display:block; transition:transform .5s; }
.art-row:hover .art-thumb img{ transform:scale(1.05); }
.art-cat{ font-size:12px; font-weight:600; letter-spacing:1.5px; text-transform:uppercase; color:#b7965b; margin:0 0 8px; }
.art-h{ font-family:'Montserrat',sans-serif; font-weight:700; font-size:clamp(18px,1.6vw,24px); line-height:1.35; margin:0 0 12px; }
.art-h a{ color:#111; text-decoration:none; }
.art-h a:hover{ color:#b7965b; }
.art-ex{ font-size:14.5px; line-height:1.7; color:#555; margin:0 0 14px; max-width:680px;
    display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
.art-date{ display:block; font-size:13px; color:#888; margin:0 0 16px; }
.art-btn{
    display:inline-flex; align-items:center; justify-content:center; min-width:190px; height:40px; padding:0 22px;
    background:#b7965b; color:#fff; font-weight:600; font-size:14px; border-radius:999px;
    text-decoration:none; transition:background .2s, transform .2s;
}
.art-btn:hover{ background:#9c7d47; color:#fff; }
.art-empty{ padding:60px 12px; color:#666; text-align:center; }

/* paginasi */
.art-pager{ margin-top:44px; display:flex; justify-content:center; }
.pg{ display:flex; flex-wrap:wrap; justify-content:center; gap:6px; font-family:'Poppins',sans-serif; }
.pg-i{
    display:inline-flex; align-items:center; justify-content:center; min-width:38px; height:38px; padding:0 12px;
    background:#fff; color:#555; font-size:15px; text-decoration:none; border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,.06); transition:background .2s, color .2s;
}
a.pg-i:hover{ background:#f1e8d6; color:#111; }
.pg-on{ background:#b7965b !important; color:#fff !important; }
.pg-dots{ background:transparent; box-shadow:none; }

@media (max-width:1000px){
    .art-row{ grid-template-columns:260px minmax(0,1fr); column-gap:20px; }
}
@media (max-width:760px){
    .art{ padding-top:120px; }
    .art-row{ grid-template-columns:1fr; row-gap:16px; padding:14px; }
    .art-banner{ aspect-ratio:16/9; }
    .art-btn{ min-width:0; width:100%; }
}
</style>
@endpush

@section('content')
@php
    $bannerFile = 'images/banner-artikel.webp';
    $hasBanner  = file_exists(public_path($bannerFile));
@endphp
<section class="art">
    <div class="art-wrap">
        @if ($hasBanner)
            <div class="art-banner"><img src="{{ asset($bannerFile) }}" alt="Artikel Ruang Kembali"></div>
        @endif

        <h1 class="art-title">Artikel Lebih Tersistem</h1>
        <p class="art-sub">Cerita, tips, dan kabar terbaru dari Lebih Tersistem.</p>

        @if ($articles->isEmpty())
            <p class="art-empty">Belum ada artikel.</p>
        @else
            <div class="art-list">
                @foreach ($articles as $a)
                    @php
                        $excerpt = $a->excerpt ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<', ' <', $a->content)))), 150);
                    @endphp
                    <article class="art-row">
                        <a href="{{ route('articles.show', $a) }}" class="art-thumb" tabindex="-1" aria-hidden="true">
                            @if ($a->featured_image)
                                <img src="{{ asset('storage/'.$a->featured_image) }}" alt="{{ $a->title }}" loading="lazy">
                            @endif
                        </a>
                        <div>
                            @if ($a->categories->isNotEmpty())
                                <p class="art-cat">{{ $a->categories->pluck('name')->implode(' · ') }}</p>
                            @endif
                            <h2 class="art-h"><a href="{{ route('articles.show', $a) }}">{{ $a->title }}</a></h2>
                            <p class="art-ex">{{ $excerpt }}</p>
                            @if ($a->published_at)
                                <time class="art-date" datetime="{{ $a->published_at->toDateString() }}">{{ $a->published_at->translatedFormat('d F Y') }}</time>
                            @endif
                            <a href="{{ route('articles.show', $a) }}" class="art-btn">Baca Selengkapnya</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="art-pager">
                {{ $articles->links('pagination.artikel') }}
            </div>
        @endif
    </div>
</section>
@endsection