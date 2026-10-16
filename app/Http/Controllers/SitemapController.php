<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PropertiDijual;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [
            ['loc' => url('/'), 'lastmod' => null],
            ['loc' => route('artikel.index'), 'lastmod' => null],
        ];

        $articles = Article::published()->where('noindex', false)->latest('updated_at')->get();
        foreach ($articles as $a) {
            $urls[] = ['loc' => route('articles.show', $a), 'lastmod' => $a->updated_at->toAtomString()];
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . e($u['loc']) . "</loc>\n";
            if ($u['lastmod']) {
                $xml .= '    <lastmod>' . e($u['lastmod']) . "</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}