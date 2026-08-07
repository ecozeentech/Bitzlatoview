@php $product = $product ?? null; @endphp

<div>
    <label class="label-field">Name</label>
    <input type="text" name="name" class="input-field" value="{{ old('name', $product?->name) }}" required>
</div>
<div>
    <label class="label-field">Asset</label>
    <select name="asset_id" class="input-field" required>
        @foreach ($assets as $asset)
            <option value="{{ $asset->id }}" @selected(old('asset_id', $product?->asset_id) == $asset->id)>{{ $asset->symbol }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label-field">Status</label>
    <select name="status" class="input-field" required>
        <option value="active" @selected(old('status', $product?->status ?? 'active') === 'active')>Active</option>
        <option value="paused" @selected(old('status', $product?->status) === 'paused')>Paused</option>
    </select>
</div>

<div class="md:col-span-3">
    <label class="label-field">Description (shown to users — must not promise guaranteed returns)</label>
    <textarea name="description" class="input-field" rows="2">{{ old('description', $product?->description) }}</textarea>
</div>

<div>
    <label class="label-field">Expected return (APY %)</label>
    <input type="number" step="0.01" name="apy_pct" class="input-field" value="{{ old('apy_pct', $product?->apy_pct) }}" required>
</div>
<div>
    <label class="label-field">Risk level</label>
    <select name="risk_level" class="input-field" required>
        <option value="low" @selected(old('risk_level', $product?->risk_level) === 'low')>Low</option>
        <option value="moderate" @selected(old('risk_level', $product?->risk_level ?? 'moderate') === 'moderate')>Moderate</option>
        <option value="high" @selected(old('risk_level', $product?->risk_level) === 'high')>High</option>
    </select>
</div>
<div>
    <label class="label-field">Payout frequency</label>
    <select name="payout_frequency" class="input-field" required>
        <option value="daily" @selected(old('payout_frequency', $product?->payout_frequency ?? 'daily') === 'daily')>Daily</option>
        <option value="weekly" @selected(old('payout_frequency', $product?->payout_frequency) === 'weekly')>Weekly</option>
    </select>
</div>

<div>
    <label class="label-field">Lock period (days, 0 = flexible)</label>
    <input type="number" name="lock_days" class="input-field" value="{{ old('lock_days', $product?->lock_days ?? 0) }}" required>
</div>
<div>
    <label class="label-field">Min investment</label>
    <input type="number" step="0.01" name="min_amount" class="input-field" value="{{ old('min_amount', $product?->min_amount ?? 10) }}" required>
</div>
<div>
    <label class="label-field">Max investment (optional)</label>
    <input type="number" step="0.01" name="max_amount" class="input-field" value="{{ old('max_amount', $product?->max_amount) }}">
</div>
