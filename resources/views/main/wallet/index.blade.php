@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-3">
    		@include('main.wallet.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-wallet">
            	<h1 class="lead font-weight-bold py-4 mx-auto">{{ __('Personal Wallet') }}</h1>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="input-group border searchbox-wrapper">
                                <input type="text" class="form-control border-0 rounded-0 coin-select" placeholder="Coin ticker">
                                <div class="input-group-append">
                                    <button data-action="search" class="btn bg-transparent border-0 rounded-0 border-left-0 btn-searchbox" type="button"><span
                                    class="fa fa-search"></span></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 ml-lg-3 mt-2">
                            <span class="switch" rel="tooltip" title="Estimated market value under 1 USD">
                                <input type="checkbox" name="{{ uniqid() }}" class="switch switch-sm checkbox-switch" id="switch-id">
                                <label for="switch-id">{{__('Hide small balances') }}
                                </label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-block">
                    <div id="wallet-table" class="table table-sm mt-3"></div>
                </div>
            </div>

            <div class="card mt-3" id="card-pending-deposit" style="min-height: 200px;">
                <h4 class="lead py-4 mx-auto">{{ __('Pending Deposit') }}</h4>
                <div class="card-block px-2 d-flex justify-content-start">
                    <div class="input-group border w-25 align-self-center">
                         @coinselect(['includeAll'=> true, 'class'=>'searchbox-input-pending rounded-0 form-control'])
                         @endcoinselect
                    </div> 
                    <br />
                </div>
                <div class="card-block">
                    <div id="pending-deposit-table" class="table table-sm mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .theme-dark .tabulator-row .fa {
        color: #1adebd;
    }
    .btn-to-trade:hover, .btn-to-trade:active {
        background: #21e5b9;
        border-radius: 0; 
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
(function($) {
    $.fn.WalletWidget = function(param) {
        var widget = this;
        var opt = $.extend({
            walletUrl: '',
            depositUrl: '',
            withdrawUrl: '',
            tradeListUrl : '',
            showIn: 'USD',
            tableSelector: '',
            checkBoxValue: '',
            limit: 25
        }, param);
        var table = widget.find('.table');
        var search = widget.find('.coin-select');
        var searcBtn = widget.find('.btn-searchbox');
        var checkbox = widget.find('.checkbox-switch');

        var actionLinks = function(row) {
            var data = row.getData();
            links = "<a href='" + opt.depositUrl + '/' + data.coin + "' rel='tooltip' data-placement='left' class='mr-1 btn btn-sm' data-action='deposit' title='Deposit'><span class='fa fa-cloud-download'></span></a>" +
                "<a href='" + opt.withdrawUrl + '/' + data.coin + "' rel='tooltip' data-placement='left' class='mr-1 btn btn-sm' data-action='withdraw' title='Withdraw'><span class='fa fa-cloud-upload'></span></a>"+
                "<a href='javascript:void(0)' data-id='"+ data.id +"' class='mr-1 btn btn-sm btn-trade' data-action='Trade' title='Trade'><span class='fa fa-exchange'></span></a>";
            return links;
        };

        widget.datatable = new Tabulator(opt.tableSelector, {
            pagination: "remote",
            paginationSize: opt.limit,
            fitColumns: true,
            columnMinWidth: 80,
            layout: "fitColumns",
            responsiveLayout: 'collapse',
            index: 'coin',
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            layoutColumnsOnNewData: true,
            ajaxURL: opt.walletUrl,
            ajaxParams: {
                coin: 'all',
                value: opt.checkBoxValue
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
                    title: "Coin",
                    field: "coin",
                    width: 75
                },
                {
                    title: "Name",
                    field: "coinName"
                },
                {
                    title: "Amount",
                    field: "amount",
                    sorter: "number",
                    formatter : function(row){
                        var data = row.getData().amount;
                        return "<span class='"+(data <= 0 ? 'text-danger' : 'text-success' )+"'>"+data+"</span>";
                    }
                },
                {
                    title: "Frozen",
                    field: "frozen",
                    sorter: "number",
                    formatter : function(row){
                        var data = row.getData().frozen;
                        return "<span class='"+(data <= 0 ? 'text-danger' : 'text-success' )+"'>"+data+"</span>";
                    }
                },
                {
                    title: "Available",
                    field: "available",
                    sorter: "number",
                    formatter : function(row){
                        var data = row.getData().available;
                        return "<span class='"+(data <= 0 ? 'text-danger' : 'text-success' )+"'>"+data+"</span>";
                    }
                },
                {
                    title: "Market Value (USD)",
                    field: "marketValue",
                    sorter: "number",
                    align: 'left',
                    formatter : function(row){
                        var data = row.getData().marketValue;
                        return "<span class='"+(data <= 0 ? 'text-danger' : 'text-success' )+"'>"+data+"</span>";
                    }
                },
                {
                    title: "Action",
                    field: 'id',
                    sortable: false,
                    headerSort: false,
                    formatter: actionLinks
                },
            ],
            rowFormatter: function(row) {},
            dataLoaded: function(data) {}
        });

        //Update filters on value change
        search.on('keyup', function(e){
            var toFind = $(this).val();
            if (toFind.length > 0 ) {
                searcBtn.attr('action','clear').find('.fa').removeClass('fa-search').addClass('fa-close');
                checkbox.change();   
            }else{
                searcBtn.attr('action','search').find('.fa').addClass('fa-search').removeClass('fa-close');
                widget.datatable.setData(opt.walletUrl, { coin : 'all', value : opt.checkBoxValue });    
            }
        });

        searcBtn.on('click', function(arg) {
            var textfield = $(this).parents('.input-group').find('input');
            if ($(this).attr('action') == 'clear') {
                textfield.val('');
                checkbox.change();
                searcBtn.attr('action','search').find('.fa').addClass('fa-search').removeClass('fa-close');
            } else {
                textfield.focus();
            }
        })   
        var timeout;

        checkbox.prop('checked', opt.checkBoxValue);
        
        checkbox.change(function(e) {
            var toFind = search.val();
            if (this.checked) {
                opt.checkBoxValue = 1;
                widget.datatable.setData(opt.walletUrl, { coin : toFind, value : opt.checkBoxValue });
            } else {
                opt.checkBoxValue = '';
                widget.datatable.setData(opt.walletUrl, { coin : toFind, value : opt.checkBoxValue });
            }
        });

        widget.on('click', '.btn-trade' ,function(e){
            var _this = $(this);
            var id = $(this).data('id');
            _this.btnProcessing('.');
            $.get(opt.tradeListUrl, { item_id : id })
            .done(function(response){
                    _this.popover({
                        placement:'left',
                        trigger:'focus',
                        html:true,
                        template: '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body p-0"></div></div>',
                        content: function(){
                            var content = "";
                            if (response.length > 0) {
                                for (var i = response.length - 1; i >= 0; i--) {
                                    var obj = response[i];
                                    content = content+'<a href="'+obj.link+'" class="btn-to-trade pointer-cursor btn btn-block py-1 my-0 mx-0 border-0"><small>'+obj.label+'</small></a>'
                                }
                            } else {
                                content = '<div class="m-2 p-1">No available trade.</div>';
                            }
                            return content;
                        }
                    })
                    .popover('show');
                    _this.btnReset();
            })
            .fail(function(xhr,status){
                console.error(xhr)
                _this.btnReset();
            })
        });

        return widget;
    }

    $.fn.PendingDeposit = function(param) {
        var widget = this;
        var opt = $.extend({
                pendingDepositUrl : '',
                showIn : 'USD',
                tableSelector : '',
                limit : 20
        },param);
        var table       = widget.find('.table');
        var search      = widget.find('.searchbox-input-pending');
        var tradeBtn    = widget.find('.btn-trade');

        widget.datatable = new Tabulator(opt.tableSelector, {
                    pagination:"remote",
                    paginationSize:opt.limit,
                    fitColumns:true,
                    layout:"fitColumns",
                    responsiveLayout: true,
                    index : 'time',
                    placeholder : window.Templates.noDataAvailable(),
                    data: [], //set initial table data
                    layoutColumnsOnNewData : false,
                    ajaxURL : opt.pendingDepositUrl, 
                    ajaxParams : { },
                    ajaxConfig:{
                        method: "POST", 
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': window.csrfToken.content,
                        },
                    },
                    columns: [{
                            title: "Time",
                            field: "time"
                        },
                        {
                            title: "Coins",
                            field: "coin"
                        },
                        {
                            title: "Amount",
                            field: "amount"
                        },
                        {
                            title: "Status",
                            field: "status"
                        },
                        {
                            title: "Details",
                            field: "details",
                            headerSort: false,
                        }
                    ],
                    rowFormatter : function(row){ },
                    dataLoaded : function(data){ }
        });

        // trigger search
        search.change(function(e) {
            var searchTxt = $(this).val();
            widget.datatable.replaceData(opt.pendingDepositUrl, {
                limit: opt.limit,
                text: searchTxt
            });
        });
        
        return widget;
    }
}(jQuery));

$(document).ready(function() {

    $("#card-wallet").WalletWidget({
        walletUrl: "{{route('my.wallets')}}",
        depositUrl: "{{route('my.depositForm',['coin'=>''])}}",
        withdrawUrl: "{{route('my.withdrawalForm',['coin'=>''])}}",
        tradeListUrl : "{{route('trade.links')}}",
        tableSelector: '#wallet-table',
        checkBoxValue: "{{$value}}"
    });

    $('#card-pending-deposit').PendingDeposit({
        searchUrl : "{{route('searchCoin')}}",
        pendingDepositUrl : "{{route('my.pendingDeposit')}}",
        tableSelector : '#pending-deposit-table'
    })
});

</script>
@endsection
