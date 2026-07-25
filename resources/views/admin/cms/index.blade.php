@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">CMS Pages</h1>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.cms.store') }}" class="grid gap-3">
            @csrf
            <input type="text" name="slug" class="input-field" placeholder="Slug (e.g. custom-page)" required>
            <input type="text" name="title" class="input-field" placeholder="Title" required>
            <textarea name="content" class="input-field" rows="5" placeholder="Content (HTML/Markdown)" required></textarea>
            <button class="btn-brand">Create Page</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Slug</th><th>Title</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($pages as $p)
                    <tr><td>{{ $p->slug }}</td><td>{{ $p->title }}</td><td><span class="pill-success">{{ $p->status }}</span></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
