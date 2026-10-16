{{-- Simpan di: resources/views/articles/show.blade.php (timpa versi lama) --}}
@extends('layouts.website')

@push('styles')
<style>
    .article-title { font-family: 'Saira', sans-serif; font-weight: 700; line-height: 1.25; }

    .share-btn {
        width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
        border: 0; border-radius: 6px; background: #2f8fb5; color: #fff;
        font-size: 22px; text-decoration: none; cursor: pointer;
    }
    .share-btn:hover { background: #247595; color: #fff; }

    .article-content { font-size: 1.05rem; line-height: 1.8; }
    .article-content h2 { margin: 2rem 0 .75rem; }
    .article-content h3 { margin: 1.6rem 0 .6rem; }
    .article-content p, .article-content ul, .article-content ol { margin-bottom: 1.2rem; }
    .article-content img { max-width: 100%; height: auto; border-radius: .5rem; }
    .article-content blockquote { margin: 1.5rem 0; padding: .25rem 1.25rem; border-left: 4px solid #dee2e6; color: #6c757d; }
    .article-content figure.table { overflow-x: auto; margin: 1.5rem 0; }
    .article-content table { width: 100%; border-collapse: collapse; }
    .article-content th, .article-content td { border: 1px solid #dee2e6; padding: .5rem .75rem; text-align: left; }
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
<article class="container py-4" style="max-width:1100px">
    {{-- Gambar utama, di atas judul --}}
    @if($article->featured_image)
        <img src="{{ asset('storage/'.$article->featured_image) }}" alt="{{ $article->title }}"
             class="w-100 rounded-3 mb-4" fetchpriority="high"
             style="max-height:480px;object-fit:cover">
    @endif

    <div class="row">

        {{-- Share (desktop: kolom kiri, menempel saat scroll) --}}
        <aside class="col-lg-2 d-none d-lg-block">
            <div class="text-center">
                <div class="fw-semibold mb-2" style="color:#2f8fb5">Share</div>
                <div class="d-flex flex-column align-items-center gap-2">
                    @foreach($shares as [$label, $icon, $href])
                        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                           class="share-btn" aria-label="Bagikan ke {{ $label }}" title="{{ $label }}">
                            <i class="ti {{ $icon }}"></i>
                        </a>
                    @endforeach
                    <button type="button" class="share-btn" data-url="{{ $url }}" onclick="copyLink(this)"
                            aria-label="Salin link" title="Salin link">
                        <i class="ti ti-link"></i>
                    </button>
                </div>
            </div>
        </aside>

        {{-- Judul dan isi --}}
        <div class="col-lg-9">
            <h1 class="article-title mb-3" style="font-size:clamp(28px,4.5vw,42px)">{{ $article->title }}</h1>

            <div class="text-muted small mb-4">
                <i class="ti ti-calendar me-1"></i>
                <time datetime="{{ $article->published_at->toDateString() }}">
                    {{ $article->published_at->translatedFormat('d F Y') }}
                </time>
                · {{ $article->author?->name ?? 'Antosa Architect' }}
                @if($article->categories->isNotEmpty())
                    · {{ $article->categories->pluck('name')->implode(', ') }}
                @endif
            </div>

            {{-- Konten sudah disanitasi (mews/purifier) saat disimpan --}}
            <div class="article-content">
                {!! $article->renderedContent() !!}
            </div>
            @if($article->cta_enabled && $article->cta_url)
    @php
        $ctaUrl = preg_match('~^(https?://|/|tel:|mailto:)~i', $article->cta_url) ? $article->cta_url : '#';
    @endphp
    <div class="cta-box my-5 p-4 rounded-3 border bg-light d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            @if($article->cta_title)
                <div class="h4 fw-bold mb-1">{{ $article->cta_title }}</div>
            @endif
            @if($article->cta_text)
                <p class="mb-0 text-muted">{{ $article->cta_text }}</p>
            @endif
        </div>
        <a href="{{ $ctaUrl }}"
           @if(str_starts_with($ctaUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif
           class="btn btn-primary btn-lg text-uppercase flex-shrink-0">{{ $article->cta_button ?: 'Hubungi Kami' }}</a>
    </div>
@endif
            @if($article->tags->isNotEmpty())
                <div class="border-top mt-5 pt-3 small text-muted">
                    Tag: {{ $article->tags->pluck('name')->implode(', ') }}
                </div>
            @endif

            {{-- Share (mobile: di bawah artikel) --}}
            <div class="d-lg-none border-top mt-4 pt-3">
                <div class="fw-semibold mb-2" style="color:#2f8fb5">Share</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($shares as [$label, $icon, $href])
                        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                           class="share-btn" aria-label="Bagikan ke {{ $label }}" title="{{ $label }}">
                            <i class="ti {{ $icon }}"></i>
                        </a>
                    @endforeach
                    <button type="button" class="share-btn" data-url="{{ $url }}" onclick="copyLink(this)"
                            aria-label="Salin link" title="Salin link">
                        <i class="ti ti-link"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</article>

{{-- Opsional: butuh variabel $related dari controller --}}
@if(isset($related) && $related->isNotEmpty())
    <section class="container pb-5" style="max-width:1100px">
        <div class="row">
            <div class="col-lg-9 offset-lg-2">
                <h2 class="h5">Artikel lainnya</h2>
                <ul>
                    @foreach($related as $r)
                        <li><a href="{{ route('articles.show', $r) }}">{{ $r->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endif
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