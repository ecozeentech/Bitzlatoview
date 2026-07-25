<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VirtualCard;

class VirtualCardController extends Controller
{
    public function index()
    {
        $cards = VirtualCard::with('user', 'transactions')->latest()->paginate(25);

        return view('admin.virtual-cards.index', compact('cards'));
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

        return back()->with('success', 'Card unfrozen.');
    }
}
