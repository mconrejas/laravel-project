<div class="card pt-3 px-1 rounded-0 border-0" id="trading-form-card">
    @includeWhen(!auth()->check(), 'partials.unlogin-mask')
    <ul class="nav nav-pills mb-3  border-bottom" id="pills-tab" role="tablist">
        <li class="nav-item {{ $limitTypeIsDisabled ? 'disabled' : '' }}">
            <a class="btn btn-sm rounded-0 mx-1 nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home"
                title="{{ __('A limit order is a buy or sell order with a specified price.') }}" rel="tooltip"
            role="tab" aria-controls="pills-home" aria-selected="true">{{__('Limit')}}</a>
        </li>
        <li class="nav-item {{ $marketTypeIsDisabled ? 'd-none' : '' }}" >
            <a class="btn btn-sm rounded-0 mx-1 nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile"
                rel="tooltip" title="{{ __('A market order is a buy or sell order that gets
                executed immediately at current market prices. Note that there may be a variation in the prices of the trades for your market order.') }}"
                role="tab" aria-controls="pills-profile" aria-selected="false">
                {{__('Market')}}
            </a>
        </li>
        <li class="nav-item {{ $stopLimitTypeIsDisabled ? 'd-none' : '' }}">
            <a class="btn btn-sm rounded-0 mx-1 nav-link" title="{{ __('Stop market order lets you send a market order
            once the market price has reached the stop price.') }}." rel="tooltip" id="pills-contact-tab" data-toggle="pill" href="#pills-contact"
                role="tab" aria-controls="pills-contact" aria-selected="false">
                {{__('Stop Limit')}}
            </a>
        </li>
        <!-- <li class="nav-item align-self-center" id="trade-fee-label">
            <a target="_blank" href="{{ $buzzexLinks->fees->url }}"><small title="{{__('Based on BZX balance.')}}" rel="tooltip">{{ __('Trading Fee Discount')}} : {{$user_discount_percentage ?? 0 }}%</small></a>
        </li> -->
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <!-- LIMIT -->
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <div class="card-group" data-group="limit">
                <div class="card rounded-0 border-0">
                    <div class="base-balance d-flex px-2">
                        <h6 class="flex-grow-1 text-secondary base-balance-label pointer-cursor">
                        {{__('Balance')}} : <span class="font-weight-bold">0</span> <b class="base-coin"></b>
                        </h6>
                        <a class="btn-link text-secondary font-14 text-buzzex"
                            href="{{route('my.depositForm',['coin'=>$base])}}">{{__('Deposit')}} »
                        </a>
                    </div>
                    <div class="card-block p-2">
                        <form method="POST" data-action="buy">
                            <input type="hidden" name="margin" value="0">
                            <input type="hidden" name="module" value="">
                            <input type="hidden" name="orig_price" value="">
                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Price')}} </span>
                                </div>
                                <input type="text" class="input-buy-price form-control border-0 numeric field-buzzex rounded-0"
                                name="price">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Amount')}}</span>
                                </div>
                                <input type="text" class="input-buy-amount form-control border-0 numeric field-buzzex rounded-0"
                                value="0.00000000" name="amount">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 target-coin"></span>
                                </div>
                            </div>

                            <div class="input-group px-2 py-1 mb-2">
                                <input id="sliderbuy_limit" class="percent-slider slider-buy mx-auto" type="hidden" value="0">
                            </div>

                            <div class="input-group border mb-2 buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span rel="tooltip" data-placement="left" title="{{$fee_percentage ?? 0}}%" class="input-group-text bg-light border-0 rounded-0">{{__('Fee')}}</span>
                                </div>
                                <input readonly type="text" class="input-buy-fee form-control border-0 numeric field-buzzex border rounded-0" value="0.00000000" disabled>
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Total')}}</span>
                                </div>
                                <input type="text" class="input-buy-total form-control border-0 numeric field-buzzex rounded-0" value="0.00000000" name="{{uniqid()}}" placeholder="0.00000000">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group border mb-2 buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span rel="tooltip" class="input-group-text bg-light border-0 rounded-0" data-placement="left" title="{{__('Total + Fee')}}">{{__('Cost')}}</span>
                                </div>
                                <input type="text" class="input-total-buy-amount form-control border-0 numeric field-buzzex rounded-0" value="0.00000000">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="error-holder mt-1"></div>
                            <button class="btn btn-success btn-block btn-buy text-capitalize" type="button">{{__('Buy')}}
                            <span class="target-coin"></span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card rounded-0 border-0">
                    <div class="target-balance d-flex px-2">
                        <h6 class="flex-grow-1 text-secondary target-balance-label pointer-cursor">
                        {{__('Balance')}} : <span class="font-weight-bold">0</span> <b class="target-coin"></b>
                        </h6>
                        <a class="btn-link text-secondary font-14 text-buzzex"
                            href="{{route('my.depositForm',['coin'=>$target])}}">{{__('Deposit')}} »
                        </a>
                    </div>
                    <div class="card-block p-2">
                        <form method="POST" data-action="sell">
                            <input type="hidden" name="margin">
                            <input type="hidden" name="module">
                            <input type="hidden" name="orig_price" value="">
                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Price')}}</span>
                                </div>
                                <input type="text" class="input-sell-price form-control border-0 numeric field-buzzex rounded-0"
                                name="price">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Amount')}}</span>
                                </div>
                                <input type="text" class="input-sell-amount form-control border-0 numeric field-buzzex rounded-0"
                                value="0.00000000" name="amount">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 target-coin"></span>
                                </div>
                            </div>

                            <div class="input-group px-2 py-1 mb-2">
                                <input id="slidersell_limit" class="percent-slider slider-sell mx-auto" type="hidden" value="0">
                            </div>

                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span rel="tooltip" data-placement="left" title="{{$fee_percentage ?? 0}}%" class="input-group-text bg-light border-0 rounded-0">{{__('Fee')}}</span>
                                </div>
                                <input readonly type="text" class="input-sell-fee form-control border-0 numeric field-buzzex border rounded-0" value="0.00000000" disabled>
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Total')}}</span>
                                </div>
                                <input type="text" class="input-sell-total form-control border-0 numeric field-buzzex rounded-0" value="0.00000000" name="{{uniqid()}}" placeholder="0.00000000">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-2 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span rel="tooltip" class="input-group-text bg-light border-0 rounded-0" data-placement="left" title="{{__('Total - Fee')}}">{{__('Cost')}}</span>
                                </div>
                                <input type="text" class="input-total-sell-amount form-control border-0 numeric field-buzzex rounded-0" value="0.00000000" name="{{uniqid()}}">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="error-holder mt-1"></div>
                            <button class="btn btn-danger btn-block btn-sell text-capitalize" type="button">{{__('Sell')}}
                            <span class="target-coin"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- MARKET -->
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
            <div class="card-group" data-group="market">
                <div class="card rounded-0 border-0">
                    <div class="base-balance d-flex px-2">
                        <h6 class="flex-grow-1 text-secondary base-balance-label pointer-cursor">
                        {{__('Balance')}} : <span class="font-weight-bold">0</span><b class="base-coin"></b>
                        </h6>
                        <a class="btn-link text-secondary font-14 text-buzzex"
                            href="{{route('my.depositForm',['coin'=>$base])}}">{{__('Deposit')}}
                        »</a>
                    </div>
                    <div class="card-block p-2">
                        <form method="POST" data-action="buy">
                            <input type="hidden" name="margin">
                            <input type="hidden" name="module">
                            <input type="hidden" name="orig_price" value="">
                            <div class="input-group my-2">
                                <p class="lead font-15">{{__('Buy in the order of current selling price')}}</p>
                            </div>
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Amount')}}</span>
                                </div>
                                <input type="text" class="input-buy-amount form-control border-0 numeric field-buzzex rounded-0"
                                value="0.00000000" name="amount">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="error-holder mt-1"></div>
                            <div class="input-group px-2 py-2 my-2">
                                <input id="sliderbuy_market" class="percent-slider slider-buy mx-auto" type="hidden" value="0">
                            </div>
                            <button class="btn btn-success btn-block btn-buy text-capitalize" type="button">{{__('Buy')}}
                                <span class="target-coin"></span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card rounded-0 border-0">
                    <div class="target-balance d-flex px-2">
                        <h6 class="flex-grow-1 text-secondary target-balance-label pointer-cursor">
                        {{__('Balance')}} : <span class="font-weight-bold">0</span><b class="target-coin"></b>
                        </h6>
                        <a class="btn-link text-secondary font-14 text-buzzex"
                            href="{{route('my.depositForm',['coin'=>$target])}}">{{__('Deposit')}}
                        »</a>
                    </div>
                    <div class="card-block p-2">
                        <form method="POST" data-action="sell">
                            <input type="hidden" name="margin">
                            <input type="hidden" name="module">
                            <input type="hidden" name="orig_price" value="">
                            <div class="input-group my-2">
                                <p class="lead font-15">{{__('Sell in the order of current buying price')}}</p>
                            </div>
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Amount')}}</span>
                                </div>
                                <input type="text" class="input-sell-amount form-control border-0 numeric field-buzzex rounded-0"
                                value="0.00000000" name="amount">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 target-coin"></span>
                                </div>
                            </div>
                            <div class="error-holder mt-1"></div>
                            <div class="input-group px-2 py-2 my-2">
                                <input id="slidersell_market" class="percent-slider slider-sell mx-auto" type="hidden" value="0">
                            </div>
                            <button class="btn btn-danger btn-block btn-sell text-capitalize" type="button">{{__('Sell')}}
                                <span class="target-coin"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- STOP LIMIT -->
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
            <div class="card-group" data-group="stop-limit">
                <div class="card rounded-0 border-0">
                    <div class="base-balance d-flex px-2">
                        <h6 class="flex-grow-1 text-secondary">
                        {{__('Balance')}} : <span>0 </span><b class="base-coin"></b>
                        </h6>
                        <a class="btn-link text-secondary font-14 text-buzzex"
                            href="{{route('my.depositForm',['coin'=>$base])}}">{{__('Deposit')}}
                        »</a>
                    </div>
                    <div class="card-block p-2">
                        <form method="POST" data-action="buy">
                            <input type="hidden" name="margin">
                            <input type="hidden" name="module">
                            <input type="hidden" name="orig_price" value="">
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Stop')}}</span>
                                </div>
                                <input type="text" class="form-control border-0 numeric field-buzzex border rounded-0" name="stop">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Limit')}}</span>
                                </div>
                                <input type="text" class="form-control border-0 numeric field-buzzex rounded-0" name="limit">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Amount')}}</span>
                                </div>
                                <input type="text" class="input-buy-amount form-control border-0 numeric field-buzzex rounded-0"
                                value="0.00000000" name="amount">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 target-coin"></span>
                                </div>
                            </div>
                            <div class="error-holder my-1"></div>
                            <button class="btn btn-success btn-block btn-buy text-capitalize" type="button">{{__('Buy')}}
                            <span class="target-coin"></span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card rounded-0 border-0">
                    <div class="target-balance d-flex px-2">
                        <h6 class="flex-grow-1 text-secondary">
                        {{__('Balance')}} : <span class="font-weight-bold">0</span><b class="target-coin"></b>
                        </h6>
                        <a class="btn-link text-secondary font-14 text-buzzex"
                            href="{{route('my.depositForm',['coin'=>$target])}}">{{__('Deposit')}}
                        »</a>
                    </div>
                    <div class="card-block p-2">
                        <form method="POST" data-action="sell">
                            <input type="hidden" name="margin">
                            <input type="hidden" name="module">
                            <input type="hidden" name="orig_price" value="">
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Stop')}}</span>
                                </div>
                                <input type="text" class="form-control border-0 numeric field-buzzex rounded-0" name="stop">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Limit')}}</span>
                                </div>
                                <input type="text" class="form-control border-0 numeric field-buzzex rounded-0" name="limit">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 base-coin"></span>
                                </div>
                            </div>
                            <div class="input-group mb-3 border buzzex-input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0 rounded-0">{{__('Amount')}}</span>
                                </div>
                                <input type="text" class="input-sell-amount form-control border-0 numeric field-buzzex rounded-0"
                                value="0.00000000" name="amount">
                                <div class="input-group-append ">
                                    <span class="input-group-text border-0 bg-transparent rounded-0 target-coin"></span>
                                </div>
                            </div>
                            <div class="error-holder my-1"></div>
                            <button class="btn btn-danger btn-block btn-sell text-capitalize" type="button">{{__('Sell')}}
                            <span class="target-coin"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
