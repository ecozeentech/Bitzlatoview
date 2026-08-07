<x-guest-layout>
    <h1 class="mb-1 text-xl font-bold">Create your Bitzlatoview account</h1>
    <p class="mb-6 text-sm text-text-muted">Trade crypto, stocks, forex, futures and NFTs from one dashboard.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full legal name')" class="label-field" />
            <x-text-input id="name" class="input-field" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="label-field" />
            <x-text-input id="email" class="input-field" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="phone" :value="__('Phone')" class="label-field" />
                <x-text-input id="phone" class="input-field" type="text" name="phone" :value="old('phone')" />
            </div>
            <div>
                <x-input-label for="country" :value="__('Country')" class="label-field" />
                <x-text-input id="country" class="input-field" type="text" name="country" :value="old('country')" required />
            </div>
        </div>

        <div>
            <x-input-label for="city" :value="__('City')" class="label-field" />
            <x-text-input id="city" class="input-field" type="text" name="city" :value="old('city')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="label-field" />
            <x-password-input id="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="label-field" />
            <x-password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div>
            <x-input-label for="referral_code" :value="__('Referral code (optional)')" class="label-field" />
            <x-text-input id="referral_code" class="input-field" type="text" name="referral_code" :value="old('referral_code')" />
        </div>

        <div class="space-y-2 text-sm text-text-muted">
            <label class="flex items-start gap-2">
                <input type="checkbox" name="terms" value="1" class="mt-0.5 rounded border-border bg-surface-2 text-brand focus:ring-brand" required>
                I accept the <a href="/terms" target="_blank" class="text-brand hover:underline">Terms of Service</a>
            </label>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="privacy" value="1" class="mt-0.5 rounded border-border bg-surface-2 text-brand focus:ring-brand" required>
                I accept the <a href="/privacy" target="_blank" class="text-brand hover:underline">Privacy Policy</a>
            </label>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="risk" value="1" class="mt-0.5 rounded border-border bg-surface-2 text-brand focus:ring-brand" required>
                I acknowledge the <a href="/risk-disclosure" target="_blank" class="text-brand hover:underline">Risk Disclosure</a> — trading involves risk of loss.
            </label>
        </div>

        <button type="submit" class="btn-brand w-full">Create Account</button>

        <p class="text-center text-sm text-text-muted">Already registered? <a href="{{ route('login') }}" class="text-brand hover:underline">Log in</a></p>
    </form>
</x-guest-layout>
