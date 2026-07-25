@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold">Profile</h1>

    <div class="glass-card p-6">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="glass-card p-6">
        @include('profile.partials.update-password-form')
    </div>

    <div class="glass-card p-6">
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection
