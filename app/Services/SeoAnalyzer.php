<?php

namespace App\Services;

use App\Models\Article;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

class SeoAnalyzer
{
    private const TRANSITIONS = [
        'namun', 'tetapi', 'tapi', 'karena', 'sebab', 'oleh karena itu', 'oleh sebab itu', 'sehingga',
        'akibatnya', 'selain itu', 'di samping itu', 'pertama', 'kedua', 'ketiga', 'selanjutnya',
        'kemudian', 'akhirnya', 'misalnya', 'contohnya', 'sebagai contoh', 'dengan demikian', 'jadi',
        'meskipun', 'walaupun', 'sementara itu', 'sedangkan', 'bahkan', 'terutama', 'pada akhirnya',
        'untuk itu', 'demikian pula', 'begitu juga', 'setelah itu', 'sebelum itu', 'pada dasarnya',
        'singkatnya', 'intinya', 'yang terpenting', 'lebih lanjut', 'selain', 'apabila', 'jika',
        'agar', 'supaya', 'sebaliknya', 'di sisi lain', 'dengan kata lain', 'serta', 'selama',
    ];

    /** Dipakai afterSave() dan perintah tinker untuk menghitung ulang skor. */
    public static function scores(Article $a): array
    {
        $r = self::analyze($a, ! empty($a->featured_image), $a->id);

        return [
            'seo_score'         => $r['seo']['score'],
            'readability_score' => $r['readability']['score'],
        ];
    }

    /** Dipakai endpoint analyze (form) dan scores(). */
    public static function analyze(Article $a, bool $hasFeatured = false, ?int $ignoreId = null): array
    {
        $html = \App\Support\Shortcodes::strip((string) $a->content);
        $text  = self::plain($html);
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
        $xp    = self::xpath($html);

        return [
            'seo'         => self::seo($a, $text, $words, $xp, $hasFeatured, $ignoreId),
            'readability' => self::readability($html, $text, $words, $xp),
        ];
    }

    // ------------------------------------------------------------------ SEO

