@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Contact Us</h1>
    <p class="mt-2 text-text-muted">Have a question about Bitzlatoview? Send us a message and our support team will get back to you.</p>

    <form method="POST" action="{{ route('contact.store') }}" class="glass-card mt-8 space-y-4 p-6">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label-field">Name</label>
                <input name="name" class="input-field" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="label-field">Email</label>
                <input type="email" name="email" class="input-field" value="{{ old('email') }}" required>
            </div>
        </div>
        <div>
            <label class="label-field">Subject</label>
            <input name="subject" class="input-field" value="{{ old('subject') }}">
        </div>
        <div>
            <label class="label-field">Message</label>
            <textarea name="message" rows="5" class="input-field" required>{{ old('message') }}</textarea>
        </div>
        <button type="submit" class="btn-brand w-full">Send Message</button>
    </form>
</div>
@endsection
