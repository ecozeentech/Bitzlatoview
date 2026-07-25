@extends('layouts.public')
@section('title', 'FAQ')
@section('content')

<div class="page-shell py-16">
<h1 class="section-title">FAQ</h1>
<div class="mt-8 space-y-3">
@foreach($faqs as $faq)
<div class="glass-card p-5"><h3 class="font-semibold">{{ $faq->question }}</h3><p class="mt-2 text-sm text-muted">{{ $faq->answer }}</p></div>
@endforeach
</div></div>
@endsection
