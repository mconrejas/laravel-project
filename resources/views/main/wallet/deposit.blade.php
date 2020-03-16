@extends('masters.app')

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
                            <img class="mr-2" src="{{ $coin->iconUrl }}" height="30"> {{$coin->name}} {{ __('Deposit') }}
                            </h4>
                        </div>
                        <div class="col-12 col-md-4 align-self-center text-center">
                            <a href="{{ route('my.withdraw',['coin'=>$coin->symbol]) }}" class="my-3 my-md-0">{{ __('Withdrawal') }} »</a>
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
                </div>
                @if(!$coin->getAltDepositStatus())
                <div class="card-body">
                    <div class="card-block my-md-3 py-md-4 px-md-5">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="border input-group-text rounded-0" id="copy">{{ __('Your deposit address') }} :</span>
                            </div>
                            <input type="text" class="text-center border rounded-0 form-control input_deposit_address" readonly aria-describedby="copy" value="{{ $deposit_address }}">
                            <div class="input-group-append">
                                <button class="btn btn-secondary btn-input-group-copy rounded-0" id="copy">{{ __('Copy') }}</button>
                            </div>
                        </div>
                        @if(!$is_available)
                        <!--div class="input-group-append text-red">
                            {{ __('NOTE') }} : {{ __('There are no new address available') }}!
                        </div-->
                        @endif
                        <div class="mx-auto text-center">
                            <img alt="scan me!" src="//chart.apis.google.com/chart?cht=qr&chs=300x300&chl={{ $deposit_address }}" />
                        </div>
                        
                        <div class="text-center mt-2">
                            <!-- my.newDepositAddress -->
                            <!--button class="btn border @if($is_available) btn-buzzex  btn-request-new-address @else text-strike disabled @endif">{{ __('Use new address') }}</button-->
                            <a href="javascript:;" class="btn btn-link btn-display-history">{{ __('History deposit add') }} »</a>
                        </div>
                    </div>
                    
                    <div class="card-block">
                        {{ __('Deposit note')}} :
                        <div class="alert alert-danger mt-2 pl-0 pl-md-2">
                            <ul class="mb-0 text-secondary">
                                <li>This address is ONLY available for {{$coin->symbol}} deposit. Minimum limit for each deposit is {{$coin->getMinimumDepositAmount()}} {{$coin->symbol}}. Any deposit below minimum limit or non-{{$coin->symbol}} deposit will NOT be added to your account and it is NOT refundable.</li>
                                <li>{{$coin->symbol}} transactions is hard to expect due to network traffic. Your deposit will be verified after {{$coin->getNumberOfConfirmationsAttribute()}} confirmation(s).</li>
                            </ul>
                        </div>
                        <a target="_blank" href="{{ $buzzexLinks->faqs->url }}" class="btn-link">{{ __('Deposit FAQ') }} » </a>
                    </div>
                </div>
                @else
                <div class="card-body">
                    @if($exact_amount)
                    <div class="card-block">                        
                        <div class="alert alert-warning mt-2 pl-0 pl-md-2 warning-holder">
                            <b>{{ __('Important')}} </b>:
                            <ul class="mb-0 text-secondary" style="list-style: none;">
                                <li>Send only {{$coin->symbol}} to this deposit address. Sending any other coin or token to this address may result in the loss of your deposit.</li> 
                                @if($api_coin['tag'])
                                <li >
                                    <div class="alert alert-danger mt-2 pl-0 pl-md-2">
                                        <ul class="mb-0 text-secondary" style="list-style: none;">
                                            <li><span class="fa fa-warning"></span> <b>Notice</b>: The <b>exact amount </b>, <b>tag</b> and <b>address</b> are required to successfully deposit your {{$coin->symbol}} to Buzzex.</li> 
                                        </ul>
                                    </div> 
                                </li>
                                @else
                                <li >
                                    <div class="alert alert-danger mt-2 pl-0 pl-md-2">
                                        <ul class="mb-0 text-secondary" style="list-style: none;">
                                            <li><span class="fa fa-warning"></span> <b>Notice</b>: Both the <b>exact amount </b>and <b>address </b> are required to successfully deposit your {{$coin->symbol}} to Buzzex.</li> 
                                        </ul>
                                    </div> 
                                </li>
                                @endif
                                @if($api_coin['info']['addressTag'])
                                <li>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input chk-aggree" id="iaggree">
                                        <label class="custom-control-label" for="iaggree" style="cursor: pointer"> I Understand.</label>
                                    </div>
                                </li>
                                @else
                                <li>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input chk-aggree" id="iaggree">
                                        <label class="custom-control-label" for="iaggree" style="cursor: pointer"> I Understand.</label>
                                    </div>
                                </li>
                                @endif
                            </ul>

                            <button class="btn border btn-buzzex pull-right btn-continue-deposit" 
                            disabled style="cursor: pointer;" 
                            >{{ __('Continue Deposit') }}</button>
                            <div style="clear:both;"></div>
                        </div> 
                    </div>
                    
                        <div class="card-block my-md-3 py-md-4 px-md-5 details-holder" style="display: none;" >

                                                       
                            <div class="input-group mb-3">   
                                <div class="input-group-prepend pt-2 pr-2">  
                                    @if($api_coin['info']['addressTag'])
                                        <span rel="tooltip" title="" data-original-title="The exact amount, tag and address are required to successfully deposit your {{$coin->symbol}} to Buzzex." style="cursor: pointer; color: #DC3547; font-size: 20px;">
                                            <span class="fa fa-warning"></span>
                                        </span> 
                                    @else
                                        <span rel="tooltip" title="" data-original-title="Both the exact amount and address are required to successfully deposit your {{$coin->symbol}} to Buzzex." style="cursor: pointer; color: #DC3547; font-size: 20px;">
                                            <span class="fa fa-warning"></span>
                                        </span> 
                                    @endif
                                </div>                           
                                <div class="input-group-prepend">
                                    <span class="border input-group-text rounded-0" id="copyamount">{{ __('Exact Amount to Deposit') }} :</span>
                                </div>
                                <input type="text" class="text-center border rounded-0 form-control input_deposit_address" readonly a value="{{$exact_amount}}" > 
                                <div class="input-group-append">
                                    <button class="btn btn-secondary btn-input-group-copy rounded-0" id="copyamount">{{ __('Copy') }}</button>
                                </div>
                            </div>

                            @if($api_coin['tag'])                               
                            <div class="input-group mb-3">
                                <div class="input-group-prepend pt-2 pr-2">  
                                    <span rel="tooltip" title="" data-original-title="An exact amount to deposit, a tag and an address are required to successfully deposit your {{$coin->symbol}} to Buzzex." style="cursor: pointer; color: #DC3547; font-size: 20px;">
                                        <span class="fa fa-warning"></span>
                                    </span> 
                                </div>   
                                <div class="input-group-prepend">
                                    <span class="border input-group-text rounded-0" id="copytag">{{ __('Your Tag') }} :</span>
                                </div>
                                <input type="text" class="text-center border rounded-0 form-control input_tag_address" readonly aria-describedby="copy" value="{{ $api_coin['tag'] }}">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary btn-input-group-copy rounded-0" id="copytag">{{ __('Copy') }}</button>
                                </div>
                            </div>
                            @endif                            
                            
                            <div class="input-group mb-3">
                                <div class="input-group-prepend pt-2" style="width: 27px;"></div>
                                <div class="input-group-prepend">
                                    <span class="border input-group-text rounded-0" id="copy">{{ __('Your deposit address') }} :</span>
                                </div>
                                <input type="text" class="text-center border rounded-0 form-control input_deposit_address" readonly aria-describedby="copy" value="{{ $api_coin['address'] }}">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary btn-input-group-copy rounded-0" id="copy">{{ __('Copy') }}</button>
                                </div>
                            </div>                            
     
                            <div class="mx-auto text-center">
                                <img alt="scan me!" src="//chart.apis.google.com/chart?cht=qr&chs=300x300&chl={{ $api_coin['address'] }}" />
                            </div>                        
                        
                        </div> 
                    @else
                    <div class="card-block">
                        <form action="{{route('my.depositForm',['coin'=>$coin->symbol])}}" method="GET">
                            <h3 style="text-align:center">{{__('Deposit Request Form')}}</h3>
                            <div class="input-group mt-4 mb-3 col-md-6 offset-md-3">
                                <input type="text" name="amount" class="text-center border rounded-0 form-control" placeholder="{{__('Enter the amount you want to deposit')}}" required autofocus>
                            </div>
                            <center>
                                <button class="btn border btn-buzzex " type="submit">{{__('Submit Request')}}</button>
                            </center>
                            <div class="instructions" style="width: 90%;text-align: center;font-size: small;margin: 0 auto;border-bottom: 1px solid #e8ebee;margin-bottom: 20px;padding-bottom: 20px;">After clicking the submit button, the system will generate the <strong>exact amount</strong> for you to deposit <br />based on the deposit amount you entered above.</div>
                        </form>
                    </div>
                    @endif

                    <div class="card-block">                        
                        <div class="alert alert-danger mt-2 pl-0 pl-md-2">
                            <b>{{ __('Deposit note')}} :</b>
                            <ul class="mb-0 text-secondary">
                                <li>This address is ONLY available for {{$coin->symbol}} deposit. 
                                    @if(!$coin->getAltDepositStatus()) Minimum limit for each deposit is 0.001. Any deposit below minimum limit or non-{{$coin->symbol}} deposit will NOT be added to your account and it is NOT refundable. @endif</li>
                                <li>{{$coin->symbol}} transactions is hard to expect due to network traffic. Your deposit will be verified after 1 confirmation and available for withdrawal after 6 confirmations.</li>
                            </ul>
                        </div>
                        <a target="_blank" href="{{ $buzzexLinks->faqs->url }}" class="btn-link">{{ __('Deposit FAQ') }} » </a>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="card rounded-0 pt-3 px-0 my-2" id="deposit-records">
                <div class="mx-3 d-flex justify-content-between flex-wrap">
                    <span class="">
                        {{ __('Last 5 Deposit Record') }}
                    </span>
                    <a href="{{ route('my.record',['type'=>'deposit']) }}">{{ __('All deposit record') }} »</a>
                </div>
                <div id="deposit-records-table" class="table table-sm mt-3"></div>
            </div>
        </div>
    </div>
