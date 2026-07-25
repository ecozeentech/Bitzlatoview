<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Deposit;
use App\Models\FundingNote;
use App\Models\Network;
use App\Models\Transfer;
use App\Models\Withdrawal;
use App\Models\WithdrawalAddress;
use App\Services\AuditLogger;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        private LedgerService $ledger,
        private AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('app.wallet.index', [
            'primary' => $user->walletAccount('PRIMARY')?->load('balances.asset'),
            'trading' => $user->walletAccount('TRADING')?->load('balances.asset'),
            'investment' => $user->walletAccount('INVESTMENT')?->load('balances.asset'),
        ]);
    }

    public function show(Request $request, string $type): View
    {
        $wallet = $request->user()->walletAccount(strtoupper($type));
        abort_unless($wallet, 404);

        return view('app.wallet.show', [
            'wallet' => $wallet->load('balances.asset'),
            'type' => strtoupper($type),
        ]);
    }

    public function depositForm(Request $request): View
    {
        return view('app.wallet.deposit', [
            'assets' => Asset::query()->where('type', 'crypto')->where('is_active', true)->get(),
            'networks' => Network::query()->where('is_active', true)->get(),
            'wallets' => $request->user()->walletAccounts,
        ]);
    }

    public function deposit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'wallet_type' => ['required', 'in:PRIMARY,TRADING,INVESTMENT'],
            'asset_id' => ['required', 'exists:assets,id'],
            'network_id' => ['nullable', 'exists:networks,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'user_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $wallet = $user->walletAccount($data['wallet_type']);
        $asset = Asset::query()->findOrFail($data['asset_id']);

        $deposit = Deposit::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'wallet_account_id' => $wallet->id,
            'asset_id' => $asset->id,
            'network_id' => $data['network_id'] ?? null,
            'amount' => $data['amount'],
            'status' => 'pending',
            'address' => 'sim_'.$asset->symbol.'_'.Str::lower(Str::random(28)),
            'is_simulated' => true,
        ]);

        if (! empty($data['user_note'])) {
            FundingNote::query()->create([
                'notable_type' => Deposit::class,
                'notable_id' => $deposit->id,
                'user_note' => $data['user_note'],
            ]);
        }

        // MVP simulation: auto-confirm after create.
        $this->ledger->creditAvailable(
            $wallet,
            $asset,
            $data['amount'],
            'deposit',
            'deposit-'.$deposit->uuid,
            Deposit::class,
            $deposit->id,
            'Simulated deposit credit'
        );

        $deposit->update([
            'status' => 'completed',
            'confirmations' => 3,
            'tx_hash' => '0xsim'.Str::random(60),
            'confirmed_at' => now(),
        ]);

        $this->audit->log('deposit.completed', $deposit, null, $deposit->toArray(), null, $request);

        return redirect()->route('app.wallet.show', strtolower($data['wallet_type']))
            ->with('success', 'Deposit simulated and credited via ledger.');
    }

    public function withdrawForm(Request $request): View
    {
        return view('app.wallet.withdraw', [
            'assets' => Asset::query()->where('type', 'crypto')->where('is_active', true)->get(),
            'networks' => Network::query()->where('is_active', true)->get(),
            'wallets' => $request->user()->walletAccounts,
            'addresses' => WithdrawalAddress::query()->where('user_id', $request->user()->id)->get(),
        ]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'wallet_type' => ['required', 'in:PRIMARY,TRADING,INVESTMENT'],
            'asset_id' => ['required', 'exists:assets,id'],
            'network_id' => ['nullable', 'exists:networks,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'destination_address' => ['required', 'string', 'max:200'],
            'user_note' => ['nullable', 'string', 'max:1000'],
            'two_factor_code' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        if (! $user->kycApproved()) {
            return back()->with('error', 'Approved KYC is required for withdrawals.');
        }

        $wallet = $user->walletAccount($data['wallet_type']);
        $asset = Asset::query()->findOrFail($data['asset_id']);
        $fee = '1.00000000';

        $withdrawal = Withdrawal::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'wallet_account_id' => $wallet->id,
            'asset_id' => $asset->id,
            'network_id' => $data['network_id'] ?? null,
            'amount' => $data['amount'],
            'fee' => $fee,
            'destination_address' => $data['destination_address'],
            'status' => 'pending_review',
            'is_simulated' => true,
        ]);

        $this->ledger->lockFunds(
            $wallet,
            $asset,
            $data['amount'],
            'withdrawal_lock',
            'withdrawal-lock-'.$withdrawal->uuid,
            Withdrawal::class,
            $withdrawal->id,
            'Lock funds for withdrawal'
        );

        if (! empty($data['user_note'])) {
            FundingNote::query()->create([
                'notable_type' => Withdrawal::class,
                'notable_id' => $withdrawal->id,
                'user_note' => $data['user_note'],
            ]);
        }

        $this->audit->log('withdrawal.requested', $withdrawal, null, $withdrawal->toArray(), null, $request);

        return redirect()->route('app.wallet.history')
            ->with('success', 'Withdrawal submitted for review. Funds locked via ledger.');
    }

    public function transferForm(Request $request): View
    {
        return view('app.wallet.transfer', [
            'assets' => Asset::query()->where('is_active', true)->get(),
            'wallets' => $request->user()->walletAccounts,
        ]);
    }

    public function transfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_wallet' => ['required', 'in:PRIMARY,TRADING,INVESTMENT'],
            'to_wallet' => ['required', 'in:PRIMARY,TRADING,INVESTMENT', 'different:from_wallet'],
            'asset_id' => ['required', 'exists:assets,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'user_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $from = $user->walletAccount($data['from_wallet']);
        $to = $user->walletAccount($data['to_wallet']);
        $asset = Asset::query()->findOrFail($data['asset_id']);

        // Investment → Primary subject to lock rules (MVP allows with note).
        $transfer = Transfer::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'from_wallet_account_id' => $from->id,
            'to_wallet_account_id' => $to->id,
            'asset_id' => $asset->id,
            'amount' => $data['amount'],
            'status' => 'completed',
        ]);

        $this->ledger->transferBetweenWallets(
            $from,
            $to,
            $asset,
            $data['amount'],
            'transfer-'.$transfer->uuid,
            "Transfer {$data['from_wallet']} → {$data['to_wallet']}"
        );

        if (! empty($data['user_note'])) {
            FundingNote::query()->create([
                'notable_type' => Transfer::class,
                'notable_id' => $transfer->id,
                'user_note' => $data['user_note'],
            ]);
        }

        $this->audit->log('wallet.transfer', $transfer, null, $transfer->toArray(), null, $request);

        return back()->with('success', 'Transfer completed through double-entry ledger.');
    }

    public function history(Request $request): View
    {
        $user = $request->user();

        return view('app.wallet.history', [
            'deposits' => Deposit::query()->where('user_id', $user->id)->latest()->limit(20)->get(),
            'withdrawals' => Withdrawal::query()->where('user_id', $user->id)->latest()->limit(20)->get(),
            'transfers' => Transfer::query()->where('user_id', $user->id)->latest()->limit(20)->get(),
        ]);
    }
}
