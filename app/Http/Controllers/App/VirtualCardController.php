<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\CardTransaction;
use App\Models\VirtualCard;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VirtualCardController extends Controller
{
    public function index()
    {
        $cards = VirtualCard::where('user_id', Auth::id())->with('transactions')->get();

        return view('app.virtual-cards.index', compact('cards'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:50'],
            'spending_limit' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'in:USD,EUR,GBP'],
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
            'status' => 'active',
        ]);

        AuditLog::record($user, 'virtual_card.created', VirtualCard::class, $card->id);

        return back()->with('success', 'Virtual card created. This is a simulated card (no real issuing provider connected). Use Stripe Issuing, Marqeta or Lithic for a live deployment.');
    }

    public function freeze(VirtualCard $card)
    {
        $this->authorizeOwner($card);
        $card->update(['status' => 'frozen']);

        return back()->with('success', 'Card frozen.');
    }

    public function unfreeze(VirtualCard $card)
    {
        $this->authorizeOwner($card);
        $card->update(['status' => 'active']);

        return back()->with('success', 'Card unfrozen.');
    }

    public function fund(Request $request, VirtualCard $card, LedgerService $ledger)
    {
        $this->authorizeOwner($card);

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

        return back()->with('success', 'Card funded (simulated spend record created).');
    }

    public function reveal(VirtualCard $card)
    {
        $this->authorizeOwner($card);

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
