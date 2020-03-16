<div class="card rounded-0 py-1 px-0 sticky-top" id="pair-card" data-pair-id="0">
  	<div class="card-body mx-0 py-1 p-md-2  current-pair-info ">
        <div class="row mx-0">
            <div class="col-12 col-lg-2 d-flex justify-content-center">
                <div class="_symbol align-self-center px-1">
                    <span class="fa-1x fa-star fa _{{$pair_id}}_value_starred font-25 mr-2"></span>
                    <h5 class="lead pair-name d-inline-block _value_pair_label font-25"></h5>
                </div>
            </div>
            <div class="col-12 col-lg-10 p-0">
                <div class="row">
                    <div class="col-6 col-lg-2 text-left text-md-center align-items-center align-self-start align-self-md-center px-1 key-value">
                        <div class="col-label d-md-block font-11 text-secondary">{{__('Last Price')}} (<span class="_value_base"></span>)</div>
                        <div class="d-md-block d-inline-block _value_trade_depth"></div>
                    </div>
                    <div class="col-6 col-lg-2 text-left text-md-center align-items-center align-self-start align-self-md-center px-1 key-value">
                        <div class="col-label d-md-block font-11 text-secondary">{{__('Last Price (USD)')}}</div>
                        <div class="d-md-block d-inline-block _value_trade_depth_usd"></div>
                    </div>
                    <div class="col-6 col-lg-2 text-left text-md-center align-items-center align-self-start align-self-md-center px-1 key-value">
                        <div class="col-label d-md-block font-11 text-secondary">{{__('24H Change')}}</div>
                        <div class="d-md-block _value_24hour_change"></div>
                    </div>
                    <div class="col-6 col-lg-2 text-left text-md-center align-items-center align-self-start align-self-md-center px-1 key-value">
                        <div class="col-label d-md-block font-11 text-secondary">{{__('24H Highest')}}</div>
                        <div class="d-inline-block _value_24hour_highest"></div>
                        <div class="d-inline-block _value_base"></div>
                    </div>
                    <div class="col-6 col-lg-2 text-left text-md-center align-items-center align-self-start align-self-md-center px-1 key-value">
                        <div class="col-label d-md-block font-11 text-secondary">{{__('24H Lowest')}}</div>
                        <div class="d-inline-block _value_24hour_lowest"></div>
                        <div class="d-inline-block _value_base"></div>
                    </div>
                    <div class="col-6 col-lg-2 text-left text-md-center align-items-center align-self-start align-self-md-center px-1 key-value">
                        <div class="col-label d-md-block font-11 text-secondary">{{__('24H Volume')}}</div>
                        <div class="d-inline-block _value_24hour_volume"></div>
                        <div class="d-inline-block _value_target"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
(function($) {

    $.fn.PairWidget = function(params) {
        var widget = this;
        var opt = $.extend({
            pairId: 0,
            pairInfoUrl: ''
        }, params);

        widget.setData = function(data) {
            if (typeof data != 'undefined') {
                widget.find('._value_pair_id').data('pair-id', data.pair_id);

                widget.find(`._${opt.pairId}_value_starred`).removeClass('text-warning');
                if (data.starred) {
                    widget.find(`._${opt.pairId}_value_starred`).addClass('text-warning');
                }

                widget.setType(data.type);
                
                var change = parseFloat(data.h24_change.replace('%', ''));
                widget.find('._value_24hour_change').removeClass('text-danger').addClass('text-success');
                if (change < 0.00) {
                    widget.find('._value_24hour_change').removeClass('text-success').addClass('text-danger');
                }

                widget.find('._value_base').text(data.base);
                widget.find('._value_target').text(data.coin);
                widget.find('._value_pair_label').text(data.coin + ' / ' + data.base);
                widget.find('._value_trade_depth').text(data.price);
                widget.find('._value_trade_depth_usd').text(data.price_usd);
                widget.find('._value_24hour_change').text(data.h24_change + '');
                widget.find('._value_24hour_highest').text(data.h24_high);
                widget.find('._value_24hour_lowest').text(data.h24_low);
                widget.find('._value_24hour_volume').text(data.h24_volume);
                // widget.find('._value_24hour_value').text(' / ' + data.h24_value);
                var title = (data.price*1)+' | '+data.coin + '/' + data.base +' | Buzzex';
                document.title = title; 
            }
        }
        widget.init = function() {
            if (opt.pairInfoUrl !== '' && opt.pairId > 0) {
                $.get(opt.pairInfoUrl, {
                        pair_id: opt.pairId
                    })
                    .done(function(data) {
                        widget.setData(data);
                        widget.listenForPairStatUpdatedEvents(opt.pairId);
                    })
                    .fail(function(e) {
                        // console.error(opt.pairInfoUrl + " request failed.");
                    })
            } else {
                // console.error('ERROR: pairInfoUrl is empty or pairId is empty');
            }
        }

        widget.listenForPairStatUpdatedEvents = function(pairId) {
            window.Echo.channel('PairStatsChannel_' + pairId)
                .listen('ExchangePairStatUpdatedEvent', function(data) {
                    // console.log('ExchangePairStatUpdatedEvent', data)
                    widget.setData(data);
                });
        }

        widget.setType = function(type) {
            widget.find('._value_trade_depth').removeClass('text-success').removeClass('text-danger');
            if (type == 'BUY') {
                widget.find('._value_trade_depth').addClass('text-success');
            } else {
                widget.find('._value_trade_depth').addClass('text-danger');
            }
        }

        return widget;
    }

}(jQuery));

$(document).ready(function() {
    window.currentPairWidget = $('#pair-card').PairWidget({
        pairInfoUrl: "{{route('pairInfo')}}",
        pairId: '{{$pair_id}}'
    });

    window.currentPairWidget.init();
});
</script>
@endpush