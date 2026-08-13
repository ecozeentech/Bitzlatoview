@php $package = $package ?? null; @endphp

<div>
    <label class="label-field">Name</label>
    <input type="text" name="name" class="input-field" value="{{ old('name', $package?->name) }}" required>
</div>
<div>
    <label class="label-field">Tracked asset (real price used for settlement)</label>
    <input type="text" name="tracked_asset_symbol" class="input-field" value="{{ old('tracked_asset_symbol', $package?->tracked_asset_symbol ?? 'BTC') }}" required>
</div>
<div>
    <label class="label-field">Status</label>
    <select name="status" class="input-field" required>
        <option value="active" @selected(old('status', $package?->status ?? 'active') === 'active')>Active</option>
        <option value="paused" @selected(old('status', $package?->status) === 'paused')>Paused</option>
        <option value="retired" @selected(old('status', $package?->status) === 'retired')>Retired</option>
    </select>
</div>

<div class="md:col-span-3">
    <label class="label-field">Description (shown to users — must not promise guaranteed returns)</label>
    <textarea name="description" class="input-field" rows="2">{{ old('description', $package?->description) }}</textarea>
</div>

<div>
    <label class="label-field">Expected return (% — disclosed estimate)</label>
    <input type="number" step="0.01" name="expected_return_pct" class="input-field" value="{{ old('expected_return_pct', $package?->expected_return_pct) }}" required>
</div>
<div>
    <label class="label-field">Risk level</label>
    <select name="risk_level" class="input-field" required>
        <option value="low" @selected(old('risk_level', $package?->risk_level) === 'low')>Low</option>
        <option value="moderate" @selected(old('risk_level', $package?->risk_level ?? 'moderate') === 'moderate')>Moderate</option>
        <option value="high" @selected(old('risk_level', $package?->risk_level) === 'high')>High</option>
    </select>
</div>
<div>
    <label class="label-field">Duration (days)</label>
    <input type="number" name="duration_days" class="input-field" value="{{ old('duration_days', $package?->duration_days ?? 30) }}" required>
</div>

<div>
    <label class="label-field">Min investment</label>
    <input type="number" step="0.01" name="min_investment" class="input-field" value="{{ old('min_investment', $package?->min_investment ?? 50) }}" required>
</div>
<div>
    <label class="label-field">Max investment (optional)</label>
    <input type="number" step="0.01" name="max_investment" class="input-field" value="{{ old('max_investment', $package?->max_investment) }}">
</div>
<div>
    <label class="label-field">Fee (%)</label>
    <input type="number" step="0.01" name="fee_pct" class="input-field" value="{{ old('fee_pct', $package?->fee_pct ?? 0) }}" required>
</div>
