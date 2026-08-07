<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\CardSetting;
use App\Models\CardTransaction;
use App\Models\FeatureFlag;
use App\Models\VirtualCard;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Services\TransactionalMailService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VirtualCardController extends Controller
{
    public function index()
    {
        $cards = VirtualCard::where('user_id', Auth::id())->with('transactions')->get();
        $settings = CardSetting::current();
        $issuingEnabled = (bool) (FeatureFlag::where('key', 'virtual_cards')->first()?->is_enabled ?? true);

        return view('app.virtual-cards.index', compact('cards', 'settings', 'issuingEnabled'));
    }

    public function store(Request $request, TransactionalMailService $mailer)
    {
        $user = Auth::user();
        $settings = CardSetting::current();
        $issuingEnabled = (bool) (FeatureFlag::where('key', 'virtual_cards')->first()?->is_enabled ?? true);

        if (! $issuingEnabled) {
            return back()->with('error', 'Card issuing is temporarily unavailable. Please check back later.');
        }

        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:50'],
            'spending_limit' => ['required', 'numeric', 'gt:0', 'max:'.$settings->max_spending_limit],
            'currency' => ['required', 'in:'.implode(',', $settings->allowed_currencies)],
        ]);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $lastFour = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $card = VirtualCard::create($data + [
            'user_id' => $user->id,
            'cardholder_name' => $user->name,
            'masked_number' => "**** **** **** {$lastFour}",
            'last_four' => $lastFour,
            'expiry_month' => str_pad((string) now()->addYears(3)->month, 2, '0', STR_PAD_LEFT),
            'expiry_year' => (string) now()->addYears(3)->year,
            'funding_wallet_account_id' => $wallet->id,
            'status' => 'pending',
        ]);

        AuditLog::record($user, 'virtual_card.requested', VirtualCard::class, $card->id);
        $mailer->send($user, 'virtual_card_requested', ['name' => $user->name, 'last_four' => $lastFour]);

        return back()->with('success', 'Card request submitted. An admin will review and approve it before it becomes active. This is NOT a real, spendable payment card until a licensed card-issuing provider (e.g. Stripe Issuing, Marqeta or Lithic) is connected.');
    }

    public function freeze(VirtualCard $card)
    {
        $this->authorizeOwner($card);
        abort_unless($card->status === 'active', 422, 'Only active cards can be frozen.');
        $card->update(['status' => 'frozen']);

        return back()->with('success', 'Card frozen.');
    }

    public function unfreeze(VirtualCard $card)
    {
        $this->authorizeOwner($card);
        abort_unless($card->status === 'frozen', 422, 'Only frozen cards can be unfrozen.');
        $card->update(['status' => 'active']);

        return back()->with('success', 'Card unfrozen.');
    }

    public function updateLimit(Request $request, VirtualCard $card)
    {
        $this->authorizeOwner($card);
        abort_if(in_array($card->status, ['cancelled', 'rejected']), 422);

        $settings = CardSetting::current();
        $data = $request->validate(['spending_limit' => ['required', 'numeric', 'gt:0', 'max:'.$settings->max_spending_limit]]);
        $card->update($data);

        return back()->with('success', 'Spending limit updated.');
    }

    public function fund(Request $request, VirtualCard $card, LedgerService $ledger)
    {
        $this->authorizeOwner($card);
        abort_unless($card->status === 'active', 422, 'This card is not yet active.');

        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0']]);

        $wallet = $card->fundingWallet;
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet($wallet->type);

        try {
            $ledger->post(
                entries: [
                    ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $data['amount']],
                    ['wallet_account_id' => $house->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $data['amount']],
                ],
                referenceType: 'card_funding',
                referenceId: $card->id,
                description: "Card funding for card ending {$card->last_four}",
                createdBy: Auth::user(),
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Insufficient balance in your Primary Wallet.');
        }

        CardTransaction::create([
            'virtual_card_id' => $card->id,
            'merchant' => 'Bitzlatoview Card Top-Up',
            'amount' => $data['amount'],
            'status' => 'settled',
            'occurred_at' => now(),
        ]);

        return back()->with('success', 'Card funding recorded on your account ledger. No real payment network transaction occurred — this card cannot be used for real purchases until a licensed issuing provider is connected.');
    }

    public function reveal(VirtualCard $card)
    {
        $this->authorizeOwner($card);
        abort_unless($card->status === 'active', 422, 'This card is not yet active.');

        // In production, full PAN would come from the issuing provider via a secure, time-limited reveal flow.
        $fullNumber = '4242 4242 4242 '.$card->last_four;

        return response()->json(['number' => $fullNumber, 'cvv' => str_pad((string) mt_rand(0, 999), 3, '0', STR_PAD_LEFT)]);
    }

    public function cancel(VirtualCard $card)
    {
        $this->authorizeOwner($card);
        $card->update(['status' => 'cancelled']);

        return back()->with('success', 'Card cancelled.');
    }

    protected function authorizeOwner(VirtualCard $card): void
    {
        abort_unless($card->user_id === Auth::id(), 403);
    }
}