b.base-coin, b.target-coin {
     font-weight: normal;
     font-size: 14px;
     margin-left: 4px;
     margin-right: 1px;
     color: #898989;
}
 .slider-container {
     min-width: 300px;
     width: 300px;
}
 #trading-form-card .nav-item .nav-link:hover {
     background-image: linear-gradient(270deg, #00c1ce, #22e6b8);
}
 #trading-form-card .nav-item .nav-link.active:focus, #trading-form-card .nav-item .nav-link:hover {
     box-shadow: none;
}
 #trading-form-card .nav-item .nav-link.active {
     background-image: linear-gradient(270deg, #22e6b8, #00c1ce);
}
 #trading-form-card #trade-fee-label {
     position: absolute;
     right: 12px;
}
 .btn-buy {
     background-image: linear-gradient(270deg, #189074, #0ee3f3);
}
 .btn-sell {
     background-image: linear-gradient(270deg, #f57482, #fe0b3e);
}
.error-holder {
    text-align: right;
    height: 15px;
}
.error-holder span{
    font-size: 0.8rem;
}

</style>

@push('scripts')

<script type="text/javascript">
var pair_id = parseInt('{{ $pair_id }}');

(function($) {

    $.fn.inputGroup = function() {
        var _this = this;
        var textfield = _this.find('input');
        _this.find('.input-group-prepend').on('click', 'span', function() {
            textfield.focus();
            //add other events listener here
        });
        return _this;
    };

    $.fn.TradingForm = function(param) {
        var widget = this;
        widget.opt = $.extend({
            baseCoin: '',
            baseBalance: 0.00,
            targetCoin: '',
            targetBalance: 0.00,
            price: 0.00,
            buyPrice: 0.00,
            sellPrice: 0.00,
            priceUsd: 0.00,
            formUrl: '',
            maxAmount: 0.001,
            minAmount: 0.001,
            maxPrice: 0.001,
            minPrice: 0.001,
            minCost: 0.001,
            totalBuyAmount: 0.00,
            totalSellAmount: 0.00,
            maxAmountLabel: "Maximum amount",
            minAmountLabel: "Minimum amount",
            maxPriceLabel: "Maximum price",
            minPricetLabel: "Minimum price",
            minCostLabel : "Minimum cost",
            insufficientBalanceLabel: "Insufficient balance",
            fee_percentage: 0.00,
            userPercentDiscount : 0,
            userHas2FAEnabled: 0,
            module:'',
            default_local_qty : 100000
        }, param);

        if (widget.data('binded') == 'trading-form-widget') {
            widget.destroy();
        }

        widget.find(".buzzex-input-group").each(function() {
            $(this).inputGroup();
        });

        /**
         * Bind events to buy input fields
         */
        var sellInput = widget.find('.input-buy-amount');

        if (sellInput.length > 0) {
            sellInput.each(function() {
                $(this).on('input change paste keyup', function() {
                    var input = $(this);
                    var value = input.val();
                    var balance = widget.opt.targetBalance;
                    input.parents('.input-group').css({
                        'border-color': value > balance ? '#dc3545' : '#dee2e6'
                    }) //red //none
                })
            });
        }

        var rangeWidth = widget.find(".percent-slider:first").parents('.input-group').width();

        var disabledSlider = function(isBuy){
            if ( (isBuy && widget.opt.baseBalance <= 0) || (isBuy === false && widget.opt.targetBalance <= 0) ) {
                return false;
            }
            return true;
        }

        var initSlider = function(el) {
            var range = $(el);
            var isBuy = range.hasClass('slider-buy');
            var form = range.parents('form');
            var isDisabledSlider = disabledSlider(isBuy);

            var slider = range.bootstrapSlider({
                ticks : [0, 25, 50, 75, 100],
                ticks_labels : ['0%', '25%', '50%', '75%', '100%'],
                ticks_snap_bounds : 0,
                min : 0,
                max : 100,
                step: 1,
                value : 0,
                tooltip_position : 'top',
                ticks_tooltip : true,
                enabled : isDisabledSlider
            });

            slider.on('slide change', function(e){
                var value = (e.type == 'change') ? e.value.newValue : e.value;
                var balance = isBuy ? widget.opt.baseBalance : widget.opt.targetBalance;
                var result = (value / 100) * balance;
                result = result.toFixed(8);
                if (isBuy) {
                    if (form.parents('.card-group').data('group') === 'limit') {
                        var buyPrice = Number(form.find('.input-buy-price').val());
                        if (!isNaN(buyPrice) && buyPrice > 0) {
                            var orderAmount = widget.getAmountViaBalance(result,buyPrice);
                            result = Number(orderAmount).toFixed(8);
                        }
                        form.find('.input-buy-amount').val(result)
                        widget.calculateBuy(form.find('.input-buy-amount'));
                    }
                    if (form.parents('.card-group').data('group') === 'market') {
                        form.find('.input-buy-amount').val(result).keyup();
                    }
                } else {
                    form.find('.input-sell-amount').val(result)
                    widget.calculateSell(form.find('.input-sell-amount'));
                }
                //window.tradingForm.setModule('');
            });
            return slider;
        };
        widget.getAmountViaBalance = function(balance, price) {
            var discounted_fee = (widget.opt.fee_percentage - (widget.opt.fee_percentage * (widget.opt.userPercentDiscount/100)));
            var fee_amount = balance / (balance  * discounted_fee);
            var total =  balance / (100 + discounted_fee) * 100;
            var amount = total / price;
            var decimal_pts = String(amount).indexOf(".");
            return String(amount).slice(0, decimal_pts + 9);
        }
        widget.find(".percent-slider").each(function() {
            var _this = this;
            var sliderId = $(this).attr('id');
            setTimeout(function() {
                window[sliderId] = initSlider(_this);
            }, 0);
        });

        widget.getPercentage = function(type, amount) {
            var balance = type == 'buy' ? widget.opt.baseBalance : widget.opt.targetBalance;
            return (amount / balance ) * 100;
        };
        
        widget.setPercentage = function(el, type) {
            var amount = $(el).val();
            if ($(el).parents('.card-group').data('group') == 'limit') {
                amount = type == 'buy' ? $('.input-total-buy-amount').val() : amount;
            }
            var slider = $(el).parents('form').find('.percent-slider');
            if (slider.length > 0) {
                var percentage = widget.getPercentage(type, amount);
                var sliderId = slider.attr('id');
                window[sliderId].bootstrapSlider('setValue', percentage, false, false);
            }
        };

        widget.init = function() {

            window.input_price = parseFloat(widget.opt.buyPrice).toFixed(8);

            widget.find('.target-balance span').text(parseFloat(widget.opt.targetBalance).toFixed(8));
            widget.find('.target-coin').text(widget.opt.targetCoin);
            widget.find('.base-balance span').text(parseFloat(widget.opt.baseBalance).toFixed(8));
            widget.find('.base-coin').text(widget.opt.baseCoin);
            widget.find('.input-buy-price').val(parseFloat(widget.opt.buyPrice).toFixed(8));
            widget.find('.input-sell-price').val(parseFloat(widget.opt.sellPrice).toFixed(8));
            widget.find('input[name="limit"]').val(parseFloat(widget.opt.price).toFixed(8));
            widget.find('input[name="stop"]').val(parseFloat(widget.opt.price).toFixed(8));

            widget.reset();
        };

        widget.find('.input-buy-price').on('focusout', function(e) {
            if (widget.validate($(this).parents('form'))) {
                widget.removeErrors('buy');
                widget.setPercentage(this,'buy');
            }
        }).on('keyup', function(e) {
            widget.calculateBuy(this)
            widget.setPercentage(this,'buy');

            if(window.input_price != widget.find('.input-buy-price').val()){
                widget.setModule({local:widget.opt.default_local_qty});
            }
            
        });

        widget.find('.input-buy-amount').on('focusout', function(e) {
            if (widget.validate($(this).parents('form'))) {
                widget.removeErrors('buy');
                widget.setPercentage(this,'buy');
            }
        }).on('keyup', function(e) {
            widget.calculateBuy(this)
            widget.setPercentage(this,'buy');
        });

        widget.find('.input-sell-amount').on('focusout', function(e) {
            if (widget.validate($(this).parents('form'))) {
                widget.removeErrors('sell');
                widget.setPercentage(this,'sell');
            }
        }).on('keyup', function(e) {
            widget.calculateSell(this)
            widget.setPercentage(this,'sell');
        });

        widget.find('.input-sell-price').on('focusout', function(e) {
            if (widget.validate($(this).parents('form'))) {
                widget.removeErrors('sell');
                widget.setPercentage(this,'sell');
            }
        }).on('keyup', function(e) {
            widget.calculateSell(this)
            widget.setPercentage(this,'sell');
        });

        widget.find('.input-buy-total').on('keyup focusout', function(e) {

            var form = $(this).parents('form');
            var total = parseFloat($(this).val());
            var price = parseFloat(form.find('.input-buy-price').val() || 0);
            var fee_amount = widget.calculateFee(total);
            var final_total = total + fee_amount;
            var amount = total / price;
            form.find('.input-buy-fee').val(fee_amount.toFixed(8));
            form.find('.input-buy-amount').val(amount.toFixed(8));
            form.find('.input-total-buy-amount').val(final_total.toFixed(8));
            widget.setPercentage(this,'buy');
            widget.limitValidation(form);
        });

        widget.find('.input-total-buy-amount').on('keyup', function(e) {
            widget.calculateBuyTotalPlusFee(this)
            widget.setPercentage(this,'buy');
        });

        widget.find('.input-sell-total').on('keyup focusout', function(e) {

            var form = $(this).parents('form');
            var total = parseFloat($(this).val());
            var price = parseFloat(form.find('.input-sell-price').val() || 0);
            var fee_amount = widget.calculateFee(total);
            var final_total = total - fee_amount;
            var amount = total / price;
            form.find('.input-sell-fee').val(fee_amount.toFixed(8));
            form.find('.input-sell-amount').val(amount.toFixed(8));
            form.find('.input-total-sell-amount').val(final_total.toFixed(8));
            widget.setPercentage(this,'sell');
            widget.limitValidation(form);
        });

        widget.find('.input-total-sell-amount').on('keyup', function(e) {
            widget.calculateSellTotalPlusFee(this)
            widget.setPercentage(this,'sell');
        });

        widget.calculateFee = function(total){
            var fee_amount = 0

            if (widget.opt.fee_percentage > 0) {
                fee_amount = total * (widget.opt.fee_percentage / 100);
            }
            if (fee_amount > 0 && widget.opt.userPercentDiscount > 0) {
                fee_amount =  fee_amount - (fee_amount * (widget.opt.userPercentDiscount / 100 ));
            }
            return fee_amount;
        };

        widget.calculateBuy = function(input){
            var form = $(input).parents('form');

            if (form.parents('.card-group').data('group') == 'limit') {
                var price = parseFloat(form.find('.input-buy-price').val());
                if (!isNaN(price) && price > 0) {
                    var amount = parseFloat(form.find('.input-buy-amount').val());
                    var total = amount * price;
                    var fee_amount = widget.calculateFee(total);
                    var final_total = total + fee_amount;

                    form.find('.input-buy-fee').val(fee_amount.toFixed(8));
                    form.find('.input-buy-total').val(total.toFixed(8));
                    form.find('.input-total-buy-amount').val(final_total.toFixed(8));
                }
            }

            widget.validate(form);
        };

        widget.calculateBuyTotalPlusFee = function(input){
            var form = $(input).parents('form');

            if (form.parents('.card-group').data('group') == 'limit') {
                var price = parseFloat(form.find('.input-buy-price').val());
                if (!isNaN(price) && price > 0) {
                    var total = parseFloat($(input).val());
                    var fee_amount = widget.calculateFee(total);
                    var final_total = (total - fee_amount) / price;

                    form.find('.input-buy-amount').val(final_total.toFixed(8));
                    form.find('.input-buy-fee').val(fee_amount.toFixed(8));
                }
            }

            widget.validate(form);
        };

        widget.calculateSell = function(input){
            var form = $(input).parents('form');

            if (form.parents('.card-group').data('group') == 'limit') {
                var price = parseFloat(form.find('.input-sell-price').val());
                if (!isNaN(price) && price > 0) {
                    var amount = parseFloat(form.find('.input-sell-amount').val());
                    var total = amount * price;
                    var fee_amount = widget.calculateFee(total);
                    var final_total = total - fee_amount;

                    form.find('.input-sell-fee').val(fee_amount.toFixed(8));
                    form.find('.input-sell-total').val(total.toFixed(8));
                    form.find('.input-total-sell-amount').val(final_total.toFixed(8));
                }
            }
            widget.validate(form);
        };

        widget.calculateSellTotalPlusFee = function(input){
            var form = $(input).parents('form');

            if (form.parents('.card-group').data('group') == 'limit') {
                var price = parseFloat(form.find('.input-buy-price').val());
                if (!isNaN(price) && price > 0) {
                    var total = parseFloat($(input).val());
                    var fee_amount = widget.calculateFee(total);
                    var final_total = (total - fee_amount) / price;

                    form.find('.input-sell-amount').val(final_total.toFixed(8));
                    form.find('.input-sell-fee').val(fee_amount.toFixed(8));
                }
            }

            widget.validate(form);
        };

        widget.removeErrors = function(type){
            if (type == 'buy') {
                widget.find('.input-buy-amount').removeClass('is-invalid');
                widget.find('.min-buy-error').remove();
            } else {
                widget.find('.input-sell-amount').removeClass('is-invalid');
                widget.find('.min-sell-error').remove();
            }
        };

        widget.setErrors = function(form, html){
            if($(form).find('.error-holder').length > 0) {
                $(form).find('.error-holder').html(html);
            }
        };

        widget.reset = function() {
            widget.find('.input-buy-amount').val('0');
            widget.find('.input-sell-amount').val('0');
            widget.find('.input-total-sell-amount').val('0');
            widget.find('.input-total-buy-amount').val('0');
            widget.find('.input-sell-fee').val('0');
            widget.find('.input-buy-fee').val('0');
            widget.find('.input-buy-total').val('0');
            widget.find('.input-sell-total').val('0');
            widget.find('.input-buy-amount').removeClass('is-invalid');
            widget.find('.min-buy-error').remove();
            widget.find('.min-sell-error').remove();
            widget.find(".percent-slider").each(function() {
                var slider = $(this);
                var sliderId = slider.attr('id');
                if (typeof window[sliderId].bootstrapSlider == 'function') {
                    window[sliderId].bootstrapSlider('setValue', 0, false, false);
                }
            });

            widget.setModule({local: widget.opt.default_local_qty});
        };

        widget.limitValidation = function(form) {
            var form = $(form);
            var type = form.data('action');
            widget.removeErrors(type);

            if (type == 'buy') {
                var price = parseFloat(form.find('.input-buy-price').val());
                if (isNaN(price) || price < parseFloat(widget.opt.minPrice)) {
                    //form.find('.input-buy-price').focus();
                    widget.setErrors(form,'<span class="min-buy-error">' + widget.opt.minPriceLabel + ' : ' + widget.opt.minPrice + '</span>');
                    return false;
                }

                if (isNaN(price) || price > parseFloat(widget.opt.maxPrice)) {
                    //form.find('.input-buy-price').focus();
                    widget.setErrors(form,'<span class="min-buy-error">' + widget.opt.maxPriceLabel + ' : ' + widget.opt.maxPrice + '</span>');
                    return false;
                }

                var final_total = parseFloat(form.find('.input-total-buy-amount').val());
                if (final_total > parseFloat(widget.opt.baseBalance) ) {
                    widget.setErrors(form, '<span class="min-buy-error">' + widget.opt.insufficientBalanceLabel.replace('{coin}', widget.opt.baseCoin) + '</span>');
                    return false;
                }

                var amount = parseFloat(form.find('.input-buy-amount').val());
                if (amount < parseFloat(widget.opt.minAmount) ) {
                    widget.setErrors(form, '<span class="min-buy-error">' + widget.opt.minAmountLabel + ' : ' + widget.opt.minAmount + '</span>');
                    return false;
                }

                if (amount > parseFloat(widget.opt.maxAmount) ) {
                    widget.setErrors(form, '<span class="min-buy-error">' + widget.opt.maxAmountLabel + ' : ' + widget.opt.maxAmount + '</span>');
                    return false;
                }

                var total = parseFloat(form.find('.input-buy-total').val());
                if (total < parseFloat(widget.opt.minCost) ) {
                    widget.setErrors(form, '<span class="min-buy-error">' + widget.opt.minCostLabel + ' : ' + widget.opt.minCost + '</span>');
                    return false;
                }

            } else {
                var price = parseFloat(form.find('.input-sell-price').val());
                if (isNaN(price) || price < widget.opt.minPrice) {
                    //form.find('.input-sell-price').focus();
                    widget.setErrors(form,'<span class="min-sell-error">' + widget.opt.minPriceLabel + ' : ' + widget.opt.minPrice + '</span>');
                    return false;
                }

                if (isNaN(price) || price > widget.opt.maxPrice) {
                    //form.find('.input-sell-price').focus();
                    widget.setErrors(form,'<span class="min-sell-error">' + widget.opt.maxPriceLabel + ' : ' + widget.opt.maxPrice + '</span>');
                    return false;
                }

                var amount = parseFloat(form.find('.input-sell-amount').val());
                if (amount > parseFloat(widget.opt.targetBalance) ) {
                    widget.setErrors(form, '<span class="min-sell-error">' + widget.opt.insufficientBalanceLabel.replace('{coin}', widget.opt.targetCoin) + '</span>');
                    return false;
                }

                if (amount < parseFloat(widget.opt.minAmount)) {
                    widget.setErrors(form, '<span class="min-sell-error">' + widget.opt.minAmountLabel + ' : ' + widget.opt.minAmount + '</span>');
                    return false;
                }

                if (amount > parseFloat(widget.opt.maxAmount)) {
                    widget.setErrors(form, '<span class="min-sell-error">' + widget.opt.maxAmountLabel + ' : ' + widget.opt.maxAmount + '</span>');
                    return false;
                }

                var total = parseFloat(form.find('.input-sell-total').val());
                if (total < parseFloat(widget.opt.minCost) ) {
                    widget.setErrors(form, '<span class="min-sell-error">' + widget.opt.minCostLabel + ' : ' + widget.opt.minCost + '</span>');
                    return false;
                }
            }

            widget.removeErrors(type);
            return true;
        };

        widget.marketValidation = function(form) {
            var form = $(form);
            var type = form.data('action');

            if (type == 'buy') {
                widget.removeErrors('buy');
                var buyAmount = parseFloat(form.find('.input-buy-amount').val());

                if (buyAmount < parseFloat(widget.opt.minAmount)) {
                    form.find('.input-buy-amount').addClass('is-invalid');
                    widget.setErrors(form,'<span class="min-buy-error">' + widget.opt.minAmountLabel + ' ' + widget.opt.minAmount + '</span>');
                    return false;
                }

                if (buyAmount > parseFloat(widget.opt.baseBalance)) {
                    form.find('.input-buy-amount').addClass('is-invalid');
                    widget.setErrors(form, '<span class="min-buy-error">' + widget.opt.insufficientBalanceLabel.replace('{coin}', widget.opt.baseCoin) + '</span>');
                    return false;
                }

            } else {
                widget.removeErrors('sell');
                var sellAmount = parseFloat(form.find('.input-sell-amount').val());

                if (sellAmount < parseFloat(widget.opt.minAmount )) {
                    form.find('.input-sell-amount').addClass('is-invalid');
                    widget.setErrors(form,'<span class="min-sell-error">' + widget.opt.minAmountLabel + ' ' + widget.opt.minAmount + '</span>');
                    return false;
                }
                
                if (sellAmount > parseFloat(widget.opt.targetBalance)) {
                    form.find('.input-sell-amount').addClass('is-invalid');
                    widget.setErrors(form, '<span class="min-sell-error">' + widget.opt.insufficientBalanceLabel.replace('{coin}', widget.opt.targetCoin) + '</span>');
                    return false;
                }
            }

            return true;
        };

        widget.stopLimitValidation = function(form) {
            var form = $(form);
            var type = form.data('action');
            var limit = parseFloat(form.find('input[name="limit"]').val());
            var stop = parseFloat(form.find('input[name="stop"]').val());

            if (limit <= 0) {
                form.find('input[name="limit"]').focus();
                return false;
            }

            if (stop <= 0) {
                form.find('input[name="stop"]').focus();
                return false;
            }

            if (type == 'buy') {
                widget.removeErrors('buy');
                var buyAmount = parseFloat(form.find('.input-buy-amount').val());

                if (buyAmount < widget.opt.minAmount) {
                    form.find('.input-buy-amount').addClass('is-invalid');
                    widget.setErrors(form,'<span class="min-buy-error">' + widget.opt.minAmountLabel + ' ' + widget.opt.minAmount + '</span>');
                    return false;
                }
                if (buyAmount > widget.opt.baseBalance) {
                    form.find('.input-buy-amount').addClass('is-invalid');
                    widget.setErrors(form, '<span class="min-buy-error">' + widget.opt.insufficientBalanceLabel.replace('{coin}', widget.opt.baseCoin) + '</span>');
                    return false;
                }
            } else {
                widget.removeErrors('sell');
                var sellAmount = parseFloat(form.find('.input-sell-amount').val());

                if (sellAmount < widget.opt.minAmount ) {
                    form.find('.input-sell-amount').addClass('is-invalid');
                    widget.setErrors(form,'<span class="min-sell-error">' + widget.opt.minAmountLabel + ' ' + widget.opt.minAmount + '</span>');
                    return false;
                }
                if (sellAmount > widget.opt.targetBalance) {
                    form.find('.input-sell-amount').addClass('is-invalid');
                    widget.setErrors(form, '<span class="min-sell-error">' + widget.opt.insufficientBalanceLabel.replace('{coin}', widget.opt.targetCoin) + '</span>');
                    return false;
                }
            }

            return true;
        };

        widget.validate = function(form) {

            if (form.parents('.card-group').data('group') === 'stop-limit') {
                return widget.stopLimitValidation(form);
            } else if (form.parents('.card-group').data('group') === 'market') {
                return widget.marketValidation(form);
            }

            return widget.limitValidation(form);
        };

        widget.processForm = function(form) {
            var form = $(form);
            var button = form.find('button');
            var data = form.serializeArray();

            data.push({
                name: 'action',
                value: form.data('action')
            });
            data.push({
                name: 'form_type',
                value: form.parents('.card-group').data('group')
            });
            data.push({
                name: 'pair_id',
                value: window.pair_id
            });
            button.btnProcessing('Processing...');

            $.post(widget.opt.formUrl, data)
                .done(function(data) {
                    notifications({ message: 'Order submitted.' });
                    widget.reset();
                    window.refreshWall();
                    widget.setBaseBalance(data.base);
                    widget.setTargetBalance(data.target);
                    button.btnReset();
                })
                .fail(function(xhr, status, error) {
                    alert({
                        title: window.Templates.getXHRMessage(xhr),
                        html: window.Templates.getXHRErrors(xhr),
                        type: 'error'
                    });
                    button.btnReset();
                });
        };

        widget.find('form:not(.processing)').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            /*if (widget.opt.userHas2FAEnabled == '1') {
                window.confirm2FA('Enter 2FA code', function() {
                    widget.processForm(form)
                });
            } else {*/
                widget.processForm(form);
            // }
        });

        widget.find('.btn-buy:not(.processing),.btn-sell:not(.processing)').on('click', function(e) {
            var form = $(this).parents('form');
            var isValid = widget.validate(form);

            if (isValid) {
                form.submit();
            }
        });

        widget.destroy = function() {
            widget.unbind().removeData();
        };

        widget.setTargetCoin = function(value) {
            if (typeof value !== 'undefined') {
                widget.opt.targetCoin = value.toUpperCase();
                widget.init();
            }
        };

        widget.setTargetBalance = function(value) {
            if (typeof value !== 'undefined') {
                widget.opt.targetBalance = value.toFixed(8);
                widget.init();
            }
        };

        widget.setBaseCoin = function(value) {
            if (typeof value !== 'undefined') {
                widget.opt.baseCoin = value.toUpperCase();
                widget.init();
            }
        };

        widget.setBaseBalance = function(value) {
            if (typeof value !== 'undefined') {
                widget.opt.baseBalance = value.toFixed(8);
                widget.init();
            }
        };
        
        widget.setBalanceByTicker = function(ticker, balance){
            if (widget.opt.baseCoin == ticker) {
                widget.setBaseBalance(balance);
            }
            if (widget.opt.targetCoin == ticker) {
                widget.setTargetBalance(balance);
            }
        };

        widget.setPrice = function(value) {
            if (typeof value !== 'undefined') {
                widget.opt.buyPrice = parseFloat(value);
                widget.opt.sellPrice = parseFloat(value);
                widget.init();
            }
        };

        widget.setAmount = function(value) {
            if (typeof value !== 'undefined') {

                var buyInput = widget.find('.input-buy-amount');
                buyInput.each(function(){
                    $(this).val(parseFloat(value).toFixed(8));
                    widget.calculateBuy($(this));
                    widget.setPercentage($(this),'buy');
                })

                var sellInput = widget.find('.input-sell-amount');
                sellInput.each(function(){
                    $(this).val(parseFloat(value).toFixed(8));
                    widget.calculateSell($(this));
                    widget.setPercentage($(this),'sell');
                })
            }
        };

        widget.setModule = function(value) {
            if (typeof value !== 'undefined') {
                var moduleInput = widget.find('input[name="module"]');                
                moduleInput.each(function(){
                    $(this).val(JSON.stringify(value)); 
                })
            }
        };

        widget.setMargin = function(value) {
            if (typeof value !== 'undefined') {
                var marginInput = widget.find('input[name="margin"]');
                marginInput.each(function(){
                    $(this).val(value.toFixed(8)); 
                })
            }
        };
        
        widget.setOriginalPrice = function(value){
            if (typeof value !== 'undefined') {
                var origPriceInput = widget.find('input[name="orig_price"]');                
                origPriceInput.each(function(){
                    $(this).val(value); 
                })
            }
        }

        widget.find('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            var slider =  $($(e.target).attr('href')).find('.percent-slider');
            if(slider.length > 0 ){
                slider.bootstrapSlider('relayout');
            }
        });

        widget.find('.base-balance-label').click(function(arg) {
            var inputBuyField = $(this).parents('.card').find('.input-buy-amount');

            if ($(this).parents('.card-group').data('group') === 'limit') {
                if (parseFloat(widget.opt.baseBalance) > 0) {
                    var buyPrice = inputBuyField.parents('form').find('.input-buy-price').val() || 0;
                    var orderAmount = widget.getAmountViaBalance(widget.opt.baseBalance,buyPrice);
                    inputBuyField.val(parseFloat(orderAmount).toFixed(8)).keyup();
                }
            }
            if ($(this).parents('.card-group').data('group') === 'market') {
                inputBuyField.val(parseFloat(widget.opt.baseBalance).toFixed(8)).keyup();
                if (parseFloat(widget.opt.baseBalance) > 0) {
                    window["sliderbuy_market"].bootstrapSlider('setValue', 100, false, false);
                }
            }
        });

        widget.find('.target-balance-label').click(function(arg) {
            var inputSellField = $(this).parents('.card').find('.input-sell-amount');
            if ($(this).parents('.card-group').data('group') === 'limit') {
                if (parseFloat(widget.opt.targetBalance) > 0) {
                    inputSellField.val(parseFloat(widget.opt.targetBalance).toFixed(8)).keyup();
                }
            }
            if ($(this).parents('.card-group').data('group') === 'market') {
                inputSellField.val(parseFloat(widget.opt.targetBalance).toFixed(8));
                if (parseFloat(widget.opt.targetBalance) > 0) {
                    window["slidersell_market"].bootstrapSlider('setValue', 100, false, false);
                }
            }
        });

        widget.attr('data-binded', 'trading-form-widget');

        return widget;
    };

}(jQuery));

