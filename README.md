# Bitzlatoview

Bitzlatoview is an original, Binance-inspired-but-not-copied multi-asset trading platform built in **PHP / Laravel**. It brings crypto spot trading, P2P, swap, copy trading, AI trading bots, mining, investments, stocks, forex, futures, MetaTrader 5 linking, an NFT marketplace, virtual cards, a tax center, analyst-package billing, a CMS/blog/news system, and a full admin back office into one dashboard.

> **This build runs entirely in simulation / paper-trading mode.** No real money, securities, crypto, or card transactions occur anywhere in this codebase. Every "deposit", "trade", "mining reward", "AI bot P&L" etc. is generated internally and settled through an auditable double-entry ledger against a platform "house" account. See [Compliance & risk posture](#compliance--risk-posture) below before ever considering a production/live-money deployment.

## Tech stack

- **PHP 8.3**, **Laravel 13**
- Blade + Tailwind CSS (dark, gold-accented fintech theme) + Alpine.js for light interactivity
- SQLite by default for zero-config local development (swap to MySQL/PostgreSQL for production — see below)
- Laravel Breeze-based authentication (login, registration, password reset, email verification) extended with:
  - KYC onboarding flow with configurable auto-approval / manual compliance review
  - A dependency-free RFC 6238 TOTP implementation for two-factor authentication
- A custom **double-entry ledger engine** (`App\Services\LedgerService`) that is the *only* code path allowed to mutate wallet balances
- A **"House" system account** (`App\Support\House`) acting as the ledger counterparty for simulated fills, rewards, and fees
- Laravel's `Mail` facade (log driver by default) as the email delivery abstraction — swap `MAIL_MAILER` for Resend/SendGrid/Postmark/SMTP

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Visit `http://127.0.0.1:8000`.

### Demo accounts (all use password `password`)

| Email | Role | Notes |
|---|---|---|
| `admin@bitzlatoview.com` | Admin | Full access to `/admin` |
| `demo@bitzlatoview.com` | User | KYC approved, seeded with BTC/ETH/USDT balances |
| `unverified@bitzlatoview.com` | User | KYC not started — used to exercise KYC gating |
| `merchant1@bitzlatoview.com` / `merchant2@…` / `merchant3@…` | User | Verified P2P merchants with live ads |

## Application structure

```
/                     Public marketing site (home, markets, product landing pages, blog, news, legal pages)
/login /register ...  Authentication (Breeze) + KYC onboarding + two-factor setup
/app/...              Authenticated user dashboard: wallets, trading, P2P, copy trading, AI bots,
                       mining, investments, stocks/forex/futures, MT5, NFT, cards, tax, billing, settings
/admin/...            Admin back office: users, KYC queue, funds/ledger/adjustments, risk & compliance,
                       trading & market controls, product catalogs, CMS, email center, support desk
```

Route definitions live in `routes/web.php`; controllers are grouped under `App\Http\Controllers\App` (user app) and `App\Http\Controllers\Admin` (admin). Views mirror the same structure under `resources/views/app` and `resources/views/admin`, extending `resources/views/layouts/{app,admin,public}.blade.php`.

## Financial model: the double-entry ledger

All balance-changing activity — deposits, withdrawals, transfers, spot/futures/forex fills, swaps, P2P escrow, mining/investment rewards, bot/copy-trading P&L, card funding, admin adjustments — is posted through `App\Services\LedgerService::post()`:

- Every transaction requires **at least two balanced entries** (debits = credits, per asset).
- Idempotency keys prevent duplicate postings on retries.
- `lockFunds()` / `unlockFunds()` move value between a wallet's `available` and `locked` buckets (e.g. an open limit order, a P2P escrow, a locked mining/investment/bot allocation) without creating value.
- `releaseLockedFunds()` moves *locked* balance from one wallet directly into another wallet's *available* balance (used for P2P escrow release and appeal resolutions), while still posting a fully balanced `LedgerTransaction`.
- **There is no direct balance-editing code path.** Admin "balance adjustments" (`/admin/adjustments`) require a reason, optional evidence URL, and a second admin's approval (maker/checker) before the ledger is touched — see `App\Http\Controllers\Admin\AdjustmentController`.
- `App\Support\House` is the platform's own system account, acting as counterparty for anything that isn't naturally balanced between two real users (simulated market fills, reward payouts, fee collection).

Users have three wallet types (`App\Models\WalletAccount::TYPES`): **Primary** (funding/P2P/cards), **Trading** (spot/futures/forex collateral), and **Investment** (bots, copy trading, mining, earn products). Internal transfers between them go through the same ledger.

## Compliance & risk posture

This build intentionally implements the guardrails called for by a multi-asset platform, while keeping everything in simulation mode:

- **KYC gates** (`kyc.approved` middleware) protect withdrawals, P2P merchant ads, virtual card issuance, and futures/stocks/forex order placement.
- **Risk disclosures** are shown on every trading, futures, bots, mining, staking/investment, copy-trading, stock/forex, and P2P surface.
- **No guaranteed-return language** anywhere in bot/mining/investment copy — all simulated performance is explicitly labeled as such.
- **Audit logging** (`App\Models\AuditLog::record()`) is called from sensitive user and admin actions (KYC decisions, withdrawals, adjustments, role changes, P2P releases, etc.).
- **Analyst/"CFA" packages** are modeled as `AnalystProfile.credential` + `credential_verified`, defaulting to unverified/generic "Market Analyst" labeling — the CFA designation is never implied unless an admin explicitly marks a profile verified, and that action is intentionally manual and audited.
- **Virtual cards** never expose a full PAN except through a explicit "reveal" action, and are clearly labeled as simulated pending a real issuing-processor integration.

## Where to plug in real providers

| Concern | Current implementation | Real integration point |
|---|---|---|
| Crypto custody / deposits / withdrawals | Simulated instant "confirmation" in `App\Http\Controllers\App\FundingController` | Wallet/custody provider (Fireblocks, BitGo, etc.) + blockchain node/webhook listeners |
| KYC/AML | Self-attested form + auto-approve unless PEP/sanctioned flags set, in `App\Http\Controllers\App\KycOnboardingController` | Licensed KYC/liveness vendor (Sumsub, Onfido, Persona, etc.) |
| Market data | Static seeded `quotes`/`market_pairs` rows, `App\Services\PricingService` | Real-time market data provider / exchange feed |
| Card issuing | Mock card records in `App\Http\Controllers\App\VirtualCardController` | Stripe Issuing, Marqeta, or Lithic (cards are issued by a bank partner under Visa/Mastercard license) |
| Stocks/Forex/Futures brokerage | Paper trading against simulated prices | Licensed broker adapter (Alpaca, Tradier, DriveWealth, MT5 bridge, etc.) — see `Admin\ExtendedMarketController` for the paper/live toggle point |
| MetaTrader 5 | Simulated account + position records, encrypted mock credentials in `App\Http\Controllers\App\Mt5Controller` | Real MT5 Manager API / broker OAuth integration |
| WalletConnect / Web3 | `App\Models\ConnectedWallet` stores address/chain only, no signing | wagmi/viem + WalletConnect Cloud project ID on the frontend |
| Email delivery | Laravel `Mail` facade, `MAIL_MAILER=log` by default | Set `MAIL_MAILER` to `resend`/`sendgrid`/`postmark`/`smtp` in `.env` and configure `config/mail.php` |
| Two-factor authentication | Self-contained RFC 6238 TOTP (`App\Services\TotpService`) | Compatible out of the box with Google Authenticator/Authy/1Password — no change needed, but consider WebAuthn for a production hardening pass |

## Feature flags

`App\Models\FeatureFlag` (seeded by `FeatureFlagSeeder`, managed at `/admin/settings/feature-flags`) lets an admin disable higher-risk modules (futures, mining, cards, etc.) without a deploy. Flags are not yet wired into route middleware — treat them as a starting point for a `feature.enabled:<key>` middleware if you need hard enforcement.

## Running tests

```bash
php artisan test
```

## Code style

```bash
./vendor/bin/pint
```

## Switching to PostgreSQL/MySQL for production

Update `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bitzlatoview
DB_USERNAME=postgres
DB_PASSWORD=secret
```

Then `php artisan migrate --seed` (drop the `--seed` in production and load only the reference data seeders you need: `AssetSeeder`, `NetworkSeeder`, `MarketSeeder`, `FeatureFlagSeeder`).

## Legal

Bitzlatoview's visuals, copy, and codebase are original. Any resemblance to major exchange product structures (spot/P2P/Earn-style sections, card offerings, API docs) is intentional as a *UX reference only*, per the product brief — no branding, logos, colors, layout code, or proprietary assets from any third party are used.
