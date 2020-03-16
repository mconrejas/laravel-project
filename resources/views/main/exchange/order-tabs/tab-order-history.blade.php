<div class="card my-3 border-0" id="card-tab-order-history">
    @include('main.exchange.order-tabs.tab-menu')
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-12 col-lg-3 align-self-center d-flex d-lg-block">
                <button class="flex-fill btn mx-auto rounded-0 btn-tab-order-history-normal">{{__('Normal')}}</button>
                <button class="flex-fill btn mx-auto rounded-0 btn-tab-order-history-stop-limit">{{__('Stop Limit')}}</button>
            </div>
            <div class="col-12 col-md-12 col-lg-2 offset-lg-7 align-self-center my-1 my-md-1 my-lg-0">
                <button class="btn rounded-0 btn-block border border-secondary btnDownload">
                    <span class="fa fa-download"></span> {{__('Export')}}
                </button>
            </div>
        </div>
        <div class="row my-md-2">
            <div class="col-12 col-md-12 col-lg-3 align-self-center">
                <div class="input-group my-1" >
                    <div class="input-group-prepend">
                        <label class="input-group-text border-0 bg-transparent" for="inputGroup01">{{__('Trading Pair')}} :</label>
                    </div>
                    <input type="text" class="input-search-pair form-control rounded-0" placeholder="" id="pair">
                    <input type="hidden" id="pair_id">
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-1 align-self-center">
                <div class="input-group my-1">
                    <select class="custom-select rounded-0 side-filter-select" id="side">
                        <option value="all" selected>{{__('All')}}</option>
                        <option value="buy">{{__('Buy')}}</option>
                        <option value="sell">{{__('Sell')}}</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-2 align-self-center">
                <div class="input-group my-1">
                    <div class="input-group-prepend">
                        <label class="input-group-text border-0 bg-transparent" for="fromDatepicker">{{__('From')}} :</label>
                    </div>
                    <input class="form-control rounded-0 datepicker-input" readonly
                        placeholder="yyyy-mm-dd" id="fromDatepicker" value="">
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-2 align-self-center">
                <div class="input-group my-1">
                    <div class="input-group-prepend">
                        <label class="input-group-text border-0 bg-transparent" for="toDatepicker">{{__('To')}} :</label>
                    </div>
                    <input class="form-control rounded-0 align-self-center py-0  border datepicker-input" readonly
                        placeholder="yyyy-mm-dd" id="toDatepicker" value="">
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-2 align-self-center my-1">
                <button class="btn-block btn btn-buzzex rounded-0 btn-check-filter" data-active-tab="normal">
                    {{ __('Check') }}
                </button>
            </div>
        </div>
        <div class="card-block mx-0">
            <div id="order-history-table" class="table table-sm  border-top"></div>
        </div>
    </div>
</div>
<style type="text/css">
    #card-tab-order-history .card-header .btn {
        min-width: 100px;
    }
    #card-tab-order-history .card-header .btn:not(.buzzex-active) {
        background: transparent;
    }
    #card-tab-order-history .datepicker-input {
        background-color: #fff;
    }
</style>



