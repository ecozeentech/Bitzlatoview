

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

// Keeps every displayed crypto price in sync with the latest quote without a full page
// reload — polls the public /markets/prices ticker (see MarketController::prices()) and
// patches any element on the current page tagged with data-live-price/-change/-high/-low/
// -volume="<PAIR-SYMBOL>" (e.g. "BTC-USDT"). No-ops entirely on pages with no such elements
// (most public/marketing pages), so this never does unnecessary work or network requests.
function initLivePrices() {
    const symbols = new Set();
    document.querySelectorAll('[data-live-price],[data-live-change],[data-live-high],[data-live-low],[data-live-volume]').forEach((el) => {
        ['livePrice', 'liveChange', 'liveHigh', 'liveLow', 'liveVolume'].forEach((key) => {
            if (el.dataset[key]) symbols.add(el.dataset[key]);
        });
    });

    if (symbols.size === 0) return;

    const money = (value, decimals) => '$' + Number(value || 0).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });

    const applyQuote = (symbol, quote) => {
        document.querySelectorAll(`[data-live-price="${symbol}"]`).forEach((el) => {
            const decimals = el.dataset.priceDecimals ? Number(el.dataset.priceDecimals) : (quote.price < 1 ? 4 : 2);
            el.textContent = money(quote.price, decimals);
        });
        document.querySelectorAll(`[data-live-change="${symbol}"]`).forEach((el) => {
            const v = Number(quote.change_24h_pct || 0);
            el.textContent = (v >= 0 ? '+' : '') + v.toFixed(2) + '%';
            el.classList.remove('price-up', 'price-down');
            el.classList.add(v >= 0 ? 'price-up' : 'price-down');
        });
        document.querySelectorAll(`[data-live-high="${symbol}"]`).forEach((el) => { el.textContent = money(quote.high_24h, 2); });
        document.querySelectorAll(`[data-live-low="${symbol}"]`).forEach((el) => { el.textContent = money(quote.low_24h, 2); });
        document.querySelectorAll(`[data-live-volume="${symbol}"]`).forEach((el) => { el.textContent = money(quote.volume_24h, 0); });
    };

    const refresh = () => {
        fetch('/markets/prices', { headers: { Accept: 'application/json' } })
            .then((res) => (res.ok ? res.json() : Promise.reject(res.status)))
            .then((quotes) => {
                Object.entries(quotes).forEach(([symbol, quote]) => {
                    if (symbols.has(symbol)) applyQuote(symbol, quote);
                });
                document.querySelectorAll('[data-live-updated-at]').forEach((el) => {
                    el.textContent = 'Live · updated ' + new Date().toLocaleTimeString();
                });
            })
            .catch(() => { /* leave last-known values on the page if a poll fails */ });
    };

    refresh();
    setInterval(refresh, 15000);
}

initLivePrices();
