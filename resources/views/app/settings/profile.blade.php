@extends('layouts.app-shell')
@section('title', 'Profile')
@section('content')
<h1 class="text-2xl font-bold mb-6">Profile</h1><form method="POST" action="{{ route('app.settings.profile.update') }}" class="glass-card max-w-xl space-y-3 p-6">@csrf @method('PATCH')<input name="name" class="input-field" value="{{ auth()->user()->name }}" required><input name="phone" class="input-field" value="{{ auth()->user()->phone }}" placeholder="Phone"><input name="country" class="input-field" value="{{ auth()->user()->country }}" maxlength="2" placeholder="Country"><input name="city" class="input-field" value="{{ auth()->user()->city }}" placeholder="City"><textarea name="bio" class="input-field" placeholder="Bio">{{ $profile->bio }}</textarea><button class="btn-brand">Save</button></form>
@endsection
