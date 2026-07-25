<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SwapTransaction;

class SwapController extends Controller
{
    public function index()
    {
        $swaps = SwapTransaction::with('user', 'fromAsset', 'toAsset')->latest()->paginate(30);

        return view('admin.swap.index', compact('swaps'));
    }
}
