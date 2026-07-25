# Bitzlatoview

Laravel (PHP) multi-asset trading platform MVP — **simulation / paper-trading mode first**.

Bitzlatoview provides a Binance-inspired product surface (exchange density, markets, P2P, wallets, earn-style modules) with an **original** dark navy / gold UI. It is **not** a Binance clone and does not use Binance branding or proprietary assets.

## Stack

- PHP 8.3+
- Laravel 13
- Blade + Alpine.js + Tailwind CSS
- SQLite by default (swap to PostgreSQL for production)
- Double-entry ledger for all balance changes
- Session auth (Laravel Breeze)

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using SQLite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

### Demo accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@bitzlatoview.com` | `password` |
| Verified user | `demo@bitzlatoview.com` | `password` |
| Unverified user | `unverified@bitzlatoview.com` | `password` |
| P2P merchant | `merchant@bitzlatoview.com` | `password` |

## What is included

### Public site
Marketing homepage, markets, top gainers/losers, crypto/stocks/forex/futures/NFT pages, swap/buy/P2P promos, copy trading, AI bots, mining, investments, MetaTrader 5, news/blog, legal/compliance pages, FAQ, contact.

### Auth & KYC
Register/login/password reset/email verification hooks, 2FA enablement, device session placeholders, KYC onboarding with document upload placeholders and admin review queue.

### User app
Dashboard, spot trading (simulated matching), buy/sell, swap, Primary/Trading/Investment wallets, deposit/withdraw/transfer with funding notes, P2P marketplace (escrow, chat, appeals), copy trading, AI bots, mining, investments, analyst package billing, stocks/forex/futures paper modules, MT5 connect placeholder, NFT section, virtual cards (mock), tax center, WalletConnect placeholder, support, referrals, settings.

### Admin
Users, KYC, deposits/withdrawals, ledger, maker/checker adjustments, orders/trades/markets, P2P appeals, email templates/campaigns/logs, news/blog/CMS, risk/compliance/audit logs, and module management pages.

### API
Public market/content endpoints under `/api/*`, plus authenticated session endpoints for wallets/orders/P2P/cards.

## Critical design rules

1. **Paper mode by default** — no live custody/broker/card issuing until providers are connected.
2. **No guaranteed returns** — bots, mining, copy trading, and investments show risk disclosures.
3. **Double-entry ledger only** — never mutate balances directly (`App\Services\LedgerService`).
4. **Admin adjustments** require maker/checker approval and create audited ledger entries.
5. **KYC gates** withdrawals, cards, futures, and merchant-grade P2P features.
6. **CFA branding** is not sold/implied unless `credential_verified` is true for a real charterholder with legal approval.
7. **WalletConnect** connects external wallets only — it does **not** credit custodial ledger balances.

## Where to connect real providers

Search the codebase for `PROVIDER:` comments. Key adapters:

| Area | Suggested providers | Location |
|------|---------------------|----------|
| Email | Resend / SendGrid / Postmark / SMTP | `App\Services\EmailDispatchService` |
| KYC/AML | Persona / Sumsub / Jumio | KYC controllers + document storage |
| Custody / deposits | Fireblocks / BitGo / Copper | Deposit/withdrawal services |
| Market data / charts | TradingView / exchange feeds | Spot views + market APIs |
| Card issuing | Stripe Issuing / Marqeta / Lithic | Virtual card controllers |
| Stocks / brokerage | Alpaca / IBKR / DriveWealth | Stock module |
| Forex / MT5 | Licensed broker APIs | MT5 module (never store plain passwords) |
| WalletConnect | WalletConnect Cloud + wagmi/viem | Settings → WalletConnect |
| Tax | Taxbit / Lukka / CoinTracker-style export | Tax center |

## PostgreSQL (production)

Update `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bitzlatoview
DB_USERNAME=bitzlatoview
DB_PASSWORD=secret
```

Then `php artisan migrate --seed`.

## Redis (optional)

Use Redis for cache/queues/rate limiting once available:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

## Compliance notice

This repository is an engineering MVP. Virtual-currency exchange, futures, securities, cards, and remittance-like flows can trigger money-transmitter, SEC/CFTC, tax, and licensing obligations depending on jurisdiction and facts. Do not enable live funds without counsel and licensed providers.

## License

Proprietary / project-specific. Update before public distribution.
