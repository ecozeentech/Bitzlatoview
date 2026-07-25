<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Mt5Account;
use App\Models\Mt5Position;
use App\Models\Mt5SyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class Mt5Controller extends Controller
{
    public function index()
    {
        $accounts = Mt5Account::where('user_id', Auth::id())->with('positions', 'syncLogs')->get();

        return view('app.mt5.index', compact('accounts'));
    }

    public function connect(Request $request)
    {
        $data = $request->validate([
            'broker_name' => ['required', 'string', 'max:100'],
            'mt5_login' => ['required', 'string', 'max:50'],
            'server_name' => ['required', 'string', 'max:100'],
            'account_type' => ['required', 'in:demo,standard,ecn'],
            'leverage' => ['required', 'integer', 'min:1', 'max:1000'],
            'currency' => ['required', 'string', 'max:8'],
            'password' => ['required', 'string', 'max:100'],
        ]);

        $account = Mt5Account::create([
            'user_id' => Auth::id(),
            'broker_name' => $data['broker_name'],
            'mt5_login' => $data['mt5_login'],
            'server_name' => $data['server_name'],
            'account_type' => $data['account_type'],
            'leverage' => $data['leverage'],
            'currency' => $data['currency'],
            'encrypted_credentials' => Crypt::encryptString($data['password']),
            'status' => 'connected',
            'last_sync_at' => now(),
        ]);

        Mt5SyncLog::create([
            'mt5_account_id' => $account->id,
            'status' => 'success',
            'message' => 'Initial simulated connection established.',
            'synced_at' => now(),
        ]);

        $this->seedSimulatedPositions($account);

        AuditLog::record(Auth::user(), 'mt5.connected', Mt5Account::class, $account->id);

        return back()->with('success', 'MT5 account connected (simulated). Credentials are encrypted at rest and never displayed in plain text.');
    }

    public function sync(Mt5Account $account)
    {
        abort_unless($account->user_id === Auth::id(), 403);

        $account->update(['last_sync_at' => now()]);

        foreach ($account->positions as $position) {
            $position->update([
                'current_price' => $position->open_price * (1 + (mt_rand(-200, 200) / 10000)),
                'pnl' => round($position->volume * mt_rand(-50, 80), 2),
            ]);
        }

        Mt5SyncLog::create([
            'mt5_account_id' => $account->id,
            'status' => 'success',
            'message' => 'Synced simulated positions and trade history.',
            'synced_at' => now(),
        ]);

        return back()->with('success', 'Account synced.');
    }

    public function disconnect(Mt5Account $account)
    {
        abort_unless($account->user_id === Auth::id(), 403);

        $account->update(['status' => 'disconnected']);
        AuditLog::record(Auth::user(), 'mt5.disconnected', Mt5Account::class, $account->id);

        return back()->with('success', 'MT5 account disconnected.');
    }

    protected function seedSimulatedPositions(Mt5Account $account): void
    {
        $symbols = ['EURUSD', 'GBPUSD', 'USDJPY'];

        foreach (array_slice($symbols, 0, mt_rand(1, 3)) as $symbol) {
            Mt5Position::create([
                'mt5_account_id' => $account->id,
                'symbol' => $symbol,
                'side' => mt_rand(0, 1) ? 'buy' : 'sell',
                'volume' => round(mt_rand(1, 10) / 10, 2),
                'open_price' => round(mt_rand(90, 150), 3),
                'current_price' => round(mt_rand(90, 150), 3),
                'pnl' => round(mt_rand(-50, 80), 2),
            ]);
        }
    }
}
