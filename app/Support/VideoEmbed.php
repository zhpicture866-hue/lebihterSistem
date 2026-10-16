<?php

namespace App\Support;

class VideoEmbed
{
/** Blok video + (opsional) paragraf keterangan miring atau paragraf kosong tepat di bawahnya */
    private const OEMBED_CAPTION = '~<figure class="media">\s*<oembed url="([^"]*)"></oembed>\s*</figure>'
        . '(?:\s*<p>\s*<(?:i|em)>((?:[^<]|<(?!/?(?:i|em)\b))*)</(?:i|em)>\s*</p>'
        . '|\s*<p>(?:&nbsp;|\s)*</p>)?~i';

    /** Paragraf yang hanya berisi satu link (boleh dibungkus <a>) */
    private const PARA_URL = '~<p>\s*(?:<a [^>]*>)?\s*(https?://[^\s<"]+)\s*(?:</a>)?\s*</p>~i';

    public static function youtubeId(string $url): ?string
    {
        if (preg_match(
            '~^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})(?:[&?#/].*)?$~i',
            trim($url),
            $m
        )) {
            return $m[1];
        }

        return null;
    }

    /** Ganti purifier-friendly: dipakai saat menyimpan artikel (pengganti clean()). */
    public static function sanitize(string $html): string
    {
        // 1. Paragraf berisi link YouTube -> blok video
        $html = self::embedPlainLinks($html);

        // 2. Lindungi blok video dari purifier dengan token
        $ids  = [];
        $html = preg_replace_callback(self::OEMBED_CAPTION, function ($m) use (&$ids) {
            $id = self::youtubeId(html_entity_decode($m[1]));
            if (! $id) {
                return '';
            }
            $ids[] = $id;

            return '<p>[[VIDEO:' . (count($ids) - 1) . ']]</p>';
        }, $html);

        // 3. Sanitasi HTML (mews/purifier)
        $html = clean($html);

        // 4. Kembalikan blok video dengan URL kanonik dari ID yang sudah divalidasi
        return preg_replace_callback('~<p>\s*\[\[VIDEO:(\d+)\]\]\s*</p>~', function ($m) use ($ids) {
            $id = $ids[(int) $m[1]] ?? null;

            return $id ? self::figure($id) : '';
        }, $html);
    }

    /** Dipakai saat menampilkan artikel di halaman publik. */
public static function render(string $html): string
{
    $html = self::embedPlainLinks($html);

    return preg_replace_callback(self::OEMBED_CAPTION, function ($m) {
        $id = self::youtubeId(html_entity_decode($m[1]));
        if (! $id) {
            return '';
        }

        $caption = $m[2] ?? '';
        $plain   = trim(html_entity_decode(strip_tags($caption)), " \t\n\r\0\x0B\xC2\xA0");

        return self::iframe($id, $plain === '' ? '' : $caption);
    }, $html);
}

    public static function embedPlainLinks(string $html): string
    {
        return preg_replace_callback(self::PARA_URL, function ($m) {
            $id = self::youtubeId(html_entity_decode($m[1]));

            return $id ? self::figure($id) : $m[0];
        }, $html);
    }

    private static function figure(string $id): string
    {
        return '<figure class="media"><oembed url="https://www.youtube.com/watch?v=' . $id . '"></oembed></figure>';
    }

private static function iframe(string $id, string $caption = ''): string
{
    return '<figure class="video-embed my-4">'
        . '<div class="ratio ratio-16x9"><iframe'
        . ' src="https://www.youtube-nocookie.com/embed/' . $id . '"'
        . ' title="Video YouTube" loading="lazy" allowfullscreen'
        . ' referrerpolicy="strict-origin-when-cross-origin"></iframe></div>'
        . ($caption !== '' ? '<figcaption class="text-center text-muted small mt-2">' . $caption . '</figcaption>' : '')
        . '</figure>';
}
}