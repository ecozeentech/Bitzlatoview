@php $bot = $bot ?? null; @endphp

<input type="text" name="name" class="input-field" placeholder="Name" value="{{ old('name', $bot?->name) }}" required>
<select name="strategy_type" class="input-field">
    @foreach (['conservative','balanced','aggressive','grid','dca','trend','arbitrage'] as $s)
        <option value="{{ $s }}" @selected(old('strategy_type', $bot?->strategy_type) === $s)>{{ ucfirst($s) }}</option>
    @endforeach
</select>
<input type="number" name="risk_score" class="input-field" placeholder="Risk score" value="{{ old('risk_score', $bot?->risk_score ?? 50) }}" required>
<input type="number" step="0.01" name="min_allocation" class="input-field" placeholder="Min allocation" value="{{ old('min_allocation', $bot?->min_allocation ?? 50) }}" required>
<input type="number" step="0.1" name="historical_return_pct" class="input-field" placeholder="Historical return %" value="{{ old('historical_return_pct', $bot?->historical_return_pct) }}" required>
<input type="number" step="0.1" name="max_drawdown_pct" class="input-field" placeholder="Max drawdown %" value="{{ old('max_drawdown_pct', $bot?->max_drawdown_pct) }}" required>
<input type="number" name="lock_days" class="input-field" placeholder="Lock days" value="{{ old('lock_days', $bot?->lock_days ?? 0) }}" required>
<select name="status" class="input-field">
    @foreach (['active' => 'Active', 'sold_out' => 'Sold Out', 'paused' => 'Paused', 'retired' => 'Retired'] as $key => $label)
        <option value="{{ $key }}" @selected(old('status', $bot?->status ?? 'active') === $key)>{{ $label }}</option>
    @endforeach
</select>
