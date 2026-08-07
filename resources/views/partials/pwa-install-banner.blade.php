<div
    x-data="{
        show: false,
        iosHint: false,
        init() {
            const dismissedAt = localStorage.getItem('pwa-install-dismissed-at');
            if (dismissedAt && (Date.now() - Number(dismissedAt)) < 1000 * 60 * 60 * 24 * 14) {
                return; // dismissed within the last 14 days
            }
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                return; // already installed / running as an installed app
            }

            const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent) && !window.MSStream;
            if (isIos) {
                this.iosHint = true;
                this.show = true;
                return;
            }

            if (window.deferredInstallPrompt) {
                this.show = true;
            }
            window.addEventListener('pwa-installable', () => { this.show = true; });
            window.addEventListener('pwa-installed', () => { this.show = false; });
        },
        async install() {
            if (!window.deferredInstallPrompt) { return; }
            window.deferredInstallPrompt.prompt();
            await window.deferredInstallPrompt.userChoice;
            window.deferredInstallPrompt = null;
            this.show = false;
        },
        dismiss() {
            this.show = false;
            localStorage.setItem('pwa-install-dismissed-at', String(Date.now()));
        },
    }"
    x-show="show"
    x-cloak
    x-transition
    class="mx-4 mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand/30 bg-brand/5 px-4 py-3 text-sm lg:mx-8"
>
    <div class="flex items-center gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-gradient font-extrabold text-background">B</span>
        <span x-show="!iosHint">Install Bitzlatoview for quick access and a full-screen app experience.</span>
        <span x-show="iosHint" x-cloak>Install Bitzlatoview: tap the Share icon, then "Add to Home Screen".</span>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" x-show="!iosHint" @click="install" class="btn-brand text-xs">Install</button>
        <button type="button" @click="dismiss" class="btn-ghost text-xs">Not now</button>
    </div>
</div>
