<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $orderby = in_array($request->query('orderby'), ['name', 'articles_count']) ? $request->query('orderby') : 'name';
        $order   = $request->query('order') === 'desc' ? 'desc' : 'asc';

        $tags = Tag::withCount('articles')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy($orderby, $order)
            ->paginate(20)
            ->withQueryString();

        return view('admin.tags.index', compact('tags', 'orderby', 'order'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name',
            'slug' => 'nullable|string|max:100|unique:tags,slug',
        ]);

        Tag::create($data);

        return redirect()->route('tags.index')->with('success', 'Tag ditambahkan.');
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name,' . $tag->id,
            'slug' => 'nullable|string|max:100|unique:tags,slug,' . $tag->id,
        ]);

        $tag->update($data);

        return redirect()->route('tags.index')->with('success', 'Tag diperbarui.');
    }

    public function destroy(Tag $tag)
    {
        $tag->articles()->detach();
        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Tag dihapus.');
    }

    public function bulk(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        if ($request->input('bulk_action') === 'delete' && $ids) {
            foreach (Tag::whereIn('id', $ids)->get() as $tag) {
                $tag->articles()->detach();
                $tag->delete();
            }
            return redirect()->route('tags.index')->with('success', count($ids) . ' tag dihapus.');
        }

        return redirect()->route('tags.index');
    }
}