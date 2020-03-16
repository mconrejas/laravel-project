<div class="card rounded-0" id="trading-chart-card" style="max-height: 380px;">
  <div id="ohlcv-container-tv" class="mh-100"></div>
</div>

@push('scripts')
<script type="text/javascript" src="{{ asset('vendor/tradingview/charting_library/charting_library.min.js') }}?v=2"></script>
<script type="text/javascript">
$(function() {
    // TradingView.onready(function () {
        var chartwidget = $("#trading-chart-card").TradingViewWidget({
            container_id: 'ohlcv-container-tv',
            debug: false,
            height: '380px',
            pair_id: '{{$pair_id}}',
            last_fid: 0,
            symbol: '{{$target}}/{{$base}}',
            ohlcv_baseUrl: "{{route('tradingBars')}}",
            serverTimeUrl: "{{route('servertime')}}",
            theme: '{{ ucfirst($user_theme) }}'
        });
        chartwidget.init();
    // });
});
</script>
@endpush