<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiBot;
use Illuminate\Http\Request;

class AiBotController extends Controller
{
    public function index()
    {
        $bots = AiBot::withCount('allocations')->get();

        return view('admin.ai-bots.index', compact('bots'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'strategy_type' => ['required', 'in:conservative,balanced,aggressive,grid,dca,trend,arbitrage'],
            'risk_score' => ['required', 'integer', 'min:1', 'max:100'],
            'min_allocation' => ['required', 'numeric', 'gt:0'],
            'historical_return_pct' => ['required', 'numeric'],
            'max_drawdown_pct' => ['required', 'numeric'],
            'lock_days' => ['required', 'integer', 'min:0'],
        ]);

        AiBot::create($data + [
            'description' => 'AI trading bot is experimental, runs in simulated/paper mode, and may lose money. No guaranteed returns.',
            'status' => 'active',
        ]);

        return back()->with('success', 'Bot created.');
    }

    public function update(Request $request, AiBot $bot)
    {
        $data = $request->validate(['status' => ['required', 'in:active,paused,retired']]);
        $bot->update($data);

        return back()->with('success', 'Bot updated.');
    }
}
