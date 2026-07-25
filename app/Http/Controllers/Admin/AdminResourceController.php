<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiBot;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\BillingPackage;
use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\ComplianceAlert;
use App\Models\CopyTraderProfile;
use App\Models\Deposit;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\FaqItem;
use App\Models\FeatureFlag;
use App\Models\FeeSchedule;
use App\Models\ForexPair;
use App\Models\FuturesMarket;
use App\Models\InvestmentProduct;
use App\Models\KycSubmission;
use App\Models\LedgerTransaction;
use App\Models\ManualAdjustment;
use App\Models\MarketPair;
use App\Models\MiningContract;
use App\Models\MiningPackage;
use App\Models\MT5Account;
use App\Models\NewsArticle;
use App\Models\NftCollection;
use App\Models\Order;
use App\Models\P2PAd;
use App\Models\P2PAppeal;
use App\Models\P2POrder;
use App\Models\StockInstrument;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\TaxReport;
use App\Models\Trade;
use App\Models\User;
use App\Models\VirtualCard;
use App\Models\Withdrawal;
use App\Services\AuditLogger;
use App\Services\EmailDispatchService;
use App\Services\LedgerService;
use App\Services\P2PService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminResourceController extends Controller
{
    public function __construct(
        private LedgerService $ledger,
        private AuditLogger $audit,
        private P2PService $p2p,
        private EmailDispatchService $email,
    ) {}

    public function users(Request $request): View
    {
        $users = User::query()
            ->when($request->q, fn ($q) => $q->where('email', 'like', '%'.$request->q.'%')->orWhere('name', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(30);

        return view('admin.users.index', compact('users'));
    }

    public function userShow(int $id): View
    {
        $user = User::query()->with(['walletAccounts.balances.asset', 'kycSubmissions'])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function userUpdate(Request $request, int $id): RedirectResponse
    {
        $user = User::query()->findOrFail($id);
        $data = $request->validate([
            'status' => ['required', 'in:active,suspended'],
            'role' => ['required', 'in:user,admin,support,compliance'],
            'kyc_status' => ['nullable', 'string'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $before = $user->only(['status', 'role', 'kyc_status']);
        $user->update(collect($data)->only(['status', 'role', 'kyc_status'])->all());

        if (! empty($data['admin_note'])) {
            \App\Models\AdminNote::query()->create([
                'user_id' => $user->id,
                'admin_id' => $request->user()->id,
                'note' => $data['admin_note'],
            ]);
        }

        $this->audit->log('admin.user.updated', $user, $before, $user->fresh()->only(['status', 'role', 'kyc_status']));

        return back()->with('success', 'User updated.');
    }

    public function kyc(): View
    {
        return view('admin.kyc.index', [
            'submissions' => KycSubmission::query()->with('user')->latest()->paginate(30),
        ]);
    }

    public function kycReview(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,reject,more_info'],
            'rejection_reason' => ['nullable', 'string'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $submission = KycSubmission::query()->findOrFail($id);
        $statusMap = [
            'approve' => 'approved',
            'reject' => 'rejected',
            'more_info' => 'more_info_required',
        ];
        $status = $statusMap[$data['action']];

        $submission->update([
            'status' => $status,
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $submission->user->update(['kyc_status' => $status]);

        if ($status === 'approved') {
            $this->email->sendTemplate('kyc_approved', $submission->user, ['name' => $submission->user->name]);
        } elseif ($status === 'rejected') {
            $this->email->sendTemplate('kyc_rejected', $submission->user, ['name' => $submission->user->name]);
        }

        $this->audit->log('admin.kyc.reviewed', $submission, null, ['status' => $status]);

        return back()->with('success', 'KYC updated.');
    }

    public function deposits(): View
    {
        return view('admin.deposits.index', [
            'deposits' => Deposit::query()->with('user')->latest()->paginate(40),
        ]);
    }

    public function withdrawals(): View
    {
        return view('admin.withdrawals.index', [
            'withdrawals' => Withdrawal::query()->with('user')->latest()->paginate(40),
        ]);
    }

    public function withdrawalAction(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $withdrawal = Withdrawal::query()->findOrFail($id);
        $wallet = $withdrawal->walletAccount()->first() ?? \App\Models\WalletAccount::query()->findOrFail($withdrawal->wallet_account_id);
        $asset = Asset::query()->findOrFail($withdrawal->asset_id);

        if ($data['action'] === 'approve') {
            // Debit locked funds (finalize withdrawal)
            $this->ledger->post(
                type: 'withdrawal',
                entries: [[
                    'wallet_account_id' => $wallet->id,
                    'asset_id' => $asset->id,
                    'entry_type' => 'debit',
                    'amount' => (string) $withdrawal->amount,
                    'balance_bucket' => 'locked',
                ]],
                userId: $withdrawal->user_id,
                idempotencyKey: 'withdrawal-complete-'.$withdrawal->uuid,
                referenceType: Withdrawal::class,
                referenceId: $withdrawal->id,
                description: 'Withdrawal completed',
                approvedBy: $request->user()->id,
                reason: $data['admin_note'] ?? 'Approved',
            );
            $withdrawal->update([
                'status' => 'completed',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'processed_at' => now(),
            ]);
        } else {
            $this->ledger->unlockFunds(
                $wallet,
                $asset,
                (string) $withdrawal->amount,
                'withdrawal_reject',
                'withdrawal-reject-'.$withdrawal->uuid,
                Withdrawal::class,
                $withdrawal->id,
                'Withdrawal rejected unlock'
            );
            $withdrawal->update(['status' => 'rejected', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
        }

        $this->audit->log('admin.withdrawal.'.$data['action'], $withdrawal);

        return back()->with('success', 'Withdrawal '.$data['action'].'d.');
    }

    public function ledger(): View
    {
        return view('admin.ledger.index', [
            'transactions' => LedgerTransaction::query()->with('entries')->latest()->paginate(50),
        ]);
    }

    public function adjustments(): View
    {
        return view('admin.wallets.adjustments', [
            'adjustments' => ManualAdjustment::query()->latest()->paginate(30),
            'users' => User::query()->orderBy('email')->limit(100)->get(),
            'assets' => Asset::query()->where('is_active', true)->get(),
        ]);
    }

    public function requestAdjustment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'wallet_type' => ['required', 'in:PRIMARY,TRADING,INVESTMENT'],
            'asset_id' => ['required', 'exists:assets,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'direction' => ['required', 'in:credit,debit'],
            'reason' => ['required', 'string', 'max:500'],
            'evidence_url' => ['nullable', 'url'],
        ]);

        $user = User::query()->findOrFail($data['user_id']);
        $wallet = $user->walletAccount($data['wallet_type']);

        ManualAdjustment::query()->create([
            ...$data,
            'wallet_account_id' => $wallet->id,
            'status' => 'pending',
            'requested_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Adjustment requested. Requires maker/checker approval.');
    }

    public function approveAdjustment(Request $request, int $id): RedirectResponse
    {
        $adj = ManualAdjustment::query()->findOrFail($id);
        if ($adj->requested_by === $request->user()->id) {
            return back()->with('error', 'Maker/checker: requester cannot approve.');
        }
        if ($adj->status !== 'pending') {
            return back()->with('error', 'Already processed.');
        }

        $wallet = \App\Models\WalletAccount::query()->findOrFail($adj->wallet_account_id);
        $asset = Asset::query()->findOrFail($adj->asset_id);

        if ($adj->direction === 'credit') {
            $this->ledger->creditAvailable($wallet, $asset, (string) $adj->amount, 'adjustment', 'adj-'.$adj->id, ManualAdjustment::class, $adj->id, $adj->reason, $request->user()->id);
        } else {
            $this->ledger->debitAvailable($wallet, $asset, (string) $adj->amount, 'adjustment', 'adj-'.$adj->id, ManualAdjustment::class, $adj->id, $adj->reason, $request->user()->id);
        }

        $adj->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $this->audit->log('admin.adjustment.approved', $adj);

        return back()->with('success', 'Adjustment posted through ledger.');
    }

    public function orders(): View
    {
        return view('admin.orders.index', ['orders' => Order::query()->latest()->paginate(50)]);
    }

    public function trades(): View
    {
        return view('admin.orders.trades', ['trades' => Trade::query()->latest()->paginate(50)]);
    }

    public function markets(): View
    {
        return view('admin.markets.index', [
            'pairs' => MarketPair::query()->orderBy('symbol')->get(),
            'assets' => Asset::query()->orderBy('symbol')->get(),
        ]);
    }

    public function p2p(): View
    {
        return view('admin.p2p.index', [
            'ads' => P2PAd::query()->latest()->limit(20)->get(),
            'orders' => P2POrder::query()->latest()->limit(20)->get(),
            'appeals' => P2PAppeal::query()->where('status', 'open')->latest()->get(),
        ]);
    }

    public function resolveAppeal(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'resolution' => ['required', 'in:release,refund,cancel'],
            'admin_resolution' => ['required', 'string'],
        ]);

        $appeal = P2PAppeal::query()->findOrFail($id);
        $order = P2POrder::query()->findOrFail($appeal->p2p_order_id);

        if ($data['resolution'] === 'release') {
            $this->p2p->release($order, $request->user());
        } elseif ($data['resolution'] === 'cancel') {
            // For paid/appealed, unlock back to seller if still locked
            if ($order->status !== 'completed') {
                $order->update(['status' => 'awaiting_payment']);
                $this->p2p->cancel($order, $request->user());
            }
        } else {
            // refund = cancel escrow back to seller
            if ($order->status !== 'completed') {
                $order->update(['status' => 'awaiting_payment']);
                $this->p2p->cancel($order, $request->user());
                $order->update(['status' => 'refunded']);
            }
        }

        $appeal->update([
            'status' => 'resolved',
            'admin_resolution' => $data['admin_resolution'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        $this->audit->log('admin.p2p.appeal_resolved', $appeal);

        return back()->with('success', 'Appeal resolved.');
    }

    public function module(string $module): View
    {
        $map = [
            'copy-trading' => ['view' => 'admin.copy-trading.index', 'data' => ['traders' => CopyTraderProfile::query()->latest()->get()]],
            'ai-bots' => ['view' => 'admin.ai-bots.index', 'data' => ['bots' => AiBot::query()->latest()->get()]],
            'mining' => ['view' => 'admin.mining.index', 'data' => ['packages' => MiningPackage::query()->latest()->get(), 'contracts' => MiningContract::query()->latest()->limit(50)->get()]],
            'investments' => ['view' => 'admin.investments.index', 'data' => ['products' => InvestmentProduct::query()->latest()->get(), 'packages' => BillingPackage::query()->latest()->get()]],
            'stocks' => ['view' => 'admin.stocks.index', 'data' => ['stocks' => StockInstrument::query()->get()]],
            'forex' => ['view' => 'admin.forex.index', 'data' => ['pairs' => ForexPair::query()->get()]],
            'futures' => ['view' => 'admin.futures.index', 'data' => ['markets' => FuturesMarket::query()->get()]],
            'metatrader' => ['view' => 'admin.metatrader.index', 'data' => ['accounts' => MT5Account::query()->latest()->paginate(30)]],
            'nft' => ['view' => 'admin.nft.index', 'data' => ['collections' => NftCollection::query()->latest()->get()]],
            'virtual-cards' => ['view' => 'admin.virtual-cards.index', 'data' => ['cards' => VirtualCard::query()->latest()->paginate(40)]],
            'tax' => ['view' => 'admin.tax.index', 'data' => ['reports' => TaxReport::query()->latest()->paginate(40)]],
            'news' => ['view' => 'admin.news.index', 'data' => ['articles' => NewsArticle::query()->latest()->paginate(30)]],
            'blog' => ['view' => 'admin.blog.index', 'data' => ['posts' => BlogPost::query()->latest()->paginate(30)]],
            'cms' => ['view' => 'admin.cms.index', 'data' => ['pages' => CmsPage::query()->get(), 'faqs' => FaqItem::query()->orderBy('sort_order')->get()]],
            'fees' => ['view' => 'admin.fees.index', 'data' => ['fees' => FeeSchedule::query()->get()]],
            'risk' => ['view' => 'admin.risk.index', 'data' => ['alerts' => ComplianceAlert::query()->latest()->paginate(40)]],
            'compliance' => ['view' => 'admin.compliance.index', 'data' => ['alerts' => ComplianceAlert::query()->latest()->paginate(40)]],
            'audit-logs' => ['view' => 'admin.audit-logs.index', 'data' => ['logs' => AuditLog::query()->latest()->paginate(50)]],
            'support' => ['view' => 'admin.support.index', 'data' => ['tickets' => SupportTicket::query()->latest()->paginate(40)]],
            'settings' => ['view' => 'admin.settings.index', 'data' => ['flags' => FeatureFlag::query()->get(), 'settings' => SystemSetting::query()->get()]],
            'email' => ['view' => 'admin.email.index', 'data' => [
                'templates' => EmailTemplate::query()->get(),
                'campaigns' => EmailCampaign::query()->latest()->get(),
                'logs' => EmailLog::query()->latest()->limit(50)->get(),
            ]],
            'wallets' => ['view' => 'admin.wallets.index', 'data' => ['users' => User::query()->with('walletAccounts.balances.asset')->latest()->paginate(20)]],
            'funding-notes' => ['view' => 'admin.deposits.funding-notes', 'data' => ['notes' => \App\Models\FundingNote::query()->latest()->paginate(50)]],
            'assets' => ['view' => 'admin.markets.assets', 'data' => ['assets' => Asset::query()->orderBy('symbol')->get()]],
            'swap' => ['view' => 'admin.markets.swap', 'data' => ['swaps' => \App\Models\SwapTransaction::query()->latest()->paginate(40)]],
        ];

        abort_unless(isset($map[$module]), 404);
        $cfg = $map[$module];

        return view($cfg['view'], $cfg['data']);
    }

    public function storeNews(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'summary' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'sentiment' => ['required', 'in:bullish,neutral,bearish'],
            'source' => ['nullable', 'string'],
        ]);

        NewsArticle::query()->create([
            ...$data,
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'News article published.');
    }

    public function storeBlog(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
        ]);

        BlogPost::query()->create([
            ...$data,
            'author_id' => $request->user()->id,
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Blog post published.');
    }

    public function storeEmailTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string'],
            'subject' => ['required', 'string'],
            'body_html' => ['required', 'string'],
            'category' => ['required', 'in:transactional,marketing'],
        ]);

        EmailTemplate::query()->updateOrCreate(['key' => $data['key']], [...$data, 'is_active' => true]);

        return back()->with('success', 'Email template saved.');
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'subject' => ['required', 'string'],
            'body_html' => ['required', 'string'],
            'audience_segment' => ['required', 'string'],
        ]);

        EmailCampaign::query()->create([
            ...$data,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Campaign drafted.');
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'template' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $this->email->sendTemplate($data['template'], $data['email'], ['name' => 'Test User']);

        return back()->with('success', 'Test email dispatched via provider adapter.');
    }
}
