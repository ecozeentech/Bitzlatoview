<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiBot;
use App\Models\AuditLog;
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
        $data = $this->validated($request);

        AiBot::create($data + [
            'description' => 'AI trading bot is experimental and may lose money. It runs on Bitzlatoview\'s internal strategy engine, not a live connection to an external exchange. No guaranteed returns.',
        ]);

        return back()->with('success', 'Bot created.');
    }

    public function update(Request $request, AiBot $bot)
    {
        $data = $this->validated($request);

        $before = $bot->only(array_keys($data));
        $bot->update($data);

        AuditLog::record(auth()->user(), 'ai_bot.updated', AiBot::class, $bot->id, $before, $data);

        return back()->with('success', 'Bot updated.');
    }

    public function toggleAvailability(AiBot $bot)
    {
        $newStatus = $bot->status === 'sold_out' ? 'active' : 'sold_out';
        $bot->update(['status' => $newStatus]);

        AuditLog::record(auth()->user(), 'ai_bot.availability_toggled', AiBot::class, $bot->id, null, ['status' => $newStatus]);

        return back()->with('success', $newStatus === 'sold_out'
            ? "{$bot->name} is now marked Sold Out — no new allocations until you mark it available again."
            : "{$bot->name} is available again.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'strategy_type' => ['required', 'in:conservative,balanced,aggressive,grid,dca,trend,arbitrage'],
            'risk_score' => ['required', 'integer', 'min:1', 'max:100'],
            'min_allocation' => ['required', 'numeric', 'gt:0'],
            'historical_return_pct' => ['required', 'numeric'],
            'max_drawdown_pct' => ['required', 'numeric'],
            'lock_days' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,paused,retired,sold_out'],
        ]);
    }
}
