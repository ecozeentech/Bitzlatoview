<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::where('status', 'published')->latest('published_at')->paginate(9);

        return view('public.blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $related = BlogPost::where('status', 'published')->where('id', '!=', $post->id)->take(3)->get();

        return view('public.blog.show', compact('post', 'related'));
    }
}