    private static function seo(Article $a, string $text, int $words, DOMXPath $xp, bool $hasFeatured, ?int $ignoreId): array
    {
        $kw    = mb_strtolower(trim((string) $a->focus_keyword));
        $title = trim((string) ($a->meta_title ?: $a->title));
        $desc  = trim((string) ($a->meta_description ?: $a->excerpt));
        $slug  = (string) $a->slug;
        $lc    = mb_strtolower($text);
        $r     = [];

        // Meta description
        $dl = mb_strlen($desc);
        $r[] = match (true) {
            $dl === 0 => self::c('meta_len', 'bad', 'Meta description belum ada. Tulis ringkasan 120–156 karakter.'),
            $dl < 120 => self::c('meta_len', 'ok', "Meta description terlalu pendek ({$dl} karakter). Idealnya 120–156."),
            $dl > 156 => self::c('meta_len', 'ok', "Meta description terlalu panjang ({$dl} karakter). Idealnya 120–156."),
            default   => self::c('meta_len', 'good', "Panjang meta description sudah pas ({$dl} karakter)."),
        };

        // Lebar SEO title (perkiraan berdasarkan jumlah karakter)
        $tl = mb_strlen($title);
        $r[] = match (true) {
            $tl === 0           => self::c('title_w', 'bad', 'SEO title belum ada.'),
            $tl < 30 || $tl > 60 => self::c('title_w', 'ok', "SEO title {$tl} karakter. Idealnya 30–60."),
            default             => self::c('title_w', 'good', "Lebar SEO title sudah pas ({$tl} karakter)."),
        };

        // Panjang teks
        $r[] = match (true) {
            $words >= 300 => self::c('text_len', 'good', "Teks berisi {$words} kata. Bagus!"),
            $words >= 200 => self::c('text_len', 'ok', "Teks baru {$words} kata. Idealnya minimal 300."),
            default       => self::c('text_len', 'bad', "Teks hanya {$words} kata. Idealnya minimal 300."),
        };

        // Link internal dan keluar
        $hosts = collect([
            parse_url((string) config('app.url'), PHP_URL_HOST),
            app()->runningInConsole() ? null : request()->getHost(),
        ])->filter()->flatMap(function ($h) {
            $bare = preg_replace('/^www\./', '', strtolower($h));

            return [$bare, 'www.' . $bare];
        })->unique()->values()->all();
        $internal = 0;
        $outbound = 0;
        foreach ($xp->query('//a[@href]') as $n) {
            $href = trim($n->getAttribute('href'));
            if ($href === '' || str_starts_with($href, '#') || preg_match('/^(mailto|tel):/i', $href)) {
                continue;
            }
            $host = parse_url($href, PHP_URL_HOST);
            if (! $host || in_array(strtolower($host), $hosts, true)) {
                $internal++;
            } else {
                $outbound++;
            }
        }
        $r[] = $internal > 0
            ? self::c('internal', 'good', "Ada {$internal} link internal. Bagus!")
            : self::c('internal', 'ok', 'Belum ada link internal. Tautkan ke artikel atau halaman lain di situsmu.');
        $r[] = $outbound > 0
            ? self::c('outbound', 'good', "Ada {$outbound} link keluar. Bagus!")
            : self::c('outbound', 'ok', 'Belum ada link keluar. Tautkan ke sumber terpercaya.');

        // Gambar
        $imgs     = $xp->query('//img');
        $imgTotal = $imgs->length + ($hasFeatured ? 1 : 0);
        $r[] = $imgTotal > 0
            ? self::c('images', 'good', "Ada {$imgTotal} gambar. Bagus!")
            : self::c('images', 'bad', 'Belum ada gambar. Tambahkan gambar utama atau gambar di konten.');

        // Keyphrase
        if ($kw === '') {
            $r[] = self::c('kw_set', 'bad', 'Focus keyphrase belum diisi. Isi dulu agar 9 pemeriksaan keyphrase bisa berjalan.');

            return ['score' => self::score($r, 16), 'results' => $r];
        }
        $r[] = self::c('kw_set', 'good', 'Focus keyphrase sudah diisi.');

        $kwWords = preg_split('/\s+/u', $kw, -1, PREG_SPLIT_NO_EMPTY);
        $kwCount = count($kwWords);

        $r[] = match (true) {
            $kwCount <= 4 => self::c('kw_len', 'good', "Panjang keyphrase {$kwCount} kata. Bagus!"),
            $kwCount <= 6 => self::c('kw_len', 'ok', "Keyphrase {$kwCount} kata. Sebaiknya maksimal 4."),
            default       => self::c('kw_len', 'bad', "Keyphrase {$kwCount} kata, terlalu panjang. Maksimal 4."),
        };

        // Pendahuluan
        $p     = $xp->query('//p')->item(0);
        $first = $p ? mb_strtolower(trim($p->textContent)) : mb_substr($lc, 0, 200);
        $r[] = str_contains($first, $kw)
            ? self::c('kw_intro', 'good', 'Keyphrase ada di pendahuluan. Bagus!')
            : self::c('kw_intro', 'bad', 'Keyphrase belum ada di paragraf pertama.');

        // Densitas
        $occ = mb_substr_count($lc, $kw);
        $den = $words > 0 ? $occ / $words * 100 : 0;
        $denTxt = number_format($den, 1, ',', '.');
        $r[] = match (true) {
            $occ === 0 => self::c('kw_density', 'bad', 'Keyphrase tidak ditemukan di teks. Gunakan beberapa kali.'),
            $den < 0.5 => self::c('kw_density', 'ok', "Keyphrase ditemukan {$occ} kali ({$denTxt}%). Terlalu jarang, idealnya 0,5–3%."),
            $den <= 3  => self::c('kw_density', 'good', "Keyphrase ditemukan {$occ} kali ({$denTxt}%). Bagus!"),
            default    => self::c('kw_density', 'bad', "Keyphrase ditemukan {$occ} kali ({$denTxt}%). Terlalu sering, idealnya maksimal 3%."),
        };

        // SEO title
        $lt  = mb_strtolower($title);
        $pos = mb_strpos($lt, $kw);
        $r[] = match (true) {
            $pos === 0     => self::c('kw_title', 'good', 'Keyphrase ada di awal SEO title. Bagus!'),
            $pos !== false => self::c('kw_title', 'ok', 'Keyphrase ada di SEO title, tapi lebih baik di awal.'),
            default        => self::c('kw_title', 'bad', 'Keyphrase belum ada di SEO title.'),
        };

        // Meta description
        $r[] = str_contains(mb_strtolower($desc), $kw)
            ? self::c('kw_meta', 'good', 'Keyphrase ada di meta description. Bagus!')
            : self::c('kw_meta', 'bad', 'Keyphrase belum ada di meta description.');

        // Slug
        $slugWords = explode('-', $slug);
        $hit = 0;
        foreach ($kwWords as $w) {
            $sw = Str::slug($w);
            if ($sw !== '' && in_array($sw, $slugWords, true)) {
                $hit++;
            }
        }
        $r[] = ($hit / $kwCount) > 0.5
            ? self::c('kw_slug', 'good', 'Sebagian besar keyphrase ada di slug. Bagus!')
            : self::c('kw_slug', 'bad', 'Kurang dari separuh keyphrase ada di slug.');

        // Subjudul
        $heads = [];
        foreach ($xp->query('//h2|//h3') as $h) {
            $heads[] = mb_strtolower(trim($h->textContent));
        }
        $withKw = count(array_filter($heads, fn ($h) => str_contains($h, $kw)));
        if (! $heads) {
            $r[] = self::c('kw_sub', 'bad', 'Belum ada subjudul (H2/H3). Tambahkan dan pakai keyphrase di sebagian di antaranya.');
        } elseif ($withKw === 0) {
            $r[] = self::c('kw_sub', 'bad', 'Keyphrase belum ada di subjudul (H2/H3). Pakai di beberapa subjudul.');
        } else {
            $share = $withKw / count($heads);
            $r[] = ($share >= 0.3 && $share <= 0.75)
                ? self::c('kw_sub', 'good', "Keyphrase ada di {$withKw} dari " . count($heads) . ' subjudul. Bagus!')
                : self::c('kw_sub', 'ok', "Keyphrase ada di {$withKw} dari " . count($heads) . ' subjudul. Idealnya 30–75%.');
        }

        // Alt gambar
        $altHit = 0;
        foreach ($imgs as $i) {
            if (str_contains(mb_strtolower($i->getAttribute('alt')), $kw)) {
                $altHit++;
            }
        }
        $r[] = match (true) {
            $imgs->length === 0 => self::c('kw_alt', 'ok', 'Tidak ada gambar di konten. Tambahkan gambar dengan alt berisi keyphrase.'),
            $altHit > 0         => self::c('kw_alt', 'good', 'Ada gambar dengan alt berisi keyphrase. Bagus!'),
            default             => self::c('kw_alt', 'bad', 'Belum ada gambar dengan alt berisi keyphrase.'),
        };

        // Keyphrase unik
        $used = Article::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereRaw('LOWER(focus_keyword) = ?', [$kw])
            ->exists();
        $r[] = $used
            ? self::c('kw_unused', 'bad', 'Keyphrase ini sudah dipakai di artikel lain. Pakai keyphrase berbeda agar tidak saling bersaing.')
            : self::c('kw_unused', 'good', 'Keyphrase belum pernah dipakai di artikel lain. Bagus!');

        return ['score' => self::score($r, 16), 'results' => $r];
    }

