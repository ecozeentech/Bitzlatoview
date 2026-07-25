<x-guest-layout>
    <div class="mb-6 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand text-sm font-extrabold text-background">BZ</span>
            <span class="text-lg font-extrabold text-slate-50">Bitzlatoview</span>
        </a>
        <p class="mt-2 text-sm text-muted">Create your trading account</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="full_legal_name" value="Full legal name" />
            <x-text-input id="full_legal_name" class="block mt-1 w-full" type="text" name="full_legal_name" :value="old('full_legal_name')" required />
            <x-input-error :messages="$errors->get('full_legal_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="phone" value="Phone" />
                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required />
            </div>
            <div>
                <x-input-label for="country" value="Country (ISO)" />
                <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country','US')" maxlength="2" required />
            </div>
        </div>
        <div>
            <x-input-label for="city" value="City" />
            <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" required />
        </div>
        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
        </div>
        <div>
            <x-input-label for="referral_code" value="Referral code (optional)" />
            <x-text-input id="referral_code" class="block mt-1 w-full" type="text" name="referral_code" :value="old('referral_code')" />
        </div>
        <label class="flex items-start gap-2 text-sm text-muted"><input type="checkbox" name="terms" value="1" required class="mt-1 rounded border-border bg-surface-2 text-brand"> Accept Terms</label>
        <label class="flex items-start gap-2 text-sm text-muted"><input type="checkbox" name="privacy" value="1" required class="mt-1 rounded border-border bg-surface-2 text-brand"> Accept Privacy Policy</label>
        <label class="flex items-start gap-2 text-sm text-muted"><input type="checkbox" name="risk" value="1" required class="mt-1 rounded border-border bg-surface-2 text-brand"> Accept Risk Disclosure</label>
        <x-primary-button class="w-full justify-center">Register</x-primary-button>
    </form>
</x-guest-layout>
