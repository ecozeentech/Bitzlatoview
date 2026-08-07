@props(['symbol' => 'BINANCE:BTCUSDT', 'height' => 420, 'id' => null])
@php $widgetId = $id ?? 'tv_'.\Illuminate\Support\Str::random(10); @endphp

<div class="glass-card overflow-hidden p-2">
    <div id="{{ $widgetId }}" style="height: {{ $height }}px;"></div>
</div>

<script src="https://s3.tradingview.com/tv.js"></script>
<script>
    (function () {
        function renderWidget() {
            if (typeof TradingView === 'undefined') {
                return setTimeout(renderWidget, 200);
            }
            new TradingView.widget({
                autosize: true,
                symbol: @json($symbol),
                interval: '60',
                timezone: 'Etc/UTC',
                theme: 'dark',
                style: '1',
                locale: 'en',
                toolbar_bg: '#0E1422',
                enable_publishing: false,
                allow_symbol_change: true,
                hide_top_toolbar: false,
                hide_legend: false,
                save_image: false,
                backgroundColor: '#0E1422',
                gridColor: 'rgba(38, 48, 68, 0.5)',
                container_id: @json($widgetId),
            });
        }
        renderWidget();
    })();
</script>
