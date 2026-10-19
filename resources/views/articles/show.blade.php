{{-- Simpan di: resources/views/articles/show.blade.php (Ruang Kembali) --}}
@extends('layouts.website')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    .show-page{ background:#f8f6f2; padding:150px 0 80px; font-family:'Poppins',sans-serif; color:#222; overflow-x:clip; }
    .show-wrap{ width:min(100% - 40px,1100px); margin:0 auto; }

    .show-hero{ width:100%; aspect-ratio:16/8; max-height:480px; border-radius:16px; overflow:hidden; background:#e4e0d8; box-shadow:0 8px 24px rgba(0,0,0,.08); margin-bottom:34px; }
    .show-hero img{ width:100%; height:100%; object-fit:cover; display:block; }

    .show-grid{ display:grid; grid-template-columns:72px minmax(0,1fr); gap:32px; align-items:start; }
    .show-share{ position:sticky; top:120px; text-align:center; }
    .show-share-label{ font-weight:600; font-size:13px; color:#b7965b; margin-bottom:10px; }
    .show-share-list{ display:flex; flex-direction:column; align-items:center; gap:10px; }

    .share-btn{
        width:44px; height:44px; display:flex; align-items:center; justify-content:center;
        border:0; border-radius:50%; background:#b7965b; color:#fff;
        font-size:20px; text-decoration:none; cursor:pointer; transition:background .2s, transform .2s;
    }
    .share-btn:hover{ background:#9c7d47; color:#fff; transform:translateY(-2px); }

    .show-card{ background:#fff; border-radius:16px; padding:40px 44px; box-shadow:0 8px 24px rgba(0,0,0,.06); min-width:0; }
    .article-title{ font-family:'Montserrat',sans-serif; font-weight:800; line-height:1.25; color:#111; font-size:clamp(26px,3.4vw,38px); margin:0 0 14px; }
    .show-meta{ font-size:13.5px; color:#888; margin-bottom:26px; padding-bottom:20px; border-bottom:1px solid #eee; }
    .show-meta i{ color:#b7965b; margin-right:4px; }

    .article-content{ font-size:1.02rem; line-height:1.85; color:#333; overflow-wrap:anywhere; }
    .article-content h2,.article-content h3{ font-family:'Montserrat',sans-serif; font-weight:700; color:#111; }
    .article-content h2{ margin:2rem 0 .75rem; font-size:1.5rem; }
    .article-content h3{ margin:1.6rem 0 .6rem; font-size:1.2rem; }
    .article-content p,.article-content ul,.article-content ol{ margin-bottom:1.2rem; }
    .article-content a{ color:#b7965b; }
    .article-content img{ max-width:100%; height:auto; border-radius:12px; }
    .article-content blockquote{ margin:1.5rem 0; padding:.5rem 1.25rem; border-left:4px solid #b7965b; background:#f8f6f2; color:#666; border-radius:0 8px 8px 0; }
    .article-content figure.table{ overflow-x:auto; margin:1.5rem 0; }
    .article-content table{ width:100%; border-collapse:collapse; }
    .article-content th,.article-content td{ border:1px solid #e7e2d8; padding:.5rem .75rem; text-align:left; }
    .article-content th{ background:#f8f6f2; }

    .cta-box{ margin:36px 0 8px; padding:26px 28px; border-radius:16px; background:#f8f6f2; border:1px solid #eadfc8;
        display:flex; flex-direction:column; gap:16px; }
    .cta-title{ font-family:'Montserrat',sans-serif; font-weight:700; font-size:20px; color:#111; margin:0 0 4px; }
    .cta-text{ margin:0; color:#666; font-size:14.5px; }
    .cta-btn{ display:inline-flex; align-items:center; justify-content:center; height:44px; padding:0 28px; border-radius:999px;
        background:#b7965b; color:#fff; font-weight:600; font-size:14px; text-transform:uppercase; text-decoration:none; align-self:flex-start; transition:background .2s; }
    .cta-btn:hover{ background:#9c7d47; color:#fff; }

    .show-tags{ border-top:1px solid #eee; margin-top:32px; padding-top:16px; font-size:13.5px; color:#888; }
    .show-share-mobile{ display:none; margin-bottom:16px; padding:14px 18px; background:#fff; border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,.06); align-items:center; gap:14px; }
    .show-share-mobile .show-share-label{ margin:0; }
    .show-share-mobile .show-share-list{ flex-direction:row; flex-wrap:wrap; justify-content:flex-start; gap:8px; }

    .show-related{ margin-top:28px; }
    .show-related h2{ font-family:'Montserrat',sans-serif; font-weight:700; font-size:18px; color:#111; margin:0 0 12px; }
    .show-related ul{ margin:0; padding-left:18px; }
    .show-related a{ color:#333; text-decoration:none; }
    .show-related a:hover{ color:#b7965b; }

    @media (max-width:991px){
        .show-grid{ grid-template-columns:1fr; }
        .show-share{ display:none; }
        .show-share-mobile{ display:flex; }
        .show-card{ padding:28px 24px; }
    }
    @media (max-width:760px){
        .show-page{ padding-top:120px; }
        .show-hero{ aspect-ratio:16/10; }
        .show-card{ padding:22px 18px; }
        .show-share-mobile{ padding:12px 14px; gap:10px; }
        .show-share-mobile .share-btn{ width:38px; height:38px; font-size:18px; }
        .cta-btn{ width:100%; align-self:stretch; }
    }
</style>
@endpush

@php
    $url   = route('articles.show', $article);
    $uEnc  = urlencode($url);
    $tEnc  = urlencode($article->title);
    $shares = [
        ['WhatsApp', 'ti-brand-whatsapp', "https://wa.me/?text={$tEnc}%20{$uEnc}"],
        ['Facebook', 'ti-brand-facebook', "https://www.facebook.com/sharer/sharer.php?u={$uEnc}"],
        ['Telegram', 'ti-brand-telegram', "https://t.me/share/url?url={$uEnc}&text={$tEnc}"],
        ['Twitter',  'ti-brand-twitter',  "https://twitter.com/intent/tweet?url={$uEnc}&text={$tEnc}"],
        ['LinkedIn', 'ti-brand-linkedin', "https://www.linkedin.com/sharing/share-offsite/?url={$uEnc}"],
    ];
@endphp

@section('content')
<main class="show-page">
<article class="show-wrap">
    @if($article->featured_image)
        <div class="show-hero">
            <img src="{{ asset('storage/'.$article->featured_image) }}" alt="{{ $article->title }}" fetchpriority="high">
        </div>
    @endif

    <div class="show-grid">
        <aside class="show-share">
            <div class="show-share-label">Share</div>
            <div class="show-share-list">
                @foreach($shares as [$label, $icon, $href])
                    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="share-btn" aria-label="Bagikan ke {{ $label }}" title="{{ $label }}"><i class="ti {{ $icon }}"></i></a>
                @endforeach
                <button type="button" class="share-btn" data-url="{{ $url }}" onclick="copyLink(this)" aria-label="Salin link" title="Salin link"><i class="ti ti-link"></i></button>
            </div>
        </aside>

        <div>

                <div class="show-share-mobile">
                    <div class="show-share-label">Share</div>
                    <div class="show-share-list">
                        @foreach($shares as [$label, $icon, $href])
                            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="share-btn" aria-label="Bagikan ke {{ $label }}" title="{{ $label }}"><i class="ti {{ $icon }}"></i></a>
                        @endforeach
                        <button type="button" class="share-btn" data-url="{{ $url }}" onclick="copyLink(this)" aria-label="Salin link" title="Salin link"><i class="ti ti-link"></i></button>
                    </div>
                </div>

            <div class="show-card">
                <h1 class="article-title">{{ $article->title }}</h1>

                <div class="show-meta">
                    <i class="ti ti-calendar"></i>
                    <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->translatedFormat('d F Y') }}</time>
                    · {{ $article->author?->name ?? 'Ruang Kembali' }}
                    @if($article->categories->isNotEmpty())
                        · {{ $article->categories->pluck('name')->implode(', ') }}
                    @endif
                </div>

                <div class="article-content">
                    {!! $article->renderedContent() !!}
                </div>

                @if($article->cta_enabled && $article->cta_url)
                    @php $ctaUrl = preg_match('~^(https?://|/|tel:|mailto:)~i', $article->cta_url) ? $article->cta_url : '#'; @endphp
                    <div class="cta-box">
                        <div>
                            @if($article->cta_title)<div class="cta-title">{{ $article->cta_title }}</div>@endif
                            @if($article->cta_text)<p class="cta-text">{{ $article->cta_text }}</p>@endif
                        </div>
                        <a href="{{ $ctaUrl }}" @if(str_starts_with($ctaUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif class="cta-btn">{{ $article->cta_button ?: 'Hubungi Kami' }}</a>
                    </div>
                @endif

                @if($article->tags->isNotEmpty())
                    <div class="show-tags">Tag: {{ $article->tags->pluck('name')->implode(', ') }}</div>
                @endif
            </div>

            @if(isset($related) && $related->isNotEmpty())
                <div class="show-card show-related">
                    <h2>Artikel lainnya</h2>
                    <ul>
                        @foreach($related as $r)
                            <li><a href="{{ route('articles.show', $r) }}">{{ $r->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</article>
</main>
@endsection

@push('scripts')
<script>
function copyLink(btn) {
    navigator.clipboard.writeText(btn.dataset.url).then(() => {
        const old = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-check"></i>';
        setTimeout(() => btn.innerHTML = old, 1500);
    });
}
</script>
@endpush