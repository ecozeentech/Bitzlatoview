

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Register the PWA service worker. Only runs over HTTPS (or localhost, for local dev) since
// service workers require a secure context — browsers simply refuse registration otherwise,
// so this is safe to call unconditionally.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.warn('Service worker registration failed:', err);
        });
    });
}

// Capture the browser's native "Add to Home Screen" prompt (Chrome/Edge/Android) so our own
// install banner (partials.pwa-install-banner) can trigger it on demand instead of relying on
// the browser's default mini-infobar, which most users dismiss without noticing.
window.deferredInstallPrompt = null;
window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    window.deferredInstallPrompt = event;
    window.dispatchEvent(new CustomEvent('pwa-installable'));
});
window.addEventListener('appinstalled', () => {
    window.deferredInstallPrompt = null;
    window.dispatchEvent(new CustomEvent('pwa-installed'));
});
