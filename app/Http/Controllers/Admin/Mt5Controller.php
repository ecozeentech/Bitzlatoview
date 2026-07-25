<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mt5Account;

class Mt5Controller extends Controller
{
    public function index()
    {
        $accounts = Mt5Account::with('user', 'positions')->latest()->get();

        return view('admin.metatrader.index', compact('accounts'));
    }
}
