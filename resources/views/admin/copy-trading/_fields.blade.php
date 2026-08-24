@php $trader = $trader ?? null; @endphp

<input type="text" name="display_name" class="input-field" placeholder="Display name" value="{{ old('display_name', $trader?->display_name) }}" required>
<select name="category" class="input-field">
    @foreach (['crypto' => 'Crypto', 'forex' => 'Forex', 'futures' => 'Futures', 'stock' => 'Stock', 'p2p' => 'P2P'] as $key => $label)
        <option value="{{ $key }}" @selected(old('category', $trader?->category) === $key)>{{ $label }}</option>
    @endforeach
</select>
<input type="number" name="risk_score" class="input-field" placeholder="Risk score 1-100" value="{{ old('risk_score', $trader?->risk_score ?? 50) }}" required>
<input type="number" name="followers_count" class="input-field" placeholder="Followers" value="{{ old('followers_count', $trader?->followers_count ?? 0) }}" required>

<input type="number" step="0.1" name="return_30d_pct" class="input-field" placeholder="30d return %" value="{{ old('return_30d_pct', $trader?->return_30d_pct) }}" required>
<input type="number" step="0.1" name="return_90d_pct" class="input-field" placeholder="90d return %" value="{{ old('return_90d_pct', $trader?->return_90d_pct) }}" required>
<input type="number" step="0.1" name="max_drawdown_pct" class="input-field" placeholder="Max drawdown %" value="{{ old('max_drawdown_pct', $trader?->max_drawdown_pct) }}" required>
<input type="number" step="0.1" name="win_rate_pct" class="input-field" placeholder="Win rate %" value="{{ old('win_rate_pct', $trader?->win_rate_pct) }}" required>

<div>
    <label class="label-field">Minimum copy amount (USDT)</label>
    <input type="number" step="0.01" name="min_copy_amount" class="input-field" value="{{ old('min_copy_amount', $trader?->min_copy_amount ?? 50) }}" required>
</div>
<div>
    <label class="label-field">Return period (days)</label>
    <input type="number" name="lock_days" class="input-field" value="{{ old('lock_days', $trader?->lock_days ?? 30) }}" required>
</div>
<div>
    <label class="label-field">Status</label>
    <select name="status" class="input-field">
        @foreach (['active' => 'Active', 'sold_out' => 'Sold Out', 'suspended' => 'Suspended', 'pending_approval' => 'Pending Approval'] as $key => $label)
            <option value="{{ $key }}" @selected(old('status', $trader?->status ?? 'active') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<label class="flex items-center gap-2 self-end pb-2 text-sm"><input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $trader?->is_verified)) class="rounded border-border bg-surface-2 text-brand focus:ring-brand"> Verified</label>
<label class="flex items-center gap-2 self-end pb-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $trader?->is_featured)) class="rounded border-border bg-surface-2 text-brand focus:ring-brand"> Featured</label>

<div class="sm:col-span-4">
    <label class="label-field">Strategy</label>
    <textarea name="strategy" class="input-field" rows="2">{{ old('strategy', $trader?->strategy) }}</textarea>
</div>
<div class="sm:col-span-4">
    <label class="label-field">Bio / disclosure (shown to users)</label>
    <textarea name="bio" class="input-field" rows="2">{{ old('bio', $trader?->bio) }}</textarea>
</div>
