<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\FaqItem;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    public function blog()
    {
        $posts = BlogPost::latest()->get();

        return view('admin.blog.index', compact('posts'));
    }

    public function storeBlog(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:draft,published'],
        ]);

        BlogPost::create($data + [
            'slug' => Str::slug($data['title']).'-'.Str::random(5),
            'author' => $data['author'] ?? 'Bitzlatoview Team',
            'published_at' => $data['status'] === 'published' ? now() : null,
        ]);

        return back()->with('success', 'Blog post created.');
    }

    public function updateBlog(Request $request, BlogPost $post)
    {
        $data = $request->validate(['status' => ['required', 'in:draft,published']]);
        $post->update($data + ['published_at' => $data['status'] === 'published' ? ($post->published_at ?? now()) : null]);

        return back()->with('success', 'Post updated.');
    }

    public function destroyBlog(BlogPost $post)
    {
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

    public function news()
    {
        $articles = NewsArticle::latest()->get();

        return view('admin.news.index', compact('articles'));
    }

    public function storeNews(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'sentiment' => ['required', 'in:bullish,neutral,bearish'],
            'source' => ['nullable', 'string', 'max:150'],
        ]);

        NewsArticle::create($data + [
            'slug' => Str::slug($data['title']).'-'.Str::random(5),
            'source' => $data['source'] ?? 'Bitzlatoview Newsroom',
            'published_at' => now(),
        ]);

        return back()->with('success', 'News article published.');
    }

    public function destroyNews(NewsArticle $article)
    {
        $article->delete();

        return back()->with('success', 'Article deleted.');
    }

    public function faq()
    {
        $faqs = FaqItem::orderBy('category')->orderBy('sort_order')->get();

        return view('admin.faq.index', compact('faqs'));
    }

    public function storeFaq(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        FaqItem::create($data);

        return back()->with('success', 'FAQ added.');
    }

    public function destroyFaq(FaqItem $faq)
    {
        $faq->delete();

        return back()->with('success', 'FAQ removed.');
    }

    public function pages()
    {
        $pages = CmsPage::all();

        return view('admin.cms.index', compact('pages'));
    }

    public function storePage(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:150', 'unique:cms_pages,slug'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        CmsPage::create($data + ['status' => 'published']);

        return back()->with('success', 'Page created.');
    }
}
