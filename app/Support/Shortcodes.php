<?php

namespace App\Support;

class Shortcodes
{
    /** Paragraf yang hanya berisi [cta ...] */
    private const CTA = '~<p>\s*(\[cta\b[^\]]*\])\s*</p>~is';

    /** Dipakai saat menampilkan artikel di halaman publik. */
    public static function render(string $html): string
    {
        return preg_replace_callback(self::CTA, function ($m) {
            $code = html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $code = str_replace("\xC2\xA0", ' ', $code);

            preg_match_all('/(\w+)\s*=\s*["“”]([^"“”]*)["“”]/u', $code, $found, PREG_SET_ORDER);

            $v = [];
            foreach ($found as $x) {
                $v[strtolower($x[1])] = trim($x[2]);
            }

            return self::cta($v);
        }, $html);
    }

    /** Buang shortcode (untuk ringkasan, meta description otomatis, dan analisis SEO). */
    public static function strip(string $html): string
    {
        return preg_replace(self::CTA, '', $html);
    }

    private static function cta(array $v): string
    {
        $judul  = trim($v['judul'] ?? '');
        $teks   = trim($v['teks'] ?? '');
        $tombol = trim($v['tombol'] ?? '') ?: 'Hubungi Kami';
        $url    = trim($v['url'] ?? '');

        if ($judul === '' && $teks === '') {
            return '';
        }

        if (! preg_match('~^(https?://|/|tel:|mailto:)~i', $url)) {
            $url = '#';
        }
        $target = preg_match('~^https?://~i', $url) ? ' target="_blank" rel="noopener noreferrer"' : '';

        return '<div class="cta-box my-4 p-4 rounded-3 border bg-light d-flex flex-column flex-md-row'
            . ' align-items-md-center justify-content-between gap-3">'
            . '<div>'
            . ($judul !== '' ? '<div class="h4 fw-bold mb-1">' . e($judul) . '</div>' : '')
            . ($teks !== '' ? '<p class="mb-0 text-muted">' . e($teks) . '</p>' : '')
            . '</div>'
            . '<a href="' . e($url) . '"' . $target . ' class="btn btn-primary btn-lg text-uppercase flex-shrink-0">'
            . e($tombol) . '</a>'
            . '</div>';
    }
}