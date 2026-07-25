@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Email Templates</h1>
    <div class="risk-banner">Provider adapter: swap MAIL_MAILER in .env between log/smtp/resend/sendgrid/postmark. Currently using the log driver by default (no real emails sent).</div>

    <div class="space-y-3">
        @foreach ($templates as $template)
            <details class="glass-card p-4">
                <summary class="cursor-pointer font-medium">{{ $template->name }} <span class="pill-{{ $template->is_active ? 'success' : 'muted' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></summary>
                <form method="POST" action="{{ route('admin.email.templates.update', $template) }}" class="mt-3 space-y-2">
                    @csrf @method('PATCH')
                    <input type="text" name="subject" class="input-field" value="{{ $template->subject }}" required>
                    <textarea name="body_html" class="input-field font-numeric" rows="4" required>{{ $template->body_html }}</textarea>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($template->is_active)> Active</label>
                    <button class="btn-brand text-sm">Save Template</button>
                </form>
            </details>
        @endforeach
    </div>
</div>
@endsection
