<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNote;
use App\Models\AuditLog;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('role', 'user')
            ->when($request->q, fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%")))
            ->when($request->kyc_status, fn ($q) => $q->where('kyc_status', $request->kyc_status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('walletAccounts.balances.asset', 'kycSubmissions', 'orders', 'deposits', 'withdrawals', 'virtualCards');
        $ledgerEntries = LedgerEntry::whereIn('wallet_account_id', $user->walletAccounts->pluck('id'))->with('asset')->latest()->take(30)->get();
        $notes = AdminNote::where('notable_type', User::class)->where('notable_id', $user->id)->latest()->get();

        return view('admin.users.show', compact('user', 'ledgerEntries', 'notes'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:user,admin,support,compliance'],
        ]);

        $before = $user->only('role');
        $user->forceFill($data)->save();

        AuditLog::record(auth()->user(), 'user.role_updated', User::class, $user->id, $before, $data);

        return back()->with('success', 'User updated.');
    }

    public function suspend(User $user)
    {
        $user->forceFill(['status' => 'suspended', 'suspended_at' => now()])->save();
        AuditLog::record(auth()->user(), 'user.suspended', User::class, $user->id);

        return back()->with('success', 'User suspended.');
    }

    public function unsuspend(User $user)
    {
        $user->forceFill(['status' => 'active', 'suspended_at' => null])->save();
        AuditLog::record(auth()->user(), 'user.unsuspended', User::class, $user->id);

        return back()->with('success', 'User reactivated.');
    }

    public function addNote(Request $request, User $user)
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);

        AdminNote::create([
            'notable_type' => User::class,
            'notable_id' => $user->id,
            'admin_id' => auth()->id(),
            'note' => $data['note'],
        ]);

        return back()->with('success', 'Note added.');
    }

    public function forcePasswordReset(User $user)
    {
        $user->forceFill(['password' => bcrypt(str()->random(20))])->save();
        AuditLog::record(auth()->user(), 'user.force_password_reset', User::class, $user->id);

        return back()->with('success', 'User password invalidated. They must use "Forgot password" to regain access.');
    }
}
