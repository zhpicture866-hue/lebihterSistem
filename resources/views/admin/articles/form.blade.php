@extends('tablar::page')

@section('content')
@php $isEdit = $article->exists; @endphp

<style>
    .ck-editor__editable { min-height: 380px; }
    #seo-checks li { padding: 2px 0; font-size: 14px; }
    .serp { font-family: Arial, sans-serif; max-width: 600px; }
    /* Kotak keterangan di bawah video */
.ck-content figure.media + p:has(> i:only-child),
.ck-content figure.media + p:has(> em:only-child) {
    text-align: center; color: #6c757d; font-size: .9em;
}
.ck-content figure.media + p:has(> br[data-cke-filler]:only-child) { position: relative; }
.ck-content figure.media + p:has(> br[data-cke-filler]:only-child)::before {
    content: 'Tambahkan keterangan video (opsional)';
    position: absolute; left: 0; right: 0; text-align: center;
    color: #adb5bd; font-style: italic; font-size: .9em; pointer-events: none;
}
.pv-img { background: #e9ecef center/cover no-repeat; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 12px; }
.pv-img-empty::after { content: 'Belum ada gambar'; }
</style>

<form method="POST" enctype="multipart/form-data"
      action="{{ $isEdit ? route('articles.update', $article) : route('articles.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('articles.index') }}"
                        class="btn btn-icon btn-dark"
                        aria-label="Kembali"
                        title="Kembali">
                            <i class="ti ti-arrow-left"></i>
                        </a>

                        <h2 class="page-title mb-0">
                            {{ $isEdit ? 'Edit' : 'Tulis' }} Artikel
                        </h2>
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <button type="submit" class="btn btn-dark">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">

                {{-- KOLOM KIRI --}}
                <div class="col-lg-8">

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Judul</label>
                                <input type="text" id="title" name="title" class="form-control"
                                       value="{{ old('title', $article->title) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slug <span class="text-muted">(kosongkan untuk otomatis)</span></label>
                                <input type="text" id="slug" name="slug" class="form-control"
                                       value="{{ old('slug', $article->slug) }}">
                            </div>

                            <div class="mb-3">
                                <textarea id="content" name="content">{{ old('content', $article->content) }}</textarea>
                            </div>
                            {{-- CTA di akhir artikel --}}
                            <div class="mb-3 border rounded-3 p-3 bg-light">
                                <label class="form-check mb-0">
                                    <input type="checkbox" id="f_cta_enabled" name="cta_enabled" value="1" class="form-check-input"
                                        @checked(old('cta_enabled', $article->cta_enabled))>
                                    <span class="form-check-label fw-semibold">Tampilkan kotak CTA di akhir artikel</span>
                                </label>

                                <div id="f_cta_fields" class="mt-3">
                                    <div class="mb-3">
                                        <label class="form-label">Judul CTA</label>
                                        <input type="text" name="cta_title" maxlength="120" class="form-control"
                                            value="{{ old('cta_title', $article->cta_title ?? 'Info Antosa Architect') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea name="cta_text" rows="2" maxlength="300" class="form-control">{{ old('cta_text', $article->cta_text ?? 'Gratis konsultasi untuk segala kebutuhan rumah dan bangunan Anda, hubungi kami dengan klik tombol berikut ini') }}</textarea>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label">Teks tombol</label>
                                            <input type="text" name="cta_button" maxlength="60" class="form-control"
                                                value="{{ old('cta_button', $article->cta_button ?? 'HUBUNGI KAMI') }}">
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label">Link tombol</label>
                                            <input type="text" name="cta_url" maxlength="255" class="form-control"
                                                placeholder="https://wa.me/62…"
                                                value="{{ old('cta_url', $article->cta_url ?: config('app.cta_url')) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Ringkasan (excerpt)</label>
                                <textarea id="excerpt" name="excerpt" rows="3" maxlength="300"
                                          class="form-control">{{ old('excerpt', $article->excerpt) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- SEO --}}
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title">SEO</h3></div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Focus keyword</label>
                                <input type="text" id="focus_keyword" name="focus_keyword" class="form-control"
                                       value="{{ old('focus_keyword', $article->focus_keyword) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta title <small id="mt-count" class="text-muted"></small></label>
                                <input type="text" id="meta_title" name="meta_title" maxlength="70" class="form-control"
                                       value="{{ old('meta_title', $article->meta_title) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta description <small id="md-count" class="text-muted"></small></label>
                                <textarea id="meta_description" name="meta_description" maxlength="170" rows="3"
                                          class="form-control">{{ old('meta_description', $article->meta_description) }}</textarea>
                            </div>

                            {{-- <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Canonical URL <span class="text-muted">(opsional)</span></label>
                                    <input type="url" name="canonical_url" class="form-control"
                                           value="{{ old('canonical_url', $article->canonical_url) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gambar share / OG image <span class="text-muted">(opsional)</span></label>
                                    <input type="file" name="og_image" accept="image/*" class="form-control">
                                </div>
                            </div>

                            <label class="form-check mb-4">
                                <input type="checkbox" name="noindex" value="1" class="form-check-input"
                                       @checked(old('noindex', $article->noindex))>
                                <span class="form-check-label">Jangan diindex Google (noindex)</span>
                            </label> --}}
                            @php
    $advancedOpen = old('canonical_url', $article->canonical_url)
        || $article->og_image
        || old('noindex', $article->noindex)
        || $errors->has('canonical_url')
        || $errors->has('og_image');
@endphp

<details class="border rounded-3 mb-4" @if($advancedOpen) open @endif>
    <summary class="px-3 py-2 fw-semibold" style="cursor:pointer">
        Pengaturan lanjutan <span class="text-muted fw-normal">(opsional, boleh dilewati)</span>
    </summary>

    <div class="p-3 border-top">

        {{-- Gambar share --}}
        <div class="mb-4">
            <label class="form-label">Gambar share</label>
            <input type="file" name="og_image" accept="image/*" class="form-control">
            <div class="text-muted small mt-1">
                Gambar yang muncul saat link artikel dibagikan ke WhatsApp atau Facebook.
                Kosongkan saja untuk memakai gambar utama artikel.
            </div>
            @if($article->og_image)
                <div class="small mt-2">
                    Gambar share saat ini:
                    <img src="{{ asset('storage/'.$article->og_image) }}" alt="" height="40" class="rounded ms-1">
                </div>
            @endif
        </div>

        {{-- Canonical URL --}}
        <div class="mb-4">
            <label class="form-label">Canonical URL</label>
            <input type="text" name="canonical_url" class="form-control" placeholder="https://…"
                   value="{{ old('canonical_url', $article->canonical_url) }}">
            <div class="text-muted small mt-1">
                Kosongkan, kecuali artikel ini salinan dari tulisan lain dan kamu ingin Google
                menganggap alamat aslinya sebagai yang utama. Kalau diisi salah, artikel ini bisa
                hilang dari hasil pencarian.
            </div>
        </div>

        {{-- Noindex --}}
        <div>
            <label class="form-check mb-0">
                <input type="checkbox" name="noindex" value="1" class="form-check-input"
                       @checked(old('noindex', $article->noindex))>
                <span class="form-check-label">Jangan tampilkan di Google (noindex)</span>
            </label>
            <div class="text-muted small ms-4">
                Centang kalau halaman ini tidak boleh muncul di hasil pencarian Google.
                Artikel tetap bisa dibuka lewat link-nya.
            </div>
        </div>
    </div>
</details>
                            <div class="form-label">Preview</div>
                            <div class="btn-group btn-group-sm mb-2" id="pv-tabs" role="group">
                                <button type="button" class="btn btn-outline-secondary active" data-pv="desktop">Google (desktop)</button>
                                <button type="button" class="btn btn-outline-secondary" data-pv="mobile">Google (mobile)</button>
                                <button type="button" class="btn btn-outline-secondary" data-pv="share">Share (WhatsApp/Facebook)</button>
                            </div>

                            <div class="border rounded p-3 mb-4 bg-white" style="max-width:640px;font-family:Arial,sans-serif">

                                {{-- Google desktop --}}
                                <div id="pv-desktop">
                                    <div class="pv-url" style="color:#188038;font-size:13px"></div>
                                    <div class="pv-title" style="color:#1a0dab;font-size:19px"></div>
                                    <div class="pv-desc" style="color:#4d5156;font-size:13px"></div>
                                </div>

                                {{-- Google mobile (dengan thumbnail) --}}
                                <div id="pv-mobile" style="display:none;max-width:380px">
                                    <div class="d-flex align-items-center mb-2" style="gap:8px">
                                        <img src="{{ asset('favicon.ico') }}" width="22" height="22" alt="" style="border-radius:50%">
                                        <div style="line-height:1.25">
                                            <div style="font-size:13px;color:#202124">Antosa Architect</div>
                                            <div class="pv-url" style="font-size:11px;color:#4d5156"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex" style="gap:12px">
                                        <div>
                                            <div class="pv-title" style="color:#1a0dab;font-size:17px;line-height:1.3"></div>
                                            <div class="pv-desc" style="color:#4d5156;font-size:13px;margin-top:4px"></div>
                                        </div>
                                        <div class="pv-img" style="width:92px;height:92px;flex:none;border-radius:8px"></div>
                                    </div>
                                </div>

                                {{-- Kartu share --}}
                                <div id="pv-share" style="display:none;max-width:500px;border:1px solid #dadde1;border-radius:8px;overflow:hidden">
                                    <div class="pv-img" style="width:100%;aspect-ratio:1.91/1"></div>
                                    <div style="background:#f0f2f5;padding:10px 12px">
                                        <div style="font-size:12px;color:#65676b;text-transform:uppercase">antosaarchitect.com</div>
                                        <div class="pv-title" style="font-size:16px;font-weight:600;color:#050505"></div>
                                        <div class="pv-desc" style="font-size:14px;color:#65676b"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-label">Analisis</div>
                            <div id="analysis" class="text-muted">Menganalisis…</div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN --}}
                <div class="col-lg-4">

                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title">Publikasi</h3></div>
                        <div class="card-body">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select mb-3">
                                <option value="draft" @selected(old('status', $article->status) === 'draft')>Draft</option>
                                <option value="published" @selected(old('status', $article->status) === 'published')>Terbit</option>
                            </select>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title">Gambar utama</h3></div>
                        <div class="card-body">
                            @if($article->featured_image)
                                <img src="{{ asset('storage/'.$article->featured_image) }}" alt=""
                                     class="img-fluid rounded mb-2">
                            @endif
                            <input type="file" name="featured_image" accept="image/*" class="form-control">
                        </div>
                    </div>

                    @php
                        $catTree      = \App\Models\Category::flatTree();
                        $selectedCats = old('categories', $article->categories->pluck('id')->all());
                    @endphp

                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title">Kategori</h3></div>
                        <div class="card-body">

                            <input type="search" id="cat-search" class="form-control form-control-sm mb-2" placeholder="Cari kategori…">

                            <div id="cat-list" class="border rounded p-2" style="max-height:220px;overflow:auto">
                                @foreach($catTree as $c)
                                    <label class="form-check d-block cat-item"
                                        data-id="{{ $c->id }}" data-depth="{{ $c->depth }}"
                                        data-name="{{ mb_strtolower($c->name) }}"
                                        style="margin-left:{{ $c->depth * 18 }}px">
                                        <input type="checkbox" name="categories[]" value="{{ $c->id }}" class="form-check-input"
                                            @checked(in_array($c->id, $selectedCats))>
                                        <span class="form-check-label">{{ $c->name }}</span>
                                    </label>
                                @endforeach
                                <div id="cat-empty" class="text-muted small" style="display:{{ $catTree->isEmpty() ? 'block' : 'none' }}">
                                    Belum ada kategori.
                                </div>
                            </div>

                            <a href="#" id="cat-toggle" class="d-inline-block mt-2 small">+ Tambah kategori baru</a>

                            <div id="cat-new" class="mt-2" style="display:none">
                                <input type="text" id="cat-name" class="form-control form-control-sm mb-2"
                                    placeholder="Nama kategori" maxlength="100">
                                <select id="cat-parent" class="form-select form-select-sm mb-2">
                                    <option value="">— Tanpa induk —</option>
                                    @foreach($catTree as $c)
                                        <option value="{{ $c->id }}">{{ str_repeat('— ', $c->depth) }}{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" id="cat-add" class="btn btn-sm btn-outline-primary">Tambah kategori</button>
                                <div id="cat-msg" class="small mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title">Tag</h3></div>
                        <div class="card-body">
                            <input type="text" name="tags" class="form-control"
                                   placeholder="pisahkan dengan koma"
                                   value="{{ old('tags', $article->tags->pluck('name')->implode(', ')) }}">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('js')

<script>
const $ = id => document.getElementById(id);
const ANALYZE_URL  = @json(route('articles.analyze'));
const ARTICLE_ID   = @json($article->id);
const HAS_FEATURED = @json((bool) $article->featured_image);
const CSRF         = @json(csrf_token());
const EXISTING_OG = @json(($article->og_auto ?: $article->og_image) ? asset('storage/'.($article->og_auto ?: $article->og_image)) : null);
const EXISTING_FEATURED = @json($article->featured_image ? asset('storage/'.$article->featured_image) : null);
let ogObjUrl = null, featObjUrl = null;   // pratinjau file yang baru dipilih (belum diupload)
let editor, timer;

const YT_RE = /^https?:\/\/(?:www\.|m\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)[\w-]{11}\S*$/i;

ClassicEditor.create($('content'), {
    toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','blockQuote','insertTable','mediaEmbed','undo','redo'],
}).then(ed => {
    editor = ed;
    // Setelah video dimasukkan, buat kotak keterangan (paragraf miring kosong) di bawahnya
    ed.commands.get('mediaEmbed').on('execute', () => {
        const media = ed.model.document.selection.getSelectedElement();
        if (!media || media.name !== 'media') return;

        const next = media.nextSibling;
        if (next && next.is('element', 'paragraph')) return;   // sudah ada paragraf di bawahnya

        ed.model.change(writer => {
            const p = writer.createElement('paragraph');
            writer.insert(p, media, 'after');
            writer.setSelection(p, 'in');
            writer.setSelectionAttribute('italic', true);       // teks yang diketik langsung miring
        });
    }, { priority: 'low' });
    // Tempel URL YouTube di paragraf kosong -> langsung jadi video
    ed.editing.view.document.on('clipboardInput', (evt, data) => {
        const text = (data.dataTransfer.getData('text/plain') || '').trim();
        const parent = ed.model.document.selection.getFirstPosition().parent;
        if (YT_RE.test(text) && parent.is('element', 'paragraph') && parent.isEmpty) {
            evt.stop();
            ed.execute('mediaEmbed', text);
        }
    });

    ed.model.document.on('change:data', onChange);
    onChange();
});

// ---------- CTA ----------
const CTA_RE  = /^\[cta\s+([\s\S]*)\]$/i;
const ctaDlg  = $('cta-dialog');
let ctaTarget = null;   // paragraf CTA yang sedang diedit (null = sisip baru)

const ctaDefaults = () => {
    let url = '';
    try { url = localStorage.getItem('cta_url') || ''; } catch (e) {}
    return {
        judul: 'Info Antosa Architect',
        teks: 'Gratis konsultasi untuk segala kebutuhan rumah dan bangunan Anda, hubungi kami dengan klik tombol berikut ini',
        tombol: 'HUBUNGI KAMI',
        url,
    };
};

const ctaClean = s => String(s).replace(/["“”]/g, "'").replace(/[\[\]\r\n]+/g, ' ').trim();

const ctaBuild = v =>
    `[cta judul="${ctaClean(v.judul)}" teks="${ctaClean(v.teks)}" tombol="${ctaClean(v.tombol)}" url="${ctaClean(v.url)}"]`;

function ctaParse(str) {
    const out = {};
    for (const m of str.matchAll(/(\w+)\s*=\s*["“”]([^"“”]*)["“”]/g)) out[m[1].toLowerCase()] = m[2];
    return out;
}

const paraText = p => Array.from(p.getChildren()).map(n => n.data ?? '').join('').trim();

const ctaToggle = () => $('f_cta_fields').style.display = $('f_cta_enabled').checked ? '' : 'none';
$('f_cta_enabled').addEventListener('change', ctaToggle);
ctaToggle();

['title','slug','focus_keyword','meta_title','meta_description','excerpt'].forEach(id =>
    $(id).addEventListener('input', onChange));
// Pratinjau gambar yang baru dipilih
document.querySelector('input[name=og_image]').addEventListener('change', e => {
    if (ogObjUrl) URL.revokeObjectURL(ogObjUrl);
    const f = e.target.files[0];
    ogObjUrl = f ? URL.createObjectURL(f) : null;
    onChange();
});
document.querySelector('input[name=featured_image]').addEventListener('change', e => {
    if (featObjUrl) URL.revokeObjectURL(featObjUrl);
    const f = e.target.files[0];
    featObjUrl = f ? URL.createObjectURL(f) : null;
    onChange();
});

// Tab preview
document.querySelectorAll('#pv-tabs button').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('#pv-tabs button').forEach(x => x.classList.toggle('active', x === b));
    ['desktop', 'mobile', 'share'].forEach(k => $('pv-' + k).style.display = (k === b.dataset.pv) ? '' : 'none');
}));

function onChange() {
    updatePreview();
    clearTimeout(timer);
    timer = setTimeout(runAnalysis, 700);
}

const slugify = s => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
    .replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');

// Counter dan preview Google (lokal, tanpa server)
function updatePreview() {
    const title = $('title').value.trim();
    const slug  = $('slug').value.trim() || slugify(title);
    const mt    = $('meta_title').value.trim() || title;
    const md    = $('meta_description').value.trim() || $('excerpt').value.trim();

    // Urutan sama dengan di halaman publik: gambar share > gambar utama
    const img = ogObjUrl || EXISTING_OG || featObjUrl || EXISTING_FEATURED || null;

    $('mt-count').textContent = `(${$('meta_title').value.length}/70)`;
    $('md-count').textContent = `(${$('meta_description').value.length}/170)`;

    const set = (cls, text) => document.querySelectorAll('.' + cls).forEach(el => el.textContent = text);
    set('pv-url',   `antosaarchitect.com › artikel › ${slug}`);
    set('pv-title', mt.slice(0, 60) || 'Judul artikel');
    set('pv-desc',  md.slice(0, 160) || 'Deskripsi artikel akan tampil di sini.');

    document.querySelectorAll('.pv-img').forEach(el => {
        el.style.backgroundImage = img ? `url("${img}")` : 'none';
        el.classList.toggle('pv-img-empty', !img);
    });
}

// Analisis lengkap (dihitung di server oleh SeoAnalyzer)
async function runAnalysis() {
    const hasNewFeatured = document.querySelector('input[name=featured_image]').files.length > 0;
    try {
        const res = await fetch(ANALYZE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({
                title: $('title').value,
                slug: $('slug').value,
                content: editor ? editor.getData() : '',
                excerpt: $('excerpt').value,
                meta_title: $('meta_title').value,
                meta_description: $('meta_description').value,
                focus_keyword: $('focus_keyword').value,
                article_id: ARTICLE_ID,
                has_featured: HAS_FEATURED || hasNewFeatured,
            }),
        });
        if (!res.ok) throw new Error(res.status);
        const data = await res.json();
        $('analysis').innerHTML =
            renderSection('Analisis SEO', data.seo) +
            renderSection('Keterbacaan', data.readability);
    } catch (e) {
        $('analysis').textContent = 'Analisis gagal dimuat. Coba muat ulang halaman.';
    }
}

const COLORS = { good: '#188038', ok: '#f59e0b', bad: '#d93025' };
const esc = s => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
const dot = c => `<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:${c};margin:4px 8px 0 0;flex:none"></span>`;

function renderSection(title, data) {
    const color = data.score >= 70 ? COLORS.good : data.score >= 40 ? COLORS.ok : COLORS.bad;
    const groups = [['bad', 'Masalah'], ['ok', 'Perbaikan'], ['good', 'Hasil baik']];

    let html = `<div class="mb-4">
        <div class="d-flex align-items-center mb-2">${dot(color)}<strong>${title}</strong>
        <span class="ms-2 text-muted">${data.score}/100</span></div>`;

    for (const [key, label] of groups) {
        const items = data.results.filter(r => r.status === key);
        if (!items.length) continue;
        html += `<details ${key !== 'good' ? 'open' : ''} class="mb-2">
            <summary><strong>${label} (${items.length})</strong></summary>
            <ul class="list-unstyled mt-2 mb-0">${items.map(i =>
                `<li class="d-flex py-1">${dot(COLORS[key])}<span>${esc(i.text)}</span></li>`).join('')}</ul>
        </details>`;
    }
    return html + '</div>';
}

// ---------- Kategori ----------
const CAT_URL  = @json(route('categories.store'));
const catList  = $('cat-list');
const catMsg   = $('cat-msg');

// Cari kategori
$('cat-search').addEventListener('input', e => {
    const q = e.target.value.trim().toLowerCase();
    catList.querySelectorAll('.cat-item').forEach(el =>
        el.style.display = (!q || el.dataset.name.includes(q)) ? '' : 'none');
});

// Buka/tutup isian kategori baru
$('cat-toggle').addEventListener('click', e => {
    e.preventDefault();
    const box = $('cat-new');
    box.style.display = box.style.display === 'none' ? '' : 'none';
    if (box.style.display !== 'none') $('cat-name').focus();
});

// Enter di kolom nama = tambah (tanpa mengirim form artikel)
$('cat-name').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); $('cat-add').click(); }
});

