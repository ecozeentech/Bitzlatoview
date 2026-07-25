@extends('layouts.public')
@section('title', 'Article')
@section('content')

<div class="page-shell py-16 max-w-3xl"><a href="{{ route('blog') }}" class="text-sm text-brand">← Blog</a>
<h1 class="section-title mt-4">{{ $post->title }}</h1>
<p class="mt-2 text-sm text-muted">{{ optional($post->published_at)->toDayDateTimeString() }}</p>
<div class="glass-card prose prose-invert mt-8 max-w-none p-6 text-sm text-slate-200">{!! nl2br(e($post->content)) !!}</div></div>
@endsection
