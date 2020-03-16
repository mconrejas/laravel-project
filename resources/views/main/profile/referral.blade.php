@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards" id="referral-section">
            <div class="card rounded-0 py-3 px-0" id="card-wallet">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start">
                        <div class="align-self-center">
                            <h5>{{__('Referral')}}</h5>
                            <small class="d-block text-secondary"> {{__('Refer friends to trade in Buzzex and get more referral rewards.')}}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body my-0">
                    <div class="row">
                        <div class="col-12 col-md-8 order-md-1 order-2 my-2 my-md-0 d-flex ">
                            <div class="input-group input-group-sm align-self-center flex-fill ">
                                <div class="input-group-append">
                                    <span class="text-light-on-dark input-group-text bg-transparent border-0">{{__('My referral code')}}</span>
                                </div>
                                <input type="text" class="btn-light-on-dark form-control bg-transparent input-sm border" readonly value="{{Auth::user()->affiliate_id}}" >
                                <div class="input-group-prepend">
                                    <span class="input-group-button">
                                        <button class="btn-light-on-dark btn-input-group-copy btn btn-sm rounded-0 border"><i class="fa fa-copy"></i></button> 
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 order-md-2 order-1 my-2 my-md-0 d-flex justify-content-md-end">
                            <button class="btn-social-share flex-fill btn btn-sm px-3 border rounded-0 btn-primary"
                            data-type="facebook"
                            data-url="{{ Auth::user()->getReferralLink() }}"
                            title="Share to Facebook"
                            rel="tooltip">
                                <span class="fa fa-facebook"></span> {{__('Share')}}
                            </button>
                            <button class="btn-social-share flex-fill btn btn-sm px-3 border rounded-0 btn-info"
                            data-type="twitter"
                            data-url="{{ Auth::user()->getReferralLink() }}"
                            data-text="{{__('Refer friends to trade in Buzzex and get more referral rewards.')}}"
                            title="Tweet to Twitter"
                            rel="tooltip">
                                <span class="fa fa-twitter"></span> {{__('Tweet')}}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body my-0">
                    <div class="row">
                        <div class="col-md-8 col-12 d-flex justify-content-start">
                            <div class="input-group input-group-sm mb-3 align-self-center">
                                <div class="input-group-append">
                                    <span class="text-light-on-dark input-group-text bg-transparent border-0">{{__('My referral link')}}</span>
                                </div>
                                <input type="text" class="btn-light-on-dark form-control bg-transparent input-sm border" readonly value="{{ Auth::user()->getReferralLink() }}" >
                                <div class="input-group-prepend">
                                    <span class="input-group-button">
                                        <button class="btn-light-on-dark btn-input-group-copy btn btn-sm rounded-0 border"><i class="fa fa-copy"></i></button> 
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-block px-4 py-1 d-flex">
                    <div class="card-group w-100">
                        <div class="card card-body py-5 border border-secondary">
                            <h6 class="text-secondary align-self-center">{{__('Referred friends')}}</h6>
                            <span class="text-warning align-self-center">{{$referred_friend_count}}</span>
                        </div>
                        <div class="card card-body py-5 border border-secondary">
                            <h6 class="text-secondary align-self-center">{{__('Referral reward')}}</h6>
                            <span class="text-warning align-self-center">{{$referral_reward_total}} BZX</span>
                        </div>
                        <div class="card card-body py-5 border border-secondary">
                            <h6 class="text-secondary align-self-center">{{__('Referral ratio')}}</h6>
                            <span class="text-warning align-self-center">{{$referral_ratio}} %</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-deck my-3">
                <div class="card py-4 rounded-0">
                    <h5 class="card-title pl-3">{{ __('Referral history') }}</h5>
                    <div class="card-block">
                        <div id="table-referral-history" class="table"></div>
                    </div>
                </div> 
                <div class="card py-4 rounded-0">  
                    <h5 class="card-title pl-3">{{ __('Referral reward history') }}</h5>
                    <div class="card-block">
                        <div id="table-reward-history" class="table"></div>
                    </div>
                </div>
            </div>

            <div class="card rounded-0 my-3">
                <div class="card-title lead px-4 py-4">{{ __('Referral rules') }}</div>
                <div class="card-body pl-0 pl-md-2">
                    <ul class="font-15 text-secondary">
                        <li>{{ __('Only users that sign up with YOUR referral link or code are counted as YOUR referrals.') }}</li>
                        <li>{{ __('BUZZEX will NOT allocate referral reward for malicious spamming registrations.') }}</li>
                        <li>{{ __('Please take reference from this page for actual referral ratio.') }}</li>
                        <li>{{ __('BUZZEX reserves the rights of final explanation for readjusting referral rules.') }}</li>                    
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .card-block .form-label {
        display: inline-block;
        min-width: 200px;
    }

    .input-group-text {
        min-width: 150px
    }

