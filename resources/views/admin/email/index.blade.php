@extends('layouts.admin')
@section('title', 'Email Center')
@section('content')

<div class="grid gap-6 lg:grid-cols-3">
<form method="POST" action="{{ route('admin.email.templates.store') }}" class="glass-card space-y-3 p-5">@csrf
<h3 class="font-semibold">Template</h3>
<input name="key" class="input-field" placeholder="key" required>
<input name="name" class="input-field" placeholder="Name" required>
<input name="subject" class="input-field" placeholder="Subject" required>
<select name="category" class="input-field"><option>transactional</option><option>marketing</option></select>
<textarea name="body_html" class="input-field" rows="5" required></textarea>
<button class="btn-brand">Save template</button>
</form>
<form method="POST" action="{{ route('admin.email.campaigns.store') }}" class="glass-card space-y-3 p-5">@csrf
<h3 class="font-semibold">Campaign</h3>
<input name="name" class="input-field" required>
<input name="subject" class="input-field" required>
<input name="audience_segment" class="input-field" value="all_users">
<textarea name="body_html" class="input-field" rows="5" required></textarea>
<button class="btn-brand">Draft campaign</button>
</form>
<form method="POST" action="{{ route('admin.email.send-test') }}" class="glass-card space-y-3 p-5">@csrf
<h3 class="font-semibold">Send test</h3>
<input name="template" class="input-field" value="welcome" required>
<input name="email" type="email" class="input-field" required>
<button class="btn-outline">Send test</button>
</form>
</div>
<div class="mt-6 grid gap-4 lg:grid-cols-2">
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Templates</h3>@foreach($templates as $t)<div class="text-sm border-b border-border/40 py-2">{{ $t->key }} · {{ $t->subject }}</div>@endforeach</div>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Logs</h3>@foreach($logs as $l)<div class="text-xs border-b border-border/40 py-2">{{ $l->recipient }} · {{ $l->status }} · {{ $l->subject }}</div>@endforeach</div>
</div>
<form method="POST" action="{{ route('admin.news.store') }}" class="glass-card mt-6 grid gap-3 p-5 md:grid-cols-2">@csrf
<h3 class="font-semibold md:col-span-2">Quick publish news</h3>
<input name="title" class="input-field" required>
<select name="sentiment" class="input-field"><option>neutral</option><option>bullish</option><option>bearish</option></select>
<input name="source" class="input-field">
<textarea name="summary" class="input-field md:col-span-2"></textarea>
<button class="btn-brand md:col-span-2">Publish news</button>
</form>

@endsection
