<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ForexPair;
use Illuminate\Http\Request;

class ForexPairController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'base_currency' => ['required', 'string', 'max:8'],
            'quote_currency' => ['required', 'string', 'max:8'],
            'bid' => ['required', 'numeric', 'min:0'],
            'ask' => ['required', 'numeric', 'min:0'],
            'spread_pips' => ['required', 'numeric', 'min:0'],
            'leverage_max' => ['required', 'integer', 'min:1', 'max:2000'],
        ]);

        $base = strtoupper($data['base_currency']);
        $quote = strtoupper($data['quote_currency']);
        $data['base_currency'] = $base;
        $data['quote_currency'] = $quote;
        $data['symbol'] = "{$base}/{$quote}";

        $pair = ForexPair::create($data);
        AuditLog::record(auth()->user(), 'forex_pair.created', ForexPair::class, $pair->id);

        return back()->with('success', "Forex pair {$pair->symbol} added.");
    }

    public function update(Request $request, ForexPair $pair)
    {
        $data = $request->validate([
            'bid' => ['required', 'numeric', 'min:0'],
            'ask' => ['required', 'numeric', 'min:0'],
            'spread_pips' => ['required', 'numeric', 'min:0'],
            'leverage_max' => ['required', 'integer', 'min:1', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $pair->update($data);
        AuditLog::record(auth()->user(), 'forex_pair.updated', ForexPair::class, $pair->id);

        return back()->with('success', "Forex pair {$pair->symbol} updated.");
    }

    public function destroy(ForexPair $pair)
    {
        if ($pair->positions()->where('status', 'open')->exists()) {
            return back()->with('error', 'Cannot delete a pair with open user positions — deactivate it instead.');
        }

        AuditLog::record(auth()->user(), 'forex_pair.deleted', ForexPair::class, $pair->id);
        $pair->delete();

        return back()->with('success', 'Forex pair deleted.');
    }
}