$('cat-add').addEventListener('click', async () => {
    const name = $('cat-name').value.trim();
    catMsg.className = 'small mt-2 text-danger';
    catMsg.textContent = '';
    if (!name) { catMsg.textContent = 'Nama kategori wajib diisi.'; return; }

    const btn = $('cat-add');
    btn.disabled = true;
    try {
        const res = await fetch(CAT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ name, parent_id: $('cat-parent').value || null }),
        });
        const data = await res.json();

        if (!res.ok) {
            catMsg.textContent = data.errors ? Object.values(data.errors).flat()[0] : (data.message || 'Gagal menambah kategori.');
            return;
        }

        addCategoryToUI(data);
        $('cat-name').value = '';
        catMsg.className = 'small mt-2 text-success';
        catMsg.textContent = data.existing ? 'Kategori sudah ada, langsung dicentang.' : 'Kategori ditambahkan.';
    } catch (err) {
        catMsg.textContent = 'Gagal menambah kategori.';
    } finally {
        btn.disabled = false;
    }
});

function addCategoryToUI(c) {
    // Pastikan kategori tidak tersembunyi oleh pencarian
    $('cat-search').value = '';
    catList.querySelectorAll('.cat-item').forEach(el => el.style.display = '');

    let input = catList.querySelector(`.cat-item input[value="${c.id}"]`);

    if (!input) {
        const parent = c.parent_id ? catList.querySelector(`.cat-item[data-id="${c.parent_id}"]`) : null;
        const depth  = parent ? Number(parent.dataset.depth) + 1 : 0;

        const label = document.createElement('label');
        label.className = 'form-check d-block cat-item';
        label.dataset.id = c.id;
        label.dataset.depth = depth;
        label.dataset.name = c.name.toLowerCase();
        label.style.marginLeft = (depth * 18) + 'px';
        label.innerHTML = '<input type="checkbox" name="categories[]" class="form-check-input"><span class="form-check-label"></span>';
        label.querySelector('input').value = c.id;
        label.querySelector('span').textContent = c.name;

        if (parent) {
            // taruh di akhir anak-anak induknya
            let ref = parent, n = parent.nextElementSibling;
            while (n && n.classList.contains('cat-item') && Number(n.dataset.depth) > Number(parent.dataset.depth)) {
                ref = n;
                n = n.nextElementSibling;
            }
            ref.after(label);
        } else {
            catList.insertBefore(label, $('cat-empty'));
        }

        $('cat-empty').style.display = 'none';
        input = label.querySelector('input');
        rebuildParentOptions();
        label.scrollIntoView({ block: 'nearest' });
    }

    input.checked = true;
}

function rebuildParentOptions() {
    const sel  = $('cat-parent');
    const keep = sel.value;
    sel.innerHTML = '<option value="">— Tanpa induk —</option>';
    catList.querySelectorAll('.cat-item').forEach(el => {
        const o = document.createElement('option');
        o.value = el.dataset.id;
        o.textContent = '— '.repeat(Number(el.dataset.depth)) + el.querySelector('.form-check-label').textContent;
        sel.appendChild(o);
    });
    sel.value = keep;
}
</script>
@endpush