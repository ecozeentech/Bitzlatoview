# Bitzlatoview

Bitzlatoview is an original, Binance-inspired-but-not-copied multi-asset trading platform built in **PHP / Laravel**. It brings crypto spot trading, P2P, swap, copy trading, AI trading bots, mining, investments, stocks, forex, futures, MetaTrader 5 linking, an NFT marketplace, virtual cards, a tax center, analyst-package billing, a CMS/blog/news system, and a full admin back office into one dashboard.

> **Read this before going live.** Bitzlatoview's ledger, wallets, KYC workflow, manual payment gateway, and P2P/matching engine are real, functioning systems — not mock data. But **accepting real customer deposits and executing real trades is regulated activity** (money transmission, and potentially securities/commodity-pool/investment-adviser activity for copy trading, AI bots, and revenue-sharing products) in essentially every jurisdiction. Do not enable real payment methods or announce the platform as "live" until your company has confirmed the required licenses/registrations with qualified legal counsel for every jurisdiction you intend to operate in. See [Going live: compliance checklist](#going-live-compliance-checklist) below.

## Tech stack

- **PHP 8.3**, **Laravel 13**
- Blade + Tailwind CSS (dark, gold-accented fintech theme) + Alpine.js for light interactivity
- SQLite by default for zero-config local development (swap to MySQL/PostgreSQL for production — see below)
- Laravel Breeze-based authentication (login, registration, password reset, email verification) extended with:
  - KYC onboarding with real document upload, always routed to manual compliance review (no auto-approval)
  - A dependency-free RFC 6238 TOTP implementation for two-factor authentication
- A custom **double-entry ledger engine** (`App\Services\LedgerService`) that is the *only* code path allowed to mutate wallet balances
- A **"House" system account** (`App\Support\House`) acting as the ledger counterparty for fee collection, reward payouts, and internal strategy-engine settlement (AI bots, copy trading, futures/forex)
- A **real peer-to-peer spot order-matching engine** (`App\Services\SpotMatchingEngine`) that matches orders directly between users, price-time priority, with no phantom liquidity
- A **live market data service** (`App\Services\MarketDataService`) pulling real crypto prices from CoinGecko
- A **manual payment gateway**: admin-configured payment methods, user-submitted deposit requests with uploaded proof of payment, and a two-step (approve → send → mark completed) withdrawal review flow — every fund movement is reviewed by a human before the ledger is touched
- Laravel's `Mail` facade (log driver by default) as the email delivery abstraction, wired to real transactional events (registration, KYC decisions, deposits, withdrawals, P2P, bots, mining, tax reports) via `App\Services\TransactionalMailService` and the admin-editable templates under `/admin/email/templates`

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

### Seeded accounts (password `password`)

| Email | Role | Notes |
|---|---|---|
| `admin@bitzlatoview.com` | Admin | Full access to `/admin` |
| `testuser@bitzlatoview.com` | User | KYC not started — internal QA account, no seeded balance |

No account is seeded with a free wallet balance. To test deposits end-to-end, add a payment method in `/admin/payment-methods`, then submit a deposit request as a user and approve it as an admin — exactly the real flow a live user would go through.

## Application structure

```
/                     Public marketing site (home, markets, product landing pages, blog, news, legal pages)
/login /register ...  Authentication (Breeze) + KYC onboarding + two-factor setup
/app/...              Authenticated user dashboard: wallets, trading, P2P, copy trading, AI bots,
                       mining, investments, stocks/forex/futures, MT5, NFT, cards, tax, billing, settings
/admin/...            Admin back office: users, KYC queue, payment methods, deposit/withdrawal requests,
                       ledger/adjustments, risk & compliance, trading & market controls, product catalogs,
                       CMS, email center, support desk
```

Route definitions live in `routes/web.php`; controllers are grouped under `App\Http\Controllers\App` (user app) and `App\Http\Controllers\Admin` (admin). Views mirror the same structure under `resources/views/app` and `resources/views/admin`, extending `resources/views/layouts/{app,admin,public}.blade.php`.

## Financial model: the double-entry ledger

All balance-changing activity — deposits, withdrawals, transfers, spot/futures/forex fills, swaps, P2P escrow, mining/investment rewards, bot/copy-trading P&L, card funding, admin adjustments — is posted through `App\Services\LedgerService::post()`:

