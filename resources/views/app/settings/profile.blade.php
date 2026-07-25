@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Profile Settings</h1>

    <div class="glass-card p-6">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-4 text-lg font-medium">Personal Details</h2>
        <form method="POST" action="{{ route('app.settings.profile.update') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <div><label class="label-field">Phone</label><input type="text" name="phone" class="input-field" value="{{ old('phone', auth()->user()->phone) }}"></div>
            <div><label class="label-field">Country</label><input type="text" name="country" class="input-field" value="{{ old('country', auth()->user()->country) }}" required></div>
            <div><label class="label-field">City</label><input type="text" name="city" class="input-field" value="{{ old('city', auth()->user()->city) }}"></div>
            <div><label class="label-field">Name</label><input type="text" name="name" class="input-field" value="{{ old('name', auth()->user()->name) }}" required></div>
            <button class="btn-brand sm:col-span-2">Save Details</button>
        </form>
    </div>

    <div class="glass-card p-6">
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection
