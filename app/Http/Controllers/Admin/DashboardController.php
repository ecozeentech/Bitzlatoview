<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiBotAllocation;
use App\Models\AuditLog;
use App\Models\ComplianceAlert;
use App\Models\Deposit;
use App\Models\EmailLog;
use App\Models\KycSubmission;
use App\Models\MiningContract;
use App\Models\Order;
use App\Models\P2PAppeal;
use App\Models\SupportTicket;
use App\Models\Trade;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'newUsers' => User::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'kycPending' => KycSubmission::query()->whereIn('status', ['submitted', 'under_review'])->count(),
            'depositVolume' => Deposit::query()->where('status', 'completed')->sum('amount'),
            'withdrawalVolume' => Withdrawal::query()->where('status', 'completed')->sum('amount'),
            'pendingWithdrawals' => Withdrawal::query()->where('status', 'pending_review')->count(),
            'p2pDisputes' => P2PAppeal::query()->where('status', 'open')->count(),
            'activeBots' => AiBotAllocation::query()->where('status', 'active')->count(),
            'activeMining' => MiningContract::query()->where('status', 'active')->count(),
            'tradingVolume' => Trade::query()->sum('quantity'),
            'openOrders' => Order::query()->whereIn('status', ['new', 'partially_filled'])->count(),
            'supportTickets' => SupportTicket::query()->where('status', 'open')->count(),
            'emailSent' => EmailLog::query()->where('status', 'sent')->where('created_at', '>=', now()->subDay())->count(),
            'riskAlerts' => ComplianceAlert::query()->where('status', 'open')->count(),
            'recentAudits' => AuditLog::query()->latest()->limit(10)->get(),
        ]);
    }
}
