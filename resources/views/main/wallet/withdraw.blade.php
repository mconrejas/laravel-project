@extends('masters.app')

@push('styles')
<style type="text/css">
    #withdraw-form form .input-group .input-group-prepend {
        min-width: 170px;
    }
    @media (max-width: 767px) {
        #withdraw-form form .input-group .input-group-prepend {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid my-md-5 my-3 px-md-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.wallet.component.side-menu')
        </div>
        <div class="col-12 col-md-9 my-cards">
            <div class="card rounded-0 pt-3 px-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-4 align-self-center">
                            <h4 class="lead py-4 mx-4">
                            <img class="mr-2" src="{{ $coin->iconUrl }}" height="30"> {{$coin->name}} {{ __('Withdrawal') }}
                            </h4>
                        </div>
                        <div class="col-12 col-md-4 align-self-center text-center">
                            <a href="{{ route('my.depositForm',['coin'=>$coin->symbol]) }}" class="my-3 my-md-0">{{ __('Deposit') }} »</a>
                        </div>
                        <div class="col-12 col-md-4 align-self-center">
                            <div class="input-group border w-100 align-self-center searchbox-wrapper">
                                <input type="text" class="form-control border-0 rounded-0 searchbox-input" placeholder="Enter coin or name">
                                <div class="input-group-append">
                                    <button class="btn bg-transparent border-0 rounded-0 border-left-0 btn-searchbox" type="button"><span class="fa fa-search"></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body px-0" id="withdraw-form">
                        <div class="card-block my-3 py-4 px-0 px-md-5">
                            
                            @include('partials.errors')
                            @include('partials.success')
                            <div class="row">
                                <form class="mx-auto col-12 col-md-8" id="{{ uniqid() }}" method="POST" action="{{ route('my.withdraw',['coin' => $coin->symbol]) }}">
                                    <input type="hidden" name="coin" value="{{$coin->symbol}}">
                                    @csrf
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{__('Address')}} :</span>
                                            </div>
                                            <input type="text" required class="form-control" name="address" placeholder="{{__('Please enter destination address')}}">
                                            <div class="input-group-append">
                                                <button class="rounded-0 btn border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('Address History') }}</button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @forelse($addresses as $label => $address)
                                                    <a class="dropdown-item set-address" href="javascript:void(0)" data-value="{{$address}}">
                                                        {{$label}}<br>
                                                        <small>{{$address}}</small>
                                                    </a>
                                                    @empty
                                                    <a class="dropdown-item" <span class="fa fa-close mr-2"></span> {{ __('No address history') }}</a>
                                                    @endforelse
                                                <!-- <div role="separator" class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#"><span class="fa fa-plus mr-2"></span> {{ __('Add new address') }}</a> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{__('Balance')}} :</span>
                                            </div>
                                            <input type="text" class="form-control numeric" readonly value="{{$balance}}">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border rounded-0 text-light-on-dark">{{ $coin->symbol }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{__('Amount')}} :</span>
                                            </div>
                                            <input type="text" required class="form-control numeric amount" name="amount" value="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border rounded-0 text-light-on-dark">{{ $coin->symbol }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if(array_key_exists($coin->symbol,$coins_with_tag))
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{ $coins_with_tag[$coin->symbol] }} :</span>
                                            </div>
                                            <input type="text" required class="form-control" name="tag" value="">

                                        </div>
                                    </div>
                                    @endif
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{__('Withdrawal Fee')}} :</span>
                                            </div>
                                            <input type="text" class="form-control numeric fee" readonly  value="{{$coin->getWithdrawalFee()}}">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border rounded-0 text-light-on-dark">{{ $coin->symbol }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{__('Obtain')}} :</span>
                                            </div>
                                            <input type="text" class="form-control numeric obtain" name="obtain" value="0" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-transparent border rounded-0 text-light-on-dark">{{ $coin->symbol }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if(auth()->user()->is2FAEnable())
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{__('2-Factor Authentication Code')}} :</span>
                                            </div>
                                            <input placeholder="{{__('Enter 2FA code')}}" type="text" required class="form-control text-center" name="twofa_code">
                                        </div>
                                    </div>
                                    @else
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-transparent border-0 text-light-on-dark">{{__('Confirmation Code')}} :</span>
                                            </div>
                                            <input placeholder="{{__('Enter email confirmation code')}}" type="text" required class="form-control text-center" name="email_code">
                                            <div class="input-group-append">
                                                <button type="button" data-source="{{route('email.requestEmailCode')}}" class="btn border rounded-0 request-code text-buzzex">{{__('Get Code')}}</button>
                                            </div>
                                        </div>
                                        <div class="text-center email_confirmation_code_message"></div>
                                    </div>
                                    @endif
                                    
                                    <div class="form-group mt-5 d-flex justify-content-center">
                                        <button class="w-50 mx-auto btn btn-buzzex" type="submit">{{ __('Submit') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card-block">
                            {{ __('Withdrawal note')}} :
                            
                            <div class=" alert alert-danger mt-2">
                                <ul class="text-secondary">
                                    <li>{{ __('Please double check your withdrawal address. If the entered address is incorrect, your funds will be lost and there is no chance to recover them.')}}</li>
                                    <li>Arrival: Withdrawals are sent immediately after auditing, while the actual arrival time depends on the required number of confirmations on blockchain.</li>
                                </ul>
                            </div>
                            
                            <a target="_blank" href="{{ $buzzexLinks->faqs->url }}" class="btn-link">{{ __('Withdrawal FAQ') }} » </a>
                        </div>
                    </div>
                </div>
                
                <div class="card rounded-0 pt-3 px-0 my-2" id="withdrawal-records">
                    <div class="mx-3 d-flex justify-content-between">
                        <span class="">
                            {{ __('Last 5 Withdrawal Record') }}
                        </span>
                        <a href="{{ route('my.record',['type'=>'withdrawal']) }}">{{ __('All withdrawal record') }} »</a>
                    </div>
                    <div id="withdrawal-records-table" class="table table-sm mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
(function($) {
    $.fn.WithdrawalForm = function(params) {
        var widget = this;
        var opt = $.extend({

        }, params);

        widget.on("click", ".set-address", function(argument) {
            var value = $(this).data('value');
            var input = $(this).parents('.input-group').find('input');
            input.val(value);
        });
        widget.on('click', 'form .request-code', function() {
            var button = $(this);
            var source = button.data('source');
            var messageBox = widget.find('.email_confirmation_code_message');
            button.btnProcessing('.');
            $.post(source, {
                    type: 'withdrawal-code'
                })
                .done(function(e) {
                    if (e.status == 200) {
                        messageBox.html("<span class='text-success'>" + e.message + "</span>")
                        button.remove();
                    } else {
                        button.btnReset();
                        messageBox.html("<span class='text-danger'>Something went wrong. Please try again later</span>");
                    }
                }).fail(function(e) {
                    button.btnReset();
                    messageBox.html("<span class='text-danger'>Something went wrong. Please try again later</span>");
                })
        });
        widget.on('keyup', 'form .amount', function() {
            var receive = $(this).val() - $('form .fee').val();
            $('form .obtain').val(receive < 0 ? 0 : receive);
        });
    };

    $.fn.WithdrawalRecordsWidget = function(param) {
        var widget = this;
        var opt = $.extend({
            recordUrl: '',
            showIn: 'USD',
            type: 'withdrawal',
            limit: 50,
            coin: 'all',
            tableSelector: ''
        }, param);

        widget.datatable = new Tabulator(opt.tableSelector, {
            columnMinWidth: 80,
            fitColumns: true,
            layout: "fitColumns",
            responsiveLayout: 'collapse',
            index: 'coin',
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            layoutColumnsOnNewData: false,
            ajaxURL: opt.recordUrl,
            ajaxParams: {
                limit: opt.limit,
                coin: opt.coin,
                type: opt.type,
                size: 5
            },
            ajaxConfig: {
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                }
            },
            columns: [
                {
                    title: 'Withdrawal Time',
                    field: "time",
                    headerSort: false
                },
                {
                    title: "Coin",
                    field: "coin",
                    width: 75,
                    headerSort: false
                },
                {
                    title: "Gross",
                    field: "amount",
                    headerSort: false
                },
                {
                    title: "Net",
                    field: "net_amount",
                    headerSort: false
                },
                {
                    title: 'Withdrawal Address',
                    field: "address",
                    headerSort: false
                },
                {
                    title: "Status",
                    field: "status",
                    sortable: false,
                    headerSort: false,
                    /*formatter: function(cell, formatterParams, onRendered) {
                        return cell.getValue();
                        var status = '<span class="text-success py-1 px-2">approved</span>';
                        if (cell.getValue() == 'cancelled')
                            status = '<span class="text-warning py-1 px-2">cancelled</span>';
                        return status;
                    }*/
                },
                {
                    title: "TXID",
                    field: 'txid',
                    align: 'left',
                    headerSort: false
                }
            ],
            rowFormatter: function(row) {},
            ajaxResponse: function(url, params, response) {
                return typeof response.data == 'undefined' ? [] : response.data;
            }
        });
        return widget;
    }
}(jQuery));
$(document).ready(function() {

    $(".searchbox-input").autocomplete({
        classes: {
            "ui-autocomplete": "record-autocomplete",
        },
        source: function(request, response) {
            $.post("{{route('searchCoin')}}", {
                    term: request.term,
                })
                .done(function(data) {
                    response(data.length > 0 ? data.slice(1) : data);
                });
        },
        minLength: 2,
        select: function(event, ui) {
            window.location = "{{route('my.withdrawalForm',['coin'=>''])}}/" + ui.item.value
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>").append("<div><img src='" + item.icon + "' width='20'> " + item.value + " <span class='font-12 float-right text-secondary'>" + item.label + "</span></div>").appendTo(ul);
    };



    $("#withdrawal-records").WithdrawalRecordsWidget({
        recordUrl: "{{route('my.getRecords',['type'=>$coin->symbol])}}",
        type: "withdrawal",
        limit: 5,
        coin: "{{ $coin->symbol }}",
        tableSelector: '#withdrawal-records-table'
    });

    $("#withdraw-form").WithdrawalForm({});
})
</script>
@endsection