@extends('layouts.public')
@section('title', 'Contact')
@section('content')

<div class="page-shell py-16 max-w-2xl">
<h1 class="section-title">Contact</h1>
<p class="section-sub">Reach the Bitzlatoview team.</p>
<form method="POST" action="{{ route('contact.submit') }}" class="glass-card mt-8 space-y-4 p-6">@csrf
<label class="label-field">Name<input class="input-field" name="name" required></label>
<label class="label-field">Email<input class="input-field" type="email" name="email" required></label>
<label class="label-field">Subject<input class="input-field" name="subject"></label>
<label class="label-field">Message<textarea class="input-field" name="message" rows="5" required></textarea></label>
<button class="btn-brand">Send message</button>
</form></div>
@endsection
