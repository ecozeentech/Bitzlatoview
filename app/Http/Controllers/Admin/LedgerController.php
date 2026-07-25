<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerTransaction;

class LedgerController extends Controller
{
    public function index()
    {
        $transactions = LedgerTransaction::with('entries.asset', 'entries.walletAccount.user', 'createdBy', 'approvedBy')
            ->latest()->paginate(25);

        return view('admin.ledger.index', compact('transactions'));
    }
}
