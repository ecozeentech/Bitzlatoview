@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">FAQ</h1>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.faq.store') }}" class="grid gap-3">
            @csrf
            <input type="text" name="question" class="input-field" placeholder="Question" required>
            <textarea name="answer" class="input-field" rows="3" placeholder="Answer" required></textarea>
            <input type="text" name="category" class="input-field" placeholder="Category" required>
            <button class="btn-brand">Add FAQ</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Question</th><th>Category</th><th></th></tr></thead>
            <tbody>
                @foreach ($faqs as $faq)
                    <tr>
                        <td>{{ $faq->question }}</td>
                        <td class="text-text-muted">{{ $faq->category }}</td>
                        <td><form method="POST" action="{{ route('admin.faq.destroy', $faq) }}">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
