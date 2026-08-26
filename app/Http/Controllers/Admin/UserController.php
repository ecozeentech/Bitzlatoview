<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNote;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\WalletAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $assets = Asset::where('is_active', true)->orderBy('symbol')->get();

        return view('admin.users.show', compact('user', 'ledgerEntries', 'notes', 'assets'));
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

    public function updateNote(Request $request, AdminNote $note)
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);

        $before = $note->only('note');
        $note->update($data);

        AuditLog::record(auth()->user(), 'admin_note.updated', AdminNote::class, $note->id, $before, $data);

        return back()->with('success', 'Note updated.');
    }

    public function destroyNote(AdminNote $note)
    {
        AuditLog::record(auth()->user(), 'admin_note.deleted', AdminNote::class, $note->id, $note->only('note'));
        $note->delete();

        return back()->with('success', 'Note deleted.');
    }

    public function toggleWalletSuspension(Request $request, User $user, string $type)
    {
        abort_unless(in_array($type, WalletAccount::TYPES, true), 404);

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => $type]);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $wallet->update([
            'is_suspended' => ! $wallet->is_suspended,
            'suspension_reason' => $wallet->is_suspended ? null : ($data['reason'] ?? null),
        ]);

        AuditLog::record(auth()->user(), 'wallet.suspension_toggled', WalletAccount::class, $wallet->id, null, ['type' => $type, 'is_suspended' => $wallet->is_suspended]);

        return back()->with('success', $wallet->is_suspended
            ? $wallet->label()." suspended for {$user->email} — transfers out and withdrawals are now blocked."
            : $wallet->label()." reactivated for {$user->email}.");
    }

    public function loginAsUser(User $user)
    {
        abort_if($user->isAdmin(), 403, 'Cannot impersonate another admin/staff account.');

        session(['impersonator_id' => Auth::id(), 'impersonator_name' => Auth::user()->name]);
        AuditLog::record(Auth::user(), 'user.impersonation_started', User::class, $user->id);

        Auth::login($user);

        return redirect('/app/dashboard')->with('success', "You are now viewing Bitzlatoview as {$user->name} ({$user->email}).");
    }

    public function stopImpersonating()
    {
        $impersonatorId = session('impersonator_id');
        abort_unless($impersonatorId, 403);

        $impersonatedUser = Auth::user();
        $admin = User::findOrFail($impersonatorId);

        session()->forget(['impersonator_id', 'impersonator_name']);
        AuditLog::record($admin, 'user.impersonation_ended', User::class, $impersonatedUser->id);

        Auth::login($admin);

        return redirect('/admin/users/'.$impersonatedUser->id)->with('success', 'Returned to your admin account.');
    }

    public function forcePasswordReset(User $user)
    {
        $user->forceFill(['password' => bcrypt(str()->random(20))])->save();
        AuditLog::record(auth()->user(), 'user.force_password_reset', User::class, $user->id);

        return back()->with('success', 'User password invalidated. They must use "Forgot password" to regain access.');
    }
}
