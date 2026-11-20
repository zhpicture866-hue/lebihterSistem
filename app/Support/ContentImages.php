<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

class ContentImages
{
    /** Hanya gambar hasil upload sendiri yang dipercaya. */
    private const ALLOWED = '~^/storage/articles/(content|web)/[A-Za-z0-9._-]+\.(jpe?g|png|webp|gif)$~i';

    /** @return array{0:string,1:array} [html dengan token, daftar gambar] */
    public static function protect(string $html): array
    {
        if (stripos($html, '<img') === false) {
            return [$html, []];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="root">' . $html . '</div>');
        libxml_clear_errors();
        $xp = new DOMXPath($dom);

        $items = [];

        // Gambar blok: <figure class="image ..."> <img> <figcaption>
        foreach (iterator_to_array($xp->query('//figure[contains(concat(" ", normalize-space(@class), " "), " image ")]')) as $fig) {
            $img  = $xp->query('.//img', $fig)->item(0);
            $item = $img instanceof DOMElement ? self::describe($img) : null;
            if (! $item) {
                continue;   // bukan gambar hasil upload kita: biarkan ke purifier
            }

            $cap = $xp->query('.//figcaption', $fig)->item(0);
            $item['caption'] = $cap ? mb_substr(trim(preg_replace('/\s+/u', ' ', $cap->textContent)), 0, 300) : '';
            $item['side']    = str_contains(' ' . $fig->getAttribute('class') . ' ', ' image-style-side ');

            $items[] = $item;
            $fig->parentNode->replaceChild(
                $dom->createElement('p', '[[IMG:' . (count($items) - 1) . ']]'),
                $fig
            );
        }

        // Gambar sebaris (di dalam paragraf)
        foreach (iterator_to_array($xp->query('//img')) as $img) {
            $item = self::describe($img);
            if (! $item) {
                continue;
            }
            $items[] = $item;
            $img->parentNode->replaceChild($dom->createTextNode('[[IMGI:' . (count($items) - 1) . ']]'), $img);
        }

        $root = $xp->query('//div[@id="root"]')->item(0);
        $out  = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return [$out, $items];
    }

    /** Kembalikan gambar dengan markup kanonik dari data yang sudah divalidasi. */
    public static function restore(string $html, array $items): string
    {
        if (! $items) {
            return $html;
        }

        $html = preg_replace_callback('~<p>\s*\[\[IMG:(\d+)\]\]\s*</p>~', function ($m) use ($items) {
            $i = $items[(int) $m[1]] ?? null;

            return $i ? self::figure($i) : '';
        }, $html);

        return preg_replace_callback('~\[\[IMGI:(\d+)\]\]~', function ($m) use ($items) {
            $i = $items[(int) $m[1]] ?? null;

            return $i ? self::img($i) : '';
        }, $html);
    }

    private static function describe(DOMElement $img): ?array
    {
        $path = (string) parse_url(trim($img->getAttribute('src')), PHP_URL_PATH);
        if (! preg_match(self::ALLOWED, $path)) {
            return null;
        }

        $w = (int) $img->getAttribute('width');
        $h = (int) $img->getAttribute('height');

        return [
            'src' => $path,   // hanya path: host apa pun dibuang, jadi selalu relatif
            'alt' => mb_substr(trim(strip_tags($img->getAttribute('alt'))), 0, 200),
            'w'   => ($w > 0 && $w <= 6000) ? $w : null,
            'h'   => ($h > 0 && $h <= 6000) ? $h : null,
        ];
    }

    private static function img(array $i): string
    {
        $size = ($i['w'] && $i['h']) ? ' width="' . $i['w'] . '" height="' . $i['h'] . '"' : '';

        return '<img src="' . e($i['src']) . '" alt="' . e($i['alt']) . '"' . $size . '>';
    }

    private static function figure(array $i): string
    {
        return '<figure class="image' . (! empty($i['side']) ? ' image-style-side' : '') . '">'
            . self::img($i)
            . (! empty($i['caption']) ? '<figcaption>' . e($i['caption']) . '</figcaption>' : '')
            . '</figure>';
    }
}