<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use SoftDeletes;
    protected $table = 'lebihtersistem.articles';
    
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'featured_image', 'author_id',
        'status', 'published_at', 'meta_title', 'meta_description',
        'focus_keyword', 'canonical_url', 'noindex', 'og_image', 'seo_score', 'readability_score', 'views',
        'cta_enabled', 'cta_title', 'cta_text', 'cta_button', 'cta_url', 'og_auto'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'noindex'      => 'boolean',
        'cta_enabled'   => 'boolean'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')
                 ->where('published_at', '<=', now());
    }

        public function categories()
    {
        return $this->belongsToMany(Category::class, 'lebihtersistem.article_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'lebihtersistem.article_tag');
    }

public function renderedContent(): string
{
    return \App\Support\Shortcodes::render(
        \App\Support\VideoEmbed::render((string) $this->content)
    );
}

protected static function booted(): void
{
    static::forceDeleting(function (Article $article) {
        foreach (['featured_image', 'og_image', 'og_auto'] as $field) {
            if ($article->$field) {
                Storage::disk('public')->delete($article->$field);
            }
        }
    });
}
}