<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $table = 'lebihtersistem.categories';
    protected $fillable = ['name', 'slug', 'parent_id'];
        protected static function booted()
    {
        static::saving(function ($category) {
            $base = Str::slug($category->slug ?: $category->name) ?: 'kategori';
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->where('id', '!=', $category->id ?? 0)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $category->slug = $slug;
        });
    }
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'lebihtersistem.article_category');
    }

    public function parent()
{
    return $this->belongsTo(self::class, 'parent_id');
}

public function children()
{
    return $this->hasMany(self::class, 'parent_id');
}

/** Semua kategori berurutan sebagai pohon; tiap item punya ->depth (0 = kategori utama). */
public static function flatTree(
    ?\Illuminate\Support\Collection $items = null,
    string $orderby = 'name',
    string $order = 'asc'
): \Illuminate\Support\Collection {
    $items ??= static::orderBy('name')->get();
    $ids = $items->pluck('id')->flip();

    $sorted = $items->sortBy(
        fn ($c) => $orderby === 'articles_count'
            ? (int) $c->articles_count
            : mb_strtolower($c->name),
        SORT_REGULAR,
        $order === 'desc'
    )->values();

    // Induk yang tidak ada di daftar dianggap kategori utama
    $byParent = $sorted->groupBy(fn ($c) => $ids->has($c->parent_id) ? $c->parent_id : 0);
    $out = collect();

    $walk = function ($parentId, $depth) use (&$walk, $byParent, &$out) {
        foreach ($byParent->get($parentId, collect()) as $c) {
            $c->depth = $depth;
            $out->push($c);
            $walk($c->id, $depth + 1);
        }
    };
    $walk(0, 0);

    return $out;
}

public function descendantIds(): array
{
    $ids = [];
    $queue = [$this->id];
    while ($queue) {
        $children = static::whereIn('parent_id', $queue)->pluck('id')->all();
        $ids = array_merge($ids, $children);
        $queue = $children;
    }
    return $ids;
}
}