</div>
<div id="deposit_history" style="display: none;">
     @if($history_address)
            <table class="table table-sm"> 
                <tbody>
                    @foreach($history_address as $history) 
                        <tr>
                            <td>{{$history->address}}</td> 
                        </tr>
                    @endforeach
                </tbody>
            </table>
     @else
        <b>No Available History!</b>
     @endif
     <p></p>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
(function($) {
    $.fn.DepositRecordsWidget = function(param) {
        var widget = this;
        var opt = $.extend({
            recordUrl: '',
            showIn: 'USD',
            type: 'deposit',
            limit: 50,
            coin: 'all',
            tableSelector: ''
        }, param);

        widget.datatable = new Tabulator(opt.tableSelector, {
            fitColumns: true,
            columnMinWidth: 80,
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
                },
            },
            columns: [{
                    title: 'Deposit Time',
                    field: "time"
                },
                {
                    title: "Coin",
                    field: "coin",
                    width: 75
                },
                {
                    title: "Gross",
                    field: "amount"
                },{
                    title: "Net",
                    field: "net_amount"
                },
                {
                    title: 'Deposit Address',
                    field: "address"
                },
                {
                    title: "Status",
                    field: "status",
                    sortable: false
                },
                {
                    title: "TXID",
                    field: 'txid',
                    align: 'left'
                },
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

    if (Clipboard.isSupported()) {
        var depositClipboard = new Clipboard('.btn-input-group-copy', {
            text: function(trigger) {
                return $(trigger).parents('.input-group').find('input').val();
            }
        });
        depositClipboard.on('success', function(e) {
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

    $(".btn-request-new-address").click(function() {

        $.post("{{route('my.newDepositAddress',['coin'=>$coin->symbol])}}", {
            
        })
        .done(function(data) {
            if(data){
                $('.input_deposit_address').val(data); // replace deposit address value
                toast({
                    type: 'success',
                    title: 'New address generated!'
                });
            }else{
                toast({
                    type: 'error',
                    title: 'Cannot Generate!'
                });
            }
        });
        
    })
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
            window.location = "{{route('my.depositForm',['coin'=>''])}}/" + ui.item.value
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>").append("<div><img src='" + item.icon + "' width='20'> " + item.value + " <span class='font-12 float-right text-secondary'>" + item.label + "</span></div>").appendTo(ul);
    };
    $("#deposit-records").DepositRecordsWidget({
        recordUrl: "{{route('my.getRecords',['type'=>$coin->name])}}",
        type: "deposit",
        limit: 5,
        coin: "{{ $coin->symbol }}",
        tableSelector: '#deposit-records-table'
    });

    $('.btn-display-history').on('click', function(){
        swal({
            title: '<h5 class="my-5">Deposit Address History</h5>',
            html : $('#deposit_history').html(),
            showCloseButton: true,
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonClass: 'btn btn-dark rounded-0 px-5',
            buttonsStyling: false,
            focusCancel: true,
            cancelButtonText: 'Close'
        })
    });

    $(".chk-aggree").click(function() {
      if(this.checked)
        $(".btn-continue-deposit").attr("disabled",false);
      else
        $(".btn-continue-deposit").attr("disabled",true);
    });

    $(document).on('click','.btn-continue-deposit', function(){
        $('.details-holder').show();
        $('.warning-holder').hide();
    });

    @if(session()->has('error')) 
        swal({
                title: '<span style="font-size:17px;">Error!</span>',
                buttonsStyling: false,
                confirmButtonClass: 'btn btn-sm btn-buzzex px-5 rounded-0',
                confirmButtonText: 'Close',
                html: '<div>{{Session::get("error")}}</div>',
            })
    @endif
    
})
</script>
@endsection