@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Blog</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">New Post</h2>
        <form method="POST" action="{{ route('admin.blog.store') }}" class="grid gap-3">
            @csrf
            <input type="text" name="title" class="input-field" placeholder="Title" required>
            <input type="text" name="excerpt" class="input-field" placeholder="Excerpt">
            <textarea name="content" class="input-field" rows="5" placeholder="Content" required></textarea>
            <div class="grid gap-3 sm:grid-cols-3">
                <input type="text" name="category" class="input-field" placeholder="Category">
                <input type="text" name="author" class="input-field" placeholder="Author">
                <select name="status" class="input-field"><option value="draft">Draft</option><option value="published">Published</option></select>
            </div>
            <button class="btn-brand">Save Post</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Category</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td class="text-text-muted">{{ $post->category }}</td>
                        <td><span class="pill-{{ $post->status === 'published' ? 'success' : 'muted' }}">{{ $post->status }}</span></td>
                        <td class="space-x-2">
                            <form method="POST" action="{{ route('admin.blog.update', $post) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $post->status === 'published' ? 'draft' : 'published' }}">
                                <button class="text-xs text-brand hover:underline">{{ $post->status === 'published' ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" class="inline">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
