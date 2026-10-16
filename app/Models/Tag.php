<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $table = 'lebihtersistem.tags';
    protected $fillable = ['name', 'slug'];

    protected static function booted()
    {
        static::saving(function ($tag) {
            $base = Str::slug($tag->slug ?: $tag->name) ?: 'tag';
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->where('id', '!=', $tag->id ?? 0)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $tag->slug = $slug;
        });
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'lebihtersistem.article_tag');
    }
}