</style>
@endsection

@section('scripts')
<script type="text/javascript">
/**
 * Left market widget
 */
(function($) {

    $.fn.ReferralWidget = function(params) {
        var widget = this;
        var opt = $.extend({
            referralHistoryUrl: '',
            rewardHistoryUrl: '',
            affiliate_id: '',
            paginationSize: 50,
        }, params);
        widget.find('.btn-social-share').on('click', function() {
            var button = $(this);
            var type = button.data('type');
            var link = "";
            if (type == 'facebook') {
                var shareurl = button.data('url');
                link = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(shareurl);
            } else if (type == 'twitter') {
                var text = button.data('text');
                var shareurl = button.data('url');
                link = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(text) + '&url=' + encodeURIComponent(shareurl);
            }
            window.Templates.popupCenter(link, 'Buzzex Social Share', 500, 400);
            return false;
        });

        if (Clipboard.isSupported()) {
            var inviteClipboard = new Clipboard('.btn-input-group-copy', {
                text: function(trigger) {
                    return $(trigger).parents('.input-group').find('input').val();
                }
            });
            inviteClipboard.on('success', function(e) {
                $(e.trigger).parents('.input-group').find('input').focus();
                $(e.trigger).parents('.input-group').find('input').select();
                toast({
                    type: 'success',
                    title: 'Successfully Copied'
                });
            });
        } else {
            $('.btn-input-group-copy').addClass('disabled').attr('data-toggle', 'tooltip').attr('title', 'Clipboard not supported')
        }

        var createTable = function(el, config) {
            var defaults = {
                layout: "fitColumns",
                placeholder: window.Templates.noDataAvailable(),
                data: [], //set initial table data
                layoutColumnsOnNewData: false
            };
            var setting = Object.assign({}, config, defaults);
            return new Tabulator(el, setting);
        };

        var referralHistoryConfig = {
            pagination: 'remote',
            paginationSize: opt.paginationSize,
            columns: [{
                    title: "Name",
                    formatter: function(row) {
                        var data = row.getData();
                        return data.first_name + ' ' + data.last_name;
                    },
                    resizable: false,
                    headerSort: false
                },
                {
                    title: "Verified",
                    field: "email_verified_at",
                    align: 'center',
                    width: 100,
                    resizable: false,
                    formatter: 'tickCross',
                    formatterParams: {
                        allowTruthy: true
                    }
                },
                {
                    title: "Signup Date",
                    field: "created_at",
                    align: 'left',
                    width: 150,
                    resizable: false
                }
            ],
            ajaxURL: opt.referralHistoryUrl,
            ajaxParams: {},
            ajaxConfig: {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                },
            },
            ajaxResponse: function(url, params, response) {
                if (response.last_page <= 1) {
                    $("#table-referral-history .tabulator-footer").hide();
                } else {
                    $("#table-referral-history .tabulator-footer").show();
                }
                return response;
            }
        };
        var rewardHistoryConfig = {
            columns: [{
                    title: "Amount",
                    field: "amount",
                    resizable: false
                },
                {
                    itle: "Coin",
                    field: "coin",
                    align: 'left',
                    resizable: false
                },
                {
                    title: "Allocated at",
                    field: "allocated_at",
                    align: 'right',
                    resizable: true
                }
            ],
            ajaxURL: opt.rewardHistoryUrl,
            ajaxParams: {},
            ajaxConfig: {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                }
            }
        };
        widget.referralHistory = createTable('#table-referral-history', referralHistoryConfig);
        widget.rewardHistory = createTable('#table-reward-history', rewardHistoryConfig);

        return widget;
    }
}(jQuery));

$(document).ready(function() {

    $("#referral-section").ReferralWidget({
        referralHistoryUrl: "{{route('ref.getReferred')}}",
        paginationSize: 10,
        rewardHistoryUrl: '',
    });
})
</script>
@endsection
