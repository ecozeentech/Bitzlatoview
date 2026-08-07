<x-guest-layout>
    <h1 class="mb-1 text-xl font-bold">Log in to Bitzlatoview</h1>
    <p class="mb-6 text-sm text-text-muted">Welcome back. Enter your credentials to access your dashboard.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="input-field" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-password-input id="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center text-sm text-text-muted">
                <input type="checkbox" name="remember" class="rounded border-border bg-surface-2 text-brand focus:ring-brand">
                <span class="ms-2">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-brand hover:underline" href="{{ route('password.request') }}">Forgot your password?</a>
            @endif
        </div>

        <button type="submit" class="btn-brand w-full">Log In</button>

        <p class="text-center text-sm text-text-muted">Don't have an account? <a href="{{ route('register') }}" class="text-brand hover:underline">Sign up</a></p>
    </form>
</x-guest-layout>