- Every transaction requires **at least two balanced entries** (debits = credits, per asset).
- Idempotency keys prevent duplicate postings on retries.
- `lockFunds()` / `unlockFunds()` move value between a wallet's `available` and `locked` buckets (e.g. an open limit order, a P2P escrow, a locked mining/investment/bot allocation) without creating value.
- `releaseLockedFunds()` moves *locked* balance from one wallet directly into another wallet's *available* balance (used for P2P escrow release and appeal resolutions), while still posting a fully balanced `LedgerTransaction`.
- **There is no direct balance-editing code path.** Admin "balance adjustments" (`/admin/adjustments`) require a reason, optional evidence URL, and a second admin's approval (maker/checker) before the ledger is touched — see `App\Http\Controllers\Admin\AdjustmentController`.
- `App\Support\House` is the platform's own system account, acting as counterparty for anything that isn't naturally balanced between two real users (fee collection, reward payouts, and the internal strategy engine behind AI bots/copy trading/futures/forex, none of which connect to a live external exchange with real capital).

Users have three wallet types (`App\Models\WalletAccount::TYPES`): **Primary** (funding/P2P/cards), **Trading** (spot/futures/forex collateral), and **Investment** (bots, copy trading, mining, earn products). Internal transfers between them go through the same ledger.

## The manual payment gateway (deposits & withdrawals)

Real fund movement is deliberately **manual and human-reviewed** end to end:

