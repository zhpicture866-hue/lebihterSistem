<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Support\OgImage;
use App\Services\SeoAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:lihat data artikel')->only(['index']);
        $this->middleware('permission:tambah data artikel')->only(['create', 'store']);
        $this->middleware('permission:ubah data artikel')->only(['edit', 'update', 'analyze']);
        $this->middleware('permission:hapus data artikel')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $status  = $request->query('status');
        $orderby = in_array($request->query('orderby'), ['title', 'created_at']) ? $request->query('orderby') : 'created_at';
        $order   = $request->query('order') === 'asc' ? 'asc' : 'desc';

        $query = Article::query()->with(['author', 'categories', 'tags']);

        if ($status === 'trash') {
            $query->onlyTrashed();
        } elseif (in_array($status, ['published', 'draft'])) {
            $query->where('status', $status);
        }

        $query
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->month, fn ($q, $m) => $q
                ->whereYear('created_at', substr($m, 0, 4))
                ->whereMonth('created_at', substr($m, 5, 2)))
            ->when($request->category, fn ($q, $c) => $q
                ->whereHas('categories', fn ($x) => $x->where('categories.id', $c)))
            ->when($request->seo, fn ($q, $v) => $this->scoreFilter($q, 'seo_score', $v))
            ->when($request->readability, fn ($q, $v) => $this->scoreFilter($q, 'readability_score', $v))
            ->when($request->filled('tag'), fn ($q) =>
                $q->whereHas('tags', fn ($t) => $t->where('tags.slug', $request->tag))
            );

        $articles = $query->orderBy($orderby, $order)->paginate(20)->withQueryString();

        $counts = [
            'all'       => Article::count(),
            'published' => Article::where('status', 'published')->count(),
            'draft'     => Article::where('status', 'draft')->count(),
            'trash'     => Article::onlyTrashed()->count(),
        ];

        $months = Article::withTrashed()->orderByDesc('created_at')->pluck('created_at')
            ->map(fn ($d) => $d->format('Y-m'))->unique()->values();

        $categories = Category::orderBy('name')->get();

        return view('admin.articles.index', compact(
            'articles', 'counts', 'months', 'categories', 'status', 'orderby', 'order'
        ));
    }

    public function create()
    {
        return view('admin.articles.form', ['article' => new Article()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['author_id'] = $request->user()->id;
        $data['slug']      = $this->uniqueSlug($data['slug'] ?: $data['title']);
        $data = $this->handleFiles($request, $data);

        $article = Article::create($data);
        $this->afterSave($request, $article);
        return redirect()->route('articles.index')->with('success', 'Artikel disimpan.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validated($request, $article);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['title'], $article->id);
        $data = $this->handleFiles($request, $data, $article);

        $article->update($data);
        $this->afterSave($request, $article);

        return redirect()->route('articles.index')->with('success', 'Artikel diperbarui.');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return back()->with('success', 'Artikel dihapus.');
    }

       private function validated(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255',
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:300',
            'status'           => 'required|in:draft,published',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:170',
            'focus_keyword'    => 'nullable|string|max:100',
            'canonical_url'    => 'nullable|url|max:255',
            'featured_image'   => 'nullable|image|max:2048',
            'og_image'         => 'nullable|image|max:2048',
            'categories'   => 'nullable|array',
            'categories.*' => ['integer', Rule::exists(Category::class, 'id')],
            'tags'         => 'nullable|string|max:500',
            'cta_title'  => 'nullable|string|max:120',
            'cta_text'   => 'nullable|string|max:300',
            'cta_button' => 'required_if:cta_enabled,1|nullable|string|max:60',
            'cta_url'    => ['required_if:cta_enabled,1', 'nullable', 'string', 'max:255', 'regex:~^(https?://|/|tel:|mailto:)~i'],
        ]);

        $data['content'] = \App\Support\VideoEmbed::sanitize($data['content']);          // sanitasi HTML (mews/purifier)
        $data['noindex'] = $request->boolean('noindex');
        $data['cta_enabled'] = $request->boolean('cta_enabled');
        
        if ($data['status'] === 'published') {
            $data['published_at'] = $request->input('published_at') ?: ($article?->published_at ?? now());
        } else {
            $data['published_at'] = null;
        }
        unset($data['categories'], $data['tags']);
        return $data;
    }

    private function handleFiles(Request $request, array $data, ?Article $article = null): array
    {
        foreach (['featured_image', 'og_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($article?->$field) {
                    Storage::disk('public')->delete($article->$field);
                }
                $data[$field] = $request->file($field)->store('articles', 'public');
            } else {
                unset($data[$field]); // pertahankan gambar lama
            }
        }
        return $data;
    }

    private function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text);
        $slug = $base;
        $i = 2;

        while (Article::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function scoreFilter($q, string $col, string $v)
    {
        return match ($v) {
            'good' => $q->where($col, '>=', 70),
            'ok'   => $q->whereBetween($col, [40, 69]),
            'bad'  => $q->where($col, '<', 40),
            'none' => $q->whereNull($col),
            default => $q,
        };
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'bulk_action' => 'required|in:publish,draft,trash,restore,delete',
            'ids'         => 'required|array',
            'ids.*'       => 'integer',
        ]);

        $permission = match ($request->bulk_action) {
            'publish', 'draft' => 'publikasi data artikel',
            'trash', 'restore' => 'hapus data artikel',
            'delete'           => 'hapus permanen data artikel',
        };
        abort_unless($request->user()->can($permission), 403);

        $query = Article::query()->whereIn('id', $request->ids);

        $articles = match ($request->bulk_action) {
            'restore', 'delete' => $query->onlyTrashed()->get(),
            default             => $query->get(),
        };

        foreach ($articles as $a) {
            match ($request->bulk_action) {
                'publish' => $a->update(['status' => 'published', 'published_at' => $a->published_at ?? now()]),
                'draft'   => $a->update(['status' => 'draft']),
                'trash'   => $a->delete(),
                'restore' => $a->restore(),
                'delete'  => $a->forceDelete(),
            };
        }

        return back()->with('success', $articles->count() . ' artikel diproses.');
    }

    private function afterSave(Request $request, Article $article): void
    {
        $article->categories()->sync($request->input('categories', []));

        $tagIds = collect(explode(',', (string) $request->input('tags')))
            ->map(fn ($t) => trim($t))->filter()
            ->map(fn ($name) => [$name, Str::slug($name)])
            ->filter(fn ($p) => $p[1] !== '')
            ->unique(fn ($p) => $p[1])
            ->map(fn ($p) => Tag::firstOrCreate(['slug' => $p[1]], ['name' => $p[0]])->id)
            ->values();

        $article->tags()->sync($tagIds);
        $regenerate = $request->hasFile('featured_image') || $request->hasFile('og_image') || ! $article->og_auto;
        if ($regenerate) {
            $source = $article->og_image ?: $article->featured_image;
            $article->update(['og_auto' => $source ? OgImage::make($source, $article->og_auto) : null]);
        }
        $article->update(SeoAnalyzer::scores($article->fresh()));
    }

    public function analyze(Request $request)
    {
        $article = new Article([
            'title'            => (string) $request->input('title'),
            'slug'             => $request->filled('slug') ? $request->input('slug') : Str::slug((string) $request->input('title')),
            'content'          => (string) $request->input('content'),
            'excerpt'          => $request->input('excerpt'),
            'meta_title'       => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'focus_keyword'    => $request->input('focus_keyword'),
        ]);

        return response()->json(SeoAnalyzer::analyze(
            $article,
            $request->boolean('has_featured'),
            $request->integer('article_id') ?: null,
        ));
    }
}