window.refreshWall = function() {
    if (typeof window.cardTradeDepth != 'undefined') {
        window.cardTradeDepth.init();
    }
    if (typeof window.currentOrdersTable  != 'undefined') {
        window.currentOrdersTable.init();
    }
    if (typeof window.orderHistory  != 'undefined') {
        window.orderHistory.init();
    }
    if (typeof window.currentPairWidget  != 'undefined') {
        window.currentPairWidget.init();
    }
    if (typeof window.latestExecutionWidget  != 'undefined') {
        window.latestExecutionWidget.updateTable();
    }
};

$(document).ready(function() {

    window.tradingForm = $("#trading-form-card").TradingForm({
        baseCoin: '{{ $base }}',
        baseBalance: "{{ $baseBalance }}",
        targetCoin: '{{ $target }}',
        targetBalance: '{{ $targetBalance }}',
        price: '{{ $price }}',
        buyPrice: '{{ isset($ask_price) ? $ask_price : 0 }}',
        sellPrice: '{{ isset($bid_price) ? $bid_price : 0 }}',
        priceUsd: '{{ $price_usd }}',
        formUrl: "{{ route('exchange.form') }}",
        maxAmount: parseFloat("{{ $max_amount }}").toFixed(8),
        minAmount: parseFloat("{{  $min_amount }}").toFixed(8),
        maxPrice: parseFloat("{{ $max_price }}").toFixed(8),
        minPrice: parseFloat("{{  $min_price }}").toFixed(8),
        minCost : parseFloat("{{  $min_cost }}").toFixed(8),
        maxAmountLabel: "{{ __('Maximum amount') }}",
        minAmountLabel: "{{ __('Minimum amount') }}",
        maxPriceLabel: "{{ __('Maximum price') }}",
        minPriceLabel: "{{ __('Minimum price') }}",
        minCostLabel : "{{ __('Minimum cost') }}", 
        insufficientBalanceLabel: "{{ __('Insufficient funds in {coin} wallet') }}",
        fee_percentage: parseFloat("{{ $fee_percentage ?? 0 }}"),
        userPercentDiscount : parseInt('{{ $user_discount_percentage }}'),
        userHas2FAEnabled: '{{ auth()->check() && auth()->user()->is2FAEnable() ? 1 : 0 }}'
    });

    window.tradingForm.init();
    // console.log(window.tradingForm.opt.minBuyAmount)
});

  </script>
    @auth
    <script type="text/javascript">
        $(document).ready(function() {      
            window.Echo.private('PairBalancesChannel_{{auth()->user()->id}}_{{$base}}{{$target}}')
                .listen('PairBalancesEvent', (data) => {
                    // console.log('BalanceEvent',data)
                    window.tradingForm.setBaseBalance(data.base);
                    window.tradingForm.setTargetBalance(data.target);
            });
            window.Echo.private('CoinBalanceChannel_{{auth()->user()->id}}')
                .listen('CoinBalanceEvent', (data) => {
                    // console.log('CoinBalanceEvent',data)
                    window.tradingForm.setBalanceByTicker(data.ticker, data.balance);
            });
        })
    </script>
    @endauth
@endpush