    // ---------------------------------------------------------- Keterbacaan

    private static function readability(string $html, string $text, int $words, DOMXPath $xp): array
    {
        if ($words === 0) {
            return ['score' => 0, 'results' => [self::c('empty', 'bad', 'Belum ada teks untuk dianalisis.')]];
        }

        $r         = [];
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [$text];
        $n         = max(count($sentences), 1);

        // Panjang kalimat
        $lens    = array_map(fn ($s) => count(preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY)), $sentences);
        $longPct = count(array_filter($lens, fn ($l) => $l > 20)) / $n * 100;
        $lp      = number_format($longPct, 0);
        $r[] = match (true) {
            $longPct <= 25 => self::c('sent_len', 'good', "{$lp}% kalimat lebih dari 20 kata. Bagus!"),
            $longPct <= 30 => self::c('sent_len', 'ok', "{$lp}% kalimat lebih dari 20 kata. Idealnya maksimal 25%."),
            default        => self::c('sent_len', 'bad', "{$lp}% kalimat lebih dari 20 kata. Pendekkan beberapa kalimat."),
        };

        // Panjang paragraf
        $longParas = 0;
        foreach ($xp->query('//p') as $p) {
            if (count(preg_split('/\s+/u', trim($p->textContent), -1, PREG_SPLIT_NO_EMPTY)) > 150) {
                $longParas++;
            }
        }
        $r[] = $longParas === 0
            ? self::c('para_len', 'good', 'Tidak ada paragraf yang terlalu panjang. Bagus!')
            : self::c('para_len', 'bad', "{$longParas} paragraf lebih dari 150 kata. Pecah menjadi paragraf lebih pendek.");

