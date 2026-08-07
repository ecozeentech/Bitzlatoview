<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CardSetting;
use App\Models\CardTransaction;
use App\Models\VirtualCard;
use App\Services\TransactionalMailService;
use Illuminate\Http\Request;

class VirtualCardController extends Controller
{
    public function index()
    {
        $cards = VirtualCard::with('user', 'transactions')->latest()->paginate(25);
        $pending = VirtualCard::where('status', 'pending')->with('user')->latest()->get();
        $settings = CardSetting::current();

        return view('admin.virtual-cards.index', compact('cards', 'pending', 'settings'));
    }

    public function approve(VirtualCard $card, TransactionalMailService $mailer)
    {
        abort_unless($card->status === 'pending', 422, 'Only pending requests can be approved.');

        $card->update(['status' => 'active', 'approved_at' => now()]);
        $this->seedIllustrativeTransactions($card);

        AuditLog::record(auth()->user(), 'virtual_card.approved', VirtualCard::class, $card->id);
        $mailer->send($card->user, 'virtual_card_approved', ['name' => $card->user->name, 'last_four' => $card->last_four]);

        return back()->with('success', 'Card approved and activated.');
    }

    public function reject(Request $request, VirtualCard $card, TransactionalMailService $mailer)
    {
        abort_unless($card->status === 'pending', 422, 'Only pending requests can be rejected.');

        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);
        $card->update(['status' => 'rejected', 'rejection_reason' => $data['rejection_reason']]);

        AuditLog::record(auth()->user(), 'virtual_card.rejected', VirtualCard::class, $card->id);
        $mailer->send($card->user, 'virtual_card_rejected', ['name' => $card->user->name, 'reason' => $data['rejection_reason']]);

        return back()->with('success', 'Card request rejected.');
    }

    public function freeze(VirtualCard $card)
    {
        $card->update(['status' => 'frozen']);
        AuditLog::record(auth()->user(), 'virtual_card.frozen_by_admin', VirtualCard::class, $card->id);

        return back()->with('success', 'Card frozen.');
    }

    public function unfreeze(VirtualCard $card)
    {
        $card->update(['status' => 'active']);
        AuditLog::record(auth()->user(), 'virtual_card.unfrozen_by_admin', VirtualCard::class, $card->id);

        return back()->with('success', 'Card unfrozen.');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'max_spending_limit' => ['required', 'numeric', 'min:0'],
            'allowed_currencies' => ['required', 'array', 'min:1'],
            'allowed_currencies.*' => ['in:USD,EUR,GBP'],
            'issuance_fee' => ['required', 'numeric', 'min:0'],
            'funding_fee_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $settings = CardSetting::current();
        $settings->update($data);
        CardSetting::forget();

        AuditLog::record(auth()->user(), 'card_settings.updated', CardSetting::class, $settings->id);

        return back()->with('success', 'Card settings updated.');
    }

    protected function seedIllustrativeTransactions(VirtualCard $card): void
    {
        $merchants = ['Amazon', 'Starbucks', 'Uber', 'Netflix', 'Apple Store'];
        for ($i = 0; $i < 3; $i++) {
            CardTransaction::create([
                'virtual_card_id' => $card->id,
                'merchant' => $merchants[array_rand($merchants)].' (sample — illustrative only)',
                'amount' => round(mt_rand(500, 15000) / 100, 2),
                'status' => 'settled',
                'occurred_at' => now()->subDays(mt_rand(1, 20)),
            ]);
        }
    }
}
