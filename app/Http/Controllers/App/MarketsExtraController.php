<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\BillingPackage;
use App\Models\Cardholder;
use App\Models\CardTransaction;
use App\Models\ConnectedWallet;
use App\Models\ForexOrder;
use App\Models\ForexPair;
use App\Models\ForexPosition;
use App\Models\FuturesMarket;
use App\Models\FuturesPosition;
use App\Models\Invoice;
use App\Models\MT5Account;
use App\Models\MT5Position;
use App\Models\NftCollection;
use App\Models\NftItem;
use App\Models\StockInstrument;
use App\Models\StockOrder;
use App\Models\StockPosition;
use App\Models\Subscription;
use App\Models\TaxReport;
use App\Models\VirtualCard;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarketsExtraController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    public function stocks(): View
    {
        return view('app.stocks.index', [
            'stocks' => StockInstrument::query()->where('is_active', true)->get(),
            'positions' => StockPosition::query()->where('user_id', auth()->id())->with('stockInstrument')->get(),
        ]);
    }

    public function stockOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stock_instrument_id' => ['required', 'exists:stock_instruments,id'],
            'side' => ['required', 'in:buy,sell'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $stock = StockInstrument::query()->findOrFail($data['stock_instrument_id']);
        $user = $request->user();
        $wallet = $user->walletAccount('TRADING');
        $usdt = Asset::query()->where('symbol', 'USDT')->firstOrFail();
        $notional = bcmul((string) $data['quantity'], (string) $stock->last_price, 8);

        if ($data['side'] === 'buy') {
            $this->ledger->debitAvailable($wallet, $usdt, $notional, 'stock_trade', (string) Str::uuid(), StockOrder::class, null, 'Paper stock buy');
            $pos = StockPosition::query()->firstOrCreate(
                ['user_id' => $user->id, 'stock_instrument_id' => $stock->id],
                ['quantity' => 0, 'avg_cost' => 0]
            );
            $newQty = bcadd((string) $pos->quantity, (string) $data['quantity'], 6);
            $pos->update(['quantity' => $newQty, 'avg_cost' => $stock->last_price]);
        } else {
            $pos = StockPosition::query()->where('user_id', $user->id)->where('stock_instrument_id', $stock->id)->firstOrFail();
            if (bccomp((string) $pos->quantity, (string) $data['quantity'], 6) < 0) {
                return back()->with('error', 'Insufficient shares.');
            }
            $this->ledger->creditAvailable($wallet, $usdt, $notional, 'stock_trade', (string) Str::uuid(), StockOrder::class, null, 'Paper stock sell');
            $pos->update(['quantity' => bcsub((string) $pos->quantity, (string) $data['quantity'], 6)]);
        }

        StockOrder::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'stock_instrument_id' => $stock->id,
            'side' => $data['side'],
            'type' => 'market',
            'quantity' => $data['quantity'],
            'price' => $stock->last_price,
            'status' => 'filled',
            'is_simulated' => true,
        ]);

        return back()->with('success', 'Paper stock order filled. Not real brokerage execution.');
    }

    public function forex(): View
    {
        return view('app.forex.index', [
            'pairs' => ForexPair::query()->where('is_active', true)->get(),
            'positions' => ForexPosition::query()->where('user_id', auth()->id())->where('status', 'open')->with('forexPair')->get(),
        ]);
    }

    public function forexOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'forex_pair_id' => ['required', 'exists:forex_pairs,id'],
            'side' => ['required', 'in:buy,sell'],
            'lots' => ['required', 'numeric', 'gt:0'],
            'leverage' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $pair = ForexPair::query()->findOrFail($data['forex_pair_id']);
        $price = $data['side'] === 'buy' ? $pair->ask : $pair->bid;

        ForexOrder::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'forex_pair_id' => $pair->id,
            'side' => $data['side'],
            'lots' => $data['lots'],
            'price' => $price,
            'leverage' => $data['leverage'] ?? 50,
            'status' => 'filled',
            'is_simulated' => true,
        ]);

        ForexPosition::query()->create([
            'user_id' => $request->user()->id,
            'forex_pair_id' => $pair->id,
            'side' => $data['side'],
            'lots' => $data['lots'],
            'entry_price' => $price,
            'status' => 'open',
            'is_simulated' => true,
        ]);

        return back()->with('success', 'Paper forex position opened. High risk — not real broker execution.');
    }

    public function futures(Request $request): View
    {
        return view('app.futures.index', [
            'markets' => FuturesMarket::query()->where('is_active', true)->get(),
            'positions' => FuturesPosition::query()->where('user_id', $request->user()->id)->where('status', 'open')->get(),
            'hasAgreement' => (bool) $request->user()->futures_agreement_at,
        ]);
    }

    public function acceptFuturesAgreement(Request $request): RedirectResponse
    {
        $request->user()->update(['futures_agreement_at' => now()]);

        return back()->with('success', 'Futures risk agreement accepted.');
    }

    public function futuresOrder(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->kycApproved()) {
            return back()->with('error', 'KYC required for futures.');
        }
        if (! $user->futures_agreement_at) {
            return back()->with('error', 'Accept futures agreement first.');
        }

        $data = $request->validate([
            'futures_market_id' => ['required', 'exists:futures_markets,id'],
            'side' => ['required', 'in:long,short'],
            'size' => ['required', 'numeric', 'gt:0'],
            'leverage' => ['required', 'integer', 'min:1', 'max:20'],
            'margin_mode' => ['required', 'in:cross,isolated'],
        ]);

        $market = FuturesMarket::query()->findOrFail($data['futures_market_id']);
        $notional = bcmul((string) $data['size'], (string) $market->mark_price, 8);
        $margin = bcdiv($notional, (string) $data['leverage'], 8);
        $wallet = $user->walletAccount('TRADING');
        $usdt = Asset::query()->where('symbol', 'USDT')->firstOrFail();

        $this->ledger->lockFunds($wallet, $usdt, $margin, 'futures_margin', (string) Str::uuid(), FuturesPosition::class, null, 'Futures margin lock');

        $liq = $data['side'] === 'long'
            ? bcmul((string) $market->mark_price, '0.9', 8)
            : bcmul((string) $market->mark_price, '1.1', 8);

        FuturesPosition::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'futures_market_id' => $market->id,
            'side' => $data['side'],
            'margin_mode' => $data['margin_mode'],
            'leverage' => $data['leverage'],
            'size' => $data['size'],
            'entry_price' => $market->mark_price,
            'mark_price' => $market->mark_price,
            'liquidation_price' => $liq,
            'margin' => $margin,
            'status' => 'open',
            'is_simulated' => true,
        ]);

        return back()->with('success', 'Simulated futures position opened. Futures trading is high risk.');
    }

    public function metatrader(Request $request): View
    {
        return view('app.metatrader.index', [
            'accounts' => MT5Account::query()->where('user_id', $request->user()->id)->get(),
            'positions' => MT5Position::query()->whereHas('mt5Account', fn ($q) => $q->where('user_id', $request->user()->id))->get(),
        ]);
    }

    public function connectMt5(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'broker_name' => ['required', 'string', 'max:120'],
            'mt5_login' => ['required', 'string', 'max:60'],
            'server_name' => ['required', 'string', 'max:120'],
            'account_type' => ['required', 'in:demo,live'],
            'leverage' => ['nullable', 'integer', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        // PROVIDER: Prefer broker OAuth/API. Never store MT5 passwords in plain text.
        MT5Account::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'connected',
            'last_sync_at' => now(),
            'is_simulated' => true,
            'encrypted_credentials' => null,
        ]);

        return back()->with('success', 'MT5 account linked in simulation mode.');
    }

    public function nft(): View
    {
        return view('app.nft.index', [
            'collections' => NftCollection::query()->where('is_active', true)->orderByDesc('volume_24h')->get(),
        ]);
    }

    public function nftCollections(): View
    {
        return view('app.nft.collections', [
            'collections' => NftCollection::query()->where('is_active', true)->get(),
        ]);
    }

    public function myNfts(Request $request): View
    {
        return view('app.nft.my-nfts', [
            'items' => NftItem::query()->where('owner_user_id', $request->user()->id)->with('nftCollection')->get(),
        ]);
    }

    public function virtualCards(Request $request): View
    {
        return view('app.virtual-cards.index', [
            'cards' => VirtualCard::query()->where('user_id', $request->user()->id)->latest()->get(),
            'kycApproved' => $request->user()->kycApproved(),
        ]);
    }

    public function createCard(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->kycApproved()) {
            return back()->with('error', 'Approved KYC required for virtual cards.');
        }

        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:60'],
            'spending_limit' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $cardholder = Cardholder::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['legal_name' => $user->full_legal_name ?: $user->name, 'status' => 'active']
        );

        $lastFour = (string) random_int(1000, 9999);

        // PROVIDER: Stripe Issuing / Marqeta / Lithic for real card issuance.
        VirtualCard::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'cardholder_id' => $cardholder->id,
            'nickname' => $data['nickname'] ?? 'Bitzlatoview Card',
            'last_four' => $lastFour,
            'brand' => 'Visa',
            'currency' => $data['currency'],
            'spending_limit' => $data['spending_limit'],
            'status' => 'active',
            'masked_pan' => '**** **** **** '.$lastFour,
            'is_simulated' => true,
        ]);

        return back()->with('success', 'Virtual card created (mock issuer). Full PAN never stored.');
    }

    public function freezeCard(Request $request, int $id): RedirectResponse
    {
        $card = VirtualCard::query()->where('user_id', $request->user()->id)->findOrFail($id);
        $card->update(['status' => $card->status === 'frozen' ? 'active' : 'frozen']);

        return back()->with('success', 'Card status updated.');
    }

    public function cardTransactions(Request $request, int $id): View
    {
        $card = VirtualCard::query()->where('user_id', $request->user()->id)->findOrFail($id);

        return view('app.virtual-cards.transactions', [
            'card' => $card,
            'transactions' => CardTransaction::query()->where('virtual_card_id', $card->id)->latest()->get(),
        ]);
    }

    public function tax(Request $request): View
    {
        $report = TaxReport::query()->firstOrCreate(
            ['user_id' => $request->user()->id, 'tax_year' => now()->year, 'country' => $request->user()->country ?: 'US'],
            [
                'cost_basis_method' => 'FIFO',
                'realized_gains' => 1250.55,
                'realized_losses' => 320.10,
                'income_total' => 88.25,
                'fees_paid' => 42.00,
                'status' => 'draft',
            ]
        );

        return view('app.tax.index', compact('report'));
    }

    public function analystPackages(): View
    {
        return view('app.investments.analyst-packages', [
            'packages' => BillingPackage::query()->where('is_active', true)->get(),
        ]);
    }

    public function purchasePackage(Request $request, int $id): RedirectResponse
    {
        $package = BillingPackage::query()->findOrFail($id);
        $user = $request->user();
        $wallet = $user->walletAccount('PRIMARY');
        $usdt = Asset::query()->where('symbol', 'USDT')->firstOrFail();

        $this->ledger->debitAvailable(
            $wallet,
            $usdt,
            (string) $package->price,
            'subscription',
            'pkg-'.$package->id.'-'.$user->id.'-'.now()->timestamp,
            BillingPackage::class,
            $package->id,
            'Analyst package purchase'
        );

        $sub = Subscription::query()->create([
            'user_id' => $user->id,
            'billing_package_id' => $package->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $line = $package->credential_verified && str_contains(strtoupper($package->analyst_credential ?? ''), 'CFA')
            ? 'CFA Charterholder Research Package'
            : ($package->invoice_label ?: 'Market Analyst Package');

        Invoice::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'subscription_id' => $sub->id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'line_item' => $line,
            'amount' => $package->price,
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Analyst package purchased. This is not investment advice.');
    }

    public function walletConnect(Request $request): View
    {
        return view('app.settings.wallet-connect', [
            'wallets' => ConnectedWallet::query()->where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }

    public function connectExternalWallet(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:100'],
            'chain' => ['required', 'string', 'max:40'],
            'wallet_type' => ['required', 'string', 'max:40'],
            'label' => ['nullable', 'string', 'max:60'],
        ]);

        // PROVIDER: WalletConnect project ID + wagmi/viem for real sessions.
        // External wallet balance is NOT credited to custodial ledger.
        ConnectedWallet::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'address' => $data['address'],
                'chain' => $data['chain'],
            ],
            [
                'wallet_type' => $data['wallet_type'],
                'label' => $data['label'] ?? null,
                'last_connected_at' => now(),
            ]
        );

        return back()->with('success', 'External wallet connected (placeholder). Custodial balances unchanged.');
    }
}