@push('scripts')
<script type="text/javascript">
(function($) {
    $.fn.OrderHistoryWidget = function(param) {
        var widget = this;
        var table = widget.find('.table');
        var btnOrderNormal = widget.find('.btn-tab-order-history-normal');
        var btnOrderStopLimit = widget.find('.btn-tab-order-history-stop-limit');
        var FromDate = widget.find('#fromDatepicker');
        var ToDate = widget.find('#toDatepicker');
        var btnCheck = widget.find('.btn-check-filter');
        var btnDownload = widget.find('.btnDownload');
        var searchPair = widget.find('.input-search-pair');
        var sideFilter = widget.find('.side-filter-select');
        widget.active = 'normal';
        var opt = $.extend({
            baseCoin: '',
            targetCoin: '',
            showIn: 'USD',
            requestType: 'GET',
            searchUrl: '',
            requestUrl: '',
            exchangeUrl : '',
            height: 150,
            limit: 20,
            tableType: 'order-history',
            tableSelector: '',
            side: 'all',
            pair: ''
        }, param);

        var actionLinks = function (row) {
            var data = row.getData();
            return "<a href='#order-details' rel='tooltip' data-placement='right' title='Details' class='mx-1 btn btn-sm btn-outline-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(data) + ")'><span class='fa fa-info'></span></a>";
        };

        widget.OrderHistoryConfigTab1 = { //normal
            ajaxParams: {
                for: opt.tableType,
                filter: 'normal',
                limit: opt.limit,
                side: opt.side,
                pair: opt.pair,
                order_by : 'order_id',
                order_as : 'desc'
            },
            columns: [{
                    title: "Execution Time",
                    field: "time",
                    headerClick : function(e, columns) {
                        var currentSort = $(columns.getElement()).attr('aria-sort');
                        widget.sortBy(currentSort == 'desc' ? 'asc' : 'desc');
                    }
                },
                {
                    title: "Trading Pair",
                    field: "pair_name",
                    headerSort: true,
                    formatter: function(cell, formatterParams, onRendered) {
                        var pair = cell.getValue().split("/");
                        return '<a href="'+opt.exchangeUrl+'?base='+pair[1]+'&target='+pair[0]+'">' + cell.getValue() + '</a>';
                    }
                },
                {
                    title: "Type",
                    field: "type",
                    headerSort: true
                },
                {
                    title: "Side",
                    field: "side",
                    headerSort: true
                },
                {
                    title: "Price",
                    field: "price",
                    headerSort: true
                },
                {
                    title: "Amount",
                    field: "amount",
                    headerSort: true
                },
                {
                    title: "Unexecuted",
                    field: "unexecuted",
                    headerSort: true
                },
                {
                    title: "Executed",
                    field: "executed",
                    headerSort: true
                },
                {
                    title: "Total Price",
                    field: "avg_price",
                    headerSort: true
                },
                {
                    title: "Action",
                    headerSort: false,
                    formatter: actionLinks
                }
            ]
        };
        widget.OrderHistoryConfigTab2 = { //stop-limit
            ajaxParams: {
                for: 'order-history',
                filter: 'stop-limit',
                limit: opt.limit,
                order_by : 'order_id',
                order_as : 'desc'
            },
            columns: [{
                    title: "Order Time",
                    field: "time",
                    headerClick : function(e, columns) {
                        var currentSort = $(columns.getElement()).attr('aria-sort');
                        widget.sortBy(currentSort == 'desc' ? 'asc' : 'desc');
                    }
                },
                {
                    title: "Trading Pair",
                    field: "pair_name",
                    headerSort: true
                },
                {
                    title: "Type",
                    field: "type",
                    headerSort: true
                },
                {
                    title: "Side",
                    field: "side",
                    headerSort: true
                },
                {
                    title: "Price",
                    field: "price",
                    headerSort: true
                },
                {
                    title: "Amount",
                    field: "amount",
                    headerSort: true
                },
                {
                    title: "Stop Price",
                    field: "stop_price",
                    headerSort: true
                },
                {
                    title: "Execution",
                    field: "execution",
                    headerSort: true
                }
            ]
        };

        widget.tabulatorConfig = {
            height: opt.height,
            pagination: "remote",
            paginationSize: opt.limit,
            layout: "fitColumns",
            columnMinWidth: 80,
            responsiveLayout: 'collapse',
            placeholder: window.Templates.noDataAvailable(),
            layoutColumnsOnNewData: false,
            data: [],
            rowFormatter: function(row) {
                var data = row.getData();
                $(row.getCell('side').getElement()).addClass((data.side == 'buy') ? 'text-success' : 'text-danger')
            },
            dataLoaded: function(data) {},
            ajaxURL: opt.requestUrl,
            ajaxConfig: {
                method: opt.requestType,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                },
            },
            ajaxResponse:function(url, params, response){
                if (typeof response == 'undefined') {
                    return [];
                }
                return response;
            }
        }

        widget.getConfig = function() {
            return widget.active == 'normal' ? widget.OrderHistoryConfigTab1 : widget.OrderHistoryConfigTab2;
        };

        widget.datatabulator = null;

        widget.init = function() {
            var config = widget.OrderHistoryConfigTab1;
            config.ajaxParams.side = $('#side').val();
            config.ajaxParams.pair = $('#pair_id').val();
            widget.active = 'normal';
            widget.datatabulator = new Tabulator(opt.tableSelector, Object.assign({}, widget.tabulatorConfig, widget.OrderHistoryConfigTab1));
            btnOrderNormal.btnActive();

            var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());


            widget.from = FromDate.datepicker({
                    autoHide: true,
                    inline: false,
                    format: 'yyyy-mm-dd',
                    endDate: today
                })
                .on('pick.datepicker', function(e) {
                    widget.to.datepicker('setStartDate', e.date);
                });

            widget.to = ToDate.datepicker({
                    autoHide: true,
                    inline: false,
                    format: 'yyyy-mm-dd',
                    endDate: today
                })
                .on('pick.datepicker', function(e) {
                    widget.from.datepicker('setEndDate', e.date);
                });

        }


        btnOrderNormal.on('click', function() {
            var button = $(this);
            button.btnProcessing('.')
            var config = widget.OrderHistoryConfigTab1;
            widget.datatabulator = new Tabulator(opt.tableSelector, Object.assign({}, widget.tabulatorConfig,config));
            widget.active = 'normal';
            button.btnReset().btnActive();
            btnOrderStopLimit.btnInActive();
            btnCheck.attr('data-active-tab', 'normal');
        });

        btnOrderStopLimit.on('click', function() {
            var button = $(this);
            button.btnProcessing('.')
            var config = widget.OrderHistoryConfigTab2;
            widget.datatabulator = new Tabulator(opt.tableSelector, Object.assign({}, widget.tabulatorConfig,config));
            widget.active = 'stop-limit';
            button.btnReset().btnActive();
            btnOrderNormal.btnInActive();
            btnCheck.attr('data-active-tab', 'stop-limit');
        });

        btnCheck.on('click', function(e) {
            var button = $(this);
            var tabFilter = button.attr('data-active-tab');
            button.btnProcessing('Checking...');
            var fromDate = widget.from.datepicker('getDate', true);
            var toDate = widget.to.datepicker('getDate', true);
            widget.datatabulator.setData(opt.requestUrl, {
                for: 'order-history',
                filter: tabFilter,
                limit: opt.limit,
                from: fromDate,
                to: toDate,
                side: $('#side').val(),
                pair: $('#pair_id').val(),
            });
            button.btnReset();
        });

        sideFilter.on('change', function(e) {
            var config = widget.getConfig();
            config.ajaxParams.side = $(this).val();
            config.ajaxParams.pair = $('#pair_id').val();
            widget.datatabulator.setData(opt.requestUrl, config.ajaxParams);
        });

        btnDownload.click(function() {
            widget.datatabulator.download("csv", "order-history.csv");
        });

        searchPair.on('keyup', function(e){
            if ($(this).val().length == 0) {
                var config = widget.getConfig();
                config.ajaxParams.side = $("#side").val();
                config.ajaxParams.pair = null;
                widget.datatabulator.setData(opt.requestUrl,config.ajaxParams);
            }
        });

        searchPair.autocomplete({
            classes: {
                "ui-autocomplete": "left-market-autocomplete",
            },
            source: function(request, response) {
                $.post(opt.searchUrl, {
                        term: request.term,
                    })
                    .done(function(data) {
                        response(data);
                    });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#pair').val(ui.item.label);
                $('#pair_id').val(ui.item.value);
                var config = widget.getConfig();
                config.ajaxParams.side = $("#side").val();
                config.ajaxParams.pair = ui.item.value;
                widget.datatabulator.setData(opt.requestUrl,config.ajaxParams);
                return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>").append("<div>" + item.label + "</div>").appendTo(ul);
        };

        widget.sortBy = function(sort) {
            var config = widget.getConfig();
            var fromDate = widget.from.datepicker('getDate', true);
            var toDate = widget.to.datepicker('getDate', true);
            config.ajaxParams.side = sideFilter.val();
            config.ajaxParams.pair = $('#pair_id').val();
            config.ajaxParams.order_as = sort;
            config.ajaxParams.from = fromDate ? fromDate : '',
            config.ajaxParams.to = toDate ? toDate : '',
            widget.datatabulator.setData(opt.requestUrl, config.ajaxParams);
        }
        return widget;
    }
}(jQuery));

    var showDetails = function (row) {
        swal({
            title: '<span style="font-size:17px;">Order Details</span>',
            buttonsStyling: false,
            confirmButtonClass: 'btn btn-sm btn-primary px-5 rounded-0',
            confirmButtonText: 'Close',
            html: '<ul class="list-group">'+
                        '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Date <span class="badge">'+row.time+'</span> </li>'+
                        '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Trading Price <span class="badge">'+row.price+'</span> </li>'+
                        '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Filled <span class="badge">'+row.executed+'</span> </li>'+
                        '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Fee <span class="badge">'+row.fee+'</span> </li>'+
                        '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Total <span class="badge">'+row.amount+'</span> </li>'+
                    '<ul>',
            width: 800,
        })
    }

$(document).ready(function() {
    $("#card-tab-order-history").OrderHistoryWidget({
        height: 0,
        searchUrl: "{{route('searchPair')}}",
        requestUrl: "{{route('orderHistory')}}",
        tableSelector: '#order-history-table',
        exchangeUrl: "{{route('exchange')}}"
    }).init();
});
</script>
@endpush