<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiBotAllocation;
use App\Models\Deposit;
use App\Models\EmailLog;
use App\Models\KycSubmission;
use App\Models\MiningContract;
use App\Models\P2PAppeal;
use App\Models\SupportTicket;
use App\Models\Trade;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'new_users_7d' => User::where('role', 'user')->where('created_at', '>=', now()->subDays(7))->count(),
            'kyc_pending' => KycSubmission::whereIn('status', ['submitted', 'under_review'])->count(),
            'deposit_volume' => Deposit::where('status', 'credited')->sum('amount'),
            'withdrawal_volume' => Withdrawal::where('status', 'completed')->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'pending_review')->count(),
            'p2p_disputes' => P2PAppeal::where('status', 'open')->count(),
            'active_bots' => AiBotAllocation::where('status', 'active')->count(),
            'active_mining' => MiningContract::where('status', 'active')->count(),
            'trading_volume' => Trade::sum(DB::raw('price * quantity')),
            'revenue_fees' => Trade::sum('fee'),
            'support_open' => SupportTicket::whereIn('status', ['open', 'pending'])->count(),
            'emails_sent' => EmailLog::count(),
        ];

        $recentUsers = User::where('role', 'user')->latest()->take(6)->get();
        $recentWithdrawals = Withdrawal::with('user', 'asset')->latest()->take(6)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentWithdrawals'));
    }
}