        // Distribusi subjudul
        if ($words <= 300) {
            $r[] = self::c('sub_dist', 'good', 'Teks cukup pendek, subjudul belum wajib.');
        } else {
            $parts = preg_split('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', (string) $xp->document->saveHTML(), -1);
            $max   = 0;
            foreach ($parts as $part) {
                $pw  = self::plain($part);
                $max = max($max, $pw === '' ? 0 : count(preg_split('/\s+/u', $pw, -1, PREG_SPLIT_NO_EMPTY)));
            }
            $r[] = match (true) {
                count($parts) <= 1 => self::c('sub_dist', 'bad', 'Teks panjang tanpa subjudul. Tambahkan subjudul (H2/H3).'),
                $max > 350         => self::c('sub_dist', 'bad', "Ada bagian {$max} kata tanpa subjudul. Tambahkan subjudul tiap ±300 kata."),
                $max > 300         => self::c('sub_dist', 'ok', "Ada bagian {$max} kata tanpa subjudul. Idealnya maksimal 300."),
                default            => self::c('sub_dist', 'good', 'Subjudul tersebar dengan baik. Bagus!'),
            };
        }

        // Kata transisi
        $pattern = '/\b(?:' . implode('|', array_map(fn ($w) => preg_quote($w, '/'), self::TRANSITIONS)) . ')\b/u';
        $withTr  = count(array_filter($sentences, fn ($s) => preg_match($pattern, mb_strtolower($s))));
        $trPct   = $withTr / $n * 100;
        $tp      = number_format($trPct, 0);
        $r[] = match (true) {
            $trPct >= 30 => self::c('transition', 'good', "{$tp}% kalimat memakai kata transisi. Bagus!"),
            $trPct >= 20 => self::c('transition', 'ok', "{$tp}% kalimat memakai kata transisi. Idealnya minimal 30%."),
            default      => self::c('transition', 'bad', "Hanya {$tp}% kalimat memakai kata transisi (namun, karena, selain itu, dst.). Idealnya minimal 30%."),
        };

        // Kalimat berurutan dengan kata awal sama
        $run = 1;
        $max = 1;
        $prev = null;
        foreach ($sentences as $s) {
            $f = mb_strtolower(preg_split('/\s+/u', trim($s))[0] ?? '');
            $run = ($f !== '' && $f === $prev) ? $run + 1 : 1;
            $max = max($max, $run);
            $prev = $f;
        }
        $r[] = match (true) {
            $max >= 4 => self::c('consec', 'bad', "{$max} kalimat berturut-turut diawali kata yang sama. Variasikan awal kalimat."),
            $max === 3 => self::c('consec', 'ok', '3 kalimat berturut-turut diawali kata yang sama. Variasikan awal kalimat.'),
            default   => self::c('consec', 'good', 'Awal kalimat sudah bervariasi. Bagus!'),
        };

        return ['score' => self::score($r, 5), 'results' => $r];
    }

    // -------------------------------------------------------------- Helper

    private static function c(string $id, string $status, string $text): array
    {
        return ['id' => $id, 'status' => $status, 'text' => $text];
    }

    /** good = 1 poin, ok = 0,5 poin, bad = 0. Pemeriksaan yang tidak jalan dihitung 0. */
    private static function score(array $results, int $total): int
    {
        $pts = 0;
        foreach ($results as $x) {
            $pts += $x['status'] === 'good' ? 1 : ($x['status'] === 'ok' ? 0.5 : 0);
        }

        return (int) round($pts / $total * 100);
    }

    private static function plain(string $html): string
    {
        $html = preg_replace('/<\/(p|h[1-6]|li|div|blockquote|tr|td)>|<br\s*\/?>/i', ' ', $html);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>');
        libxml_clear_errors();

        return new DOMXPath($dom);
    }
}