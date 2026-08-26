@props(['symbol' => 'BINANCE:BTCUSDT', 'height' => 420, 'id' => null])
@php $widgetId = $id ?? 'tv_'.\Illuminate\Support\Str::random(10); @endphp

{{--
    TradingView's current officially-documented free embed for the Advanced Real-Time Chart
    widget (see https://www.tradingview.com/widget/advanced-chart/) — a static container plus
    a single async script carrying its JSON config as inline text. This replaced an older
    tv.js + `new TradingView.widget()` constructor pattern that TradingView has since moved
    away from documenting/promoting; that older approach was the actual cause of charts
    failing to load real-time data on some markets.
--}}
<div class="glass-card overflow-hidden p-2" style="height: {{ $height }}px;">
    <div class="tradingview-widget-container" style="height: 100%; width: 100%;">
        <div id="{{ $widgetId }}" class="tradingview-widget-container__widget" style="height: 100%; width: 100%;"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js" async>
        {
            "autosize": true,
            "symbol": {!! json_encode($symbol) !!},
            "interval": "60",
            "timezone": "Etc/UTC",
            "theme": "dark",
            "style": "1",
            "locale": "en",
            "toolbar_bg": "#0E1422",
            "enable_publishing": false,
            "allow_symbol_change": true,
            "hide_top_toolbar": false,
            "hide_legend": false,
            "save_image": false,
            "backgroundColor": "rgba(14, 20, 34, 1)",
            "gridColor": "rgba(38, 48, 68, 0.5)",
            "support_host": "https://www.tradingview.com"
        }
        </script>
    </div>
</div>