1. **Admin configures payment methods** at `/admin/payment-methods` — crypto address + network, bank account details, or CashApp/Venmo/PayPal handles, each with min/max limits and an optional QR code.
2. **User deposits**: picks a payment method on `/app/funding/deposit`, sends funds off-platform using the published instructions, then submits a deposit request with the amount and an uploaded proof-of-payment file (`App\Http\Controllers\App\FundingController::storeDeposit`). Nothing is credited yet.
3. **Admin reviews** the request and the uploaded proof at `/admin/deposits`, then either credits the ledger (`Admin\DepositController::credit`) or rejects it with a reason.
4. **User withdraws**: requests a withdrawal, funds are immediately locked (not debited) in their wallet.
5. **Admin approves** the request (confirms it's legitimate — no funds move yet), manually sends the money externally, then **marks it completed** (`Admin\DepositController::completeWithdrawal`), which is the only point the ledger actually debits the wallet. Rejecting at any point before completion unlocks the funds back to the user.

Every step is audit-logged (`App\Models\AuditLog`) and triggers a transactional email. Proof-of-payment and KYC document uploads are stored on the **private** filesystem disk and are only ever served through authenticated admin-only routes (`Admin\DepositController::proof`, `Admin\KycController::document`) — never a public URL.

## Real order matching (spot trading)

`App\Services\SpotMatchingEngine` matches incoming orders directly against other users' resting orders in the same market (best price, then time priority — FIFO). There is no synthetic "house" liquidity standing in as a fake counterparty:

- A **limit order** that doesn't immediately cross the book simply rests on it (funds locked) until matched or cancelled.
- A **market order** fills against whatever resting liquidity exists; if there isn't enough (or any), it partially fills or is rejected — exactly like a real exchange with no market maker connected.
- Both sides of a match settle through the same ledger transaction, with maker/taker fees routed to the platform's fee-revenue account (`House::wallet('trading')`).

Copy trading, AI bots, and futures/forex still settle against **real, live market prices** (via `MarketDataService`/`PricingService`) but on Bitzlatoview's own internal engine rather than a live connection to an external exchange — this is disclosed on every relevant page.

## Live market data

`App\Services\MarketDataService` pulls real prices from [CoinGecko's public API](https://www.coingecko.com/en/api) for all seeded crypto assets. Run it manually or on a schedule:

```bash
php artisan market:sync-prices
```

`routes/console.php` schedules this to run every 5 minutes via Laravel's scheduler — make sure your host runs the standard cron entry:

```
* * * * * cd /path-to-your-app && php artisan schedule:run >> /dev/null 2>&1
```

Set `COINGECKO_API_KEY` in `.env` if you have a CoinGecko Pro plan (recommended for real traffic — the free public endpoint is rate-limited). **Stocks and forex are not yet fed by a live licensed market data vendor** and remain in disclosed paper-trading mode — see the table below.

## Compliance & risk posture

- **KYC is always manually reviewed.** `App\Http\Controllers\App\KycOnboardingController` requires real document uploads (government ID, proof of address, selfie) and always routes to the admin KYC queue — there is no auto-approval path.
- **KYC gates** (`kyc.approved` middleware) protect withdrawals, P2P merchant ads, virtual card issuance, and futures/stocks/forex order placement.
- **Risk disclosures** are shown on every trading, futures, bots, mining, staking/investment, copy-trading, stock/forex, and P2P surface, and have been reworded (see `/risk-disclosure`, `/terms`) to reflect real trading/fund-handling risk rather than "this is a simulation" language.
- **No guaranteed-return language** anywhere in bot/mining/investment copy.
- **Audit logging** (`App\Models\AuditLog::record()`) is called from sensitive user and admin actions (KYC decisions, deposits, withdrawals, adjustments, role changes, P2P releases, order matches, etc.).
- **Analyst/"CFA" packages** are modeled as `AnalystProfile.credential` + `credential_verified`, defaulting to unverified/generic "Market Analyst" labeling — the CFA designation is never implied unless an admin explicitly marks a profile verified, and that action is intentionally manual and audited.
- **Virtual cards are explicitly and repeatedly marked as NOT real, spendable cards** (in the UI and in every success message) until a licensed card-issuing provider is connected — do not remove this labeling; presenting a mock card number as a working payment instrument would be deceptive to users. Requests now go through an admin approval queue (`/admin/virtual-cards`) rather than activating instantly, and admin-configurable limits/currencies/fees live in `App\Models\CardSetting`.
- **Stocks and forex remain paper-trading** (`/app/stocks`, `/app/forex`) even though admins can now fully manage instruments/pairs, prices and CSV import (`Admin\StockInstrumentController`, `Admin\ForexPairController`) — do not remove that disclosure without real broker-dealer/RFED registration in place; a platform that lets users "trade" securities or leveraged FX against the house without that registration is exactly the kind of unlicensed brokerage/bucket-shop activity regulators pursue.
- **MetaTrader 5** (`/app/metatrader-5`) always discloses that no real broker connection exists yet, even though the banner styling is neutral rather than alarming — removing that disclosure while still generating placeholder position data would show users fabricated trading data with no indication it isn't real.
- **Seeded P2P merchant liquidity is treasury capital, not free user balance.** `P2PSeeder` credits the three demo merchant accounts via the real ledger (reference type `merchant_treasury_funding`) so their listed ads are genuinely backed — this models a company funding its own market-making accounts before going live, not giving an end user free money to withdraw. Regular user accounts are never seeded with a balance; they must go through the real deposit flow.
- **Transactional emails** (welcome, KYC decisions, deposit/withdrawal updates, P2P, bots, mining, tax reports) are wired to real events via `App\Services\TransactionalMailService` and are logged to `App\Models\EmailLog` for auditability, using admin-editable templates (`/admin/email/templates`).

## Going live: compliance checklist

Before enabling real payment methods or telling users this platform handles real money, confirm with qualified legal/compliance counsel:

1. **Money transmission / e-money licensing** in every jurisdiction where you'll accept deposits or send withdrawals. This is required *before* handling customer funds, not while an application is pending.
2. **Securities/commodities/investment-adviser analysis** for copy trading, AI bots, and any investment/staking product with a revenue-sharing or profit-participation structure — these can implicate the Howey test / CFTC commodity-pool rules / investment adviser registration depending on how they're structured and marketed.
3. **Card issuing**: real, spendable cards require a licensed processor/bank-partner stack (Stripe Issuing, Marqeta, Lithic) — this repo only ever produces internal account records.
4. **Broker/market-data licensing** for stocks and forex before removing their paper-trading label, plus a real broker adapter (Alpaca, Tradier, DriveWealth, MT5 Manager API, etc.).
5. **KYC/AML program**: a documented compliance program, SAR filing capability, and sanctions/PEP screening beyond the self-attestation collected today.
6. Once the above are confirmed for your specific structure and jurisdictions, configure real payment methods in `/admin/payment-methods` and proceed deliberately, starting with a small user base.

## Where to plug in real providers

| Concern | Current implementation | Real integration point |
|---|---|---|
| Crypto/fiat deposits & withdrawals | Manual payment gateway with proof-of-payment upload + admin review (`App\Http\Controllers\App\FundingController`, `Admin\DepositController`) | This is a legitimate manual-settlement design; add automated on-chain monitoring / bank webhook confirmation later if desired, but manual review is a reasonable and compliant starting point |
| Crypto custody | Ledger-only (no real custody) | Wallet/custody provider (Fireblocks, BitGo, etc.) for real on-chain custody |
| KYC/AML | Manual document upload + admin review, in `App\Http\Controllers\App\KycOnboardingController` / `Admin\KycController` | Licensed KYC/liveness vendor (Sumsub, Onfido, Persona, etc.) for automated identity/document verification and sanctions screening |
| Crypto market data | **Live** — CoinGecko public API via `App\Services\MarketDataService` | Already real; consider a paid plan or exchange-direct feed for production-scale traffic |
| Stock/forex market data | Admin-managed prices (manual entry or CSV import via `Admin\StockInstrumentController` / `Admin\ForexPairController`), paper trading only | Licensed market data vendor (e.g. Polygon.io, Twelve Data, Alpha Vantage) + licensed broker |
| Card issuing | Internal account records only, explicitly labeled as not real in the UI, with an admin approval queue in `App\Http\Controllers\App\VirtualCardController` / `Admin\VirtualCardController` | Stripe Issuing, Marqeta, or Lithic (cards are issued by a bank partner under Visa/Mastercard license) |
| Stocks/Forex/Futures brokerage | Paper trading; futures settle against real crypto prices on the internal engine; admin has full CRUD over instruments/pairs/markets, fees and leverage caps | Licensed broker adapter (Alpaca, Tradier, DriveWealth, MT5 bridge, etc.) — see `Admin\ExtendedMarketController`, `Admin\StockInstrumentController`, `Admin\ForexPairController`, `Admin\FuturesMarketController` for the paper/live toggle point |
| MetaTrader 5 | Account records with encrypted credentials; no live broker sync, in `App\Http\Controllers\App\Mt5Controller` | Real MT5 Manager API / broker OAuth integration |
| WalletConnect / Web3 | Real EIP-1193 `window.ethereum` connection for browser-extension wallets (MetaMask, Coinbase Wallet, Trust Wallet, Rainbow); WalletConnect QR/Ledger require a WalletConnect Cloud project ID and are clearly labeled as pending configuration. `App\Models\ConnectedWallet` stores address/chain only, no signing | A WalletConnect Cloud project ID + `@walletconnect/*` SDK for QR/hardware-wallet pairing |
| TradingView charts | Live via the free public TradingView widget (`resources/views/components/tradingview-chart.blade.php`) on spot/futures/stocks/forex pages — real market data from TradingView's own feed, independent of our backend | Already real; no change needed unless you want a custom data feed |
| Branding | Admin-uploadable logo/favicon (`/admin/settings/branding`, `App\Models\BrandingSetting`), served from `storage/app/public/branding` via the `public/storage` symlink | Already real; no change needed |
| Email delivery | Laravel `Mail` facade, real transactional triggers, `MAIL_MAILER=log` by default | Set `MAIL_MAILER` to `resend`/`sendgrid`/`postmark`/`smtp` in `.env` and configure `config/mail.php` |
| Two-factor authentication | Self-contained RFC 6238 TOTP (`App\Services\TotpService`) | Compatible out of the box with Google Authenticator/Authy/1Password — no change needed, but consider WebAuthn for a production hardening pass |
| AI bots / copy trading execution | Settle against real live crypto prices on Bitzlatoview's internal ledger (no external exchange connection) | A live exchange API connection (e.g. via a licensed broker/exchange relationship) if you want bots to execute on real external liquidity — this is a significant undertaking requiring real capital custody and its own risk controls |

## Admin capabilities

Every trading/product module has a dedicated admin section with real create/edit/delete capability, not just read-only reporting:

| Module | Admin route | What admins can do |
|---|---|---|
| Markets & assets | `/admin/markets` | Create new assets and trading pairs, set maker/taker fees, update price, activate/pause pairs |
| Stocks | `/admin/markets-extended` | Create/edit/delete instruments, bulk CSV import, activate/deactivate, manual price updates |
| Forex | `/admin/markets-extended` | Create/edit/delete pairs, set bid/ask/spread/leverage cap, activate/deactivate |
| Futures | `/admin/markets-extended` | Create/edit/delete markets, set max leverage/maintenance margin/funding rate |
| Investment products | `/admin/investments` | Full CRUD: name, description, asset, expected return, risk level, lock period, payout frequency (daily/weekly), min/max investment, active status |
| NFTs | `/admin/nft` | Create/edit/delete collections and items, upload images, set floor price |
| Virtual cards | `/admin/virtual-cards` | Approve/reject card requests, freeze/unfreeze, configure platform-wide limits/allowed currencies/fees |
| Branding | `/admin/settings/branding` | Upload logo/favicon, set site name, used across public/app/admin layouts |
| P2P | `/admin/p2p/*` | Manage ads, orders, appeals, merchant approvals |
| Payment methods, deposits, withdrawals | `/admin/payment-methods`, `/admin/deposits`, `/admin/withdrawals` | Configure manual payment rails, review proof-of-payment, two-step withdrawal approval |
| KYC | `/admin/kyc` | Review uploaded documents, approve/reject with reason |
| Copy trading, AI bots, mining | `/admin/copy-trading`, `/admin/ai-bots`, `/admin/mining` | Create/edit products, performance figures, risk disclosures |
| Signals | `/admin/signals` | Create/edit/delete/pause packages (risk level, min/max investment, duration, fee, tracked asset); view all user subscriptions; post an audited ledger correction to a settled subscription's P&L |
| Messages to users | `/admin/messages` | Send a message to one user (by email) or broadcast to all — appears in the recipient's notification bell |
| Emails, CMS, tax | `/admin/email/*`, `/admin/cms`, `/admin/blog`, `/admin/news`, `/admin/faq`, `/admin/tax` | Edit templates/campaigns, manage pages/posts/FAQs, review tax reports |
| Feature flags | `/admin/settings/feature-flags` | Enable/disable higher-risk modules (virtual cards, NFTs, etc.) without a deploy |

### Signals module

`/app/signals` is a packaged strategy product that works exactly like AI Bots and Copy Trading: a user subscribes an amount from their Investment Wallet to a package (`App\Models\SignalPackage`), and P&L is settled on stop against the package's `tracked_asset_symbol`'s **real** live price movement (via `App\Services\PricingService`), not a random number or a fabricated "AI-generated" outcome — same approach already used for AI Bots/Copy Trading. Admins manage packages (risk level, min/max investment, duration/lock period, fee %, tracked asset, active status) at `/admin/signals`, and can post an audited, ledger-backed correction to a settled subscription's P&L if needed (never a silent field edit — it always posts a real, logged ledger transaction for the delta).

### Localization (in progress)

A real (not decorative) language switcher is wired up via `App\Http\Middleware\SetLocale` and `lang/{en,es,fr,ar,zh}/common.php` — switching persists to the logged-in user's `locale` column (or session for guests) and actually changes `App::getLocale()`, including `dir="rtl"` for Arabic. **This currently covers navigation, the topbar, and a handful of common UI strings — it does not yet cover every screen in the app.** Translating the full application is a substantial follow-on effort (100+ view files); the `common.php` files list every key currently wired up as a starting point to extend from.

Legal and risk-disclosure text (terms, risk disclosure page, KYC legal copy, risk banners) is **intentionally left English-only** regardless of the selected UI language until a qualified translator/legal reviewer signs off on each language — an incorrect translation of "no guaranteed returns" or similar language is a compliance risk, not just a UX nitpick. Do not extend translations to that content without that review.

## Feature flags

`App\Models\FeatureFlag` (seeded by `FeatureFlagSeeder`, managed at `/admin/settings/feature-flags`) lets an admin disable higher-risk modules without a deploy. The `virtual_cards` flag is enforced in `App\Http\Controllers\App\VirtualCardController` (new card requests are blocked while disabled); other flags are a starting point for a `feature.enabled:<key>` middleware if you need hard enforcement everywhere.

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

Then `php artisan migrate --seed` (drop the `--seed` in production and load only the reference data seeders you need: `AssetSeeder`, `NetworkSeeder`, `MarketSeeder`, `FeatureFlagSeeder`). Follow up with `php artisan market:sync-prices` to populate live prices immediately rather than waiting for the first scheduled run.

## Legal

Bitzlatoview's visuals, copy, and codebase are original. Any resemblance to major exchange product structures (spot/P2P/Earn-style sections, card offerings, API docs) is intentional as a *UX reference only*, per the product brief — no branding, logos, colors, layout code, or proprietary assets from any third party are used.
