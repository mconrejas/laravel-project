<div class="card my-3 border-0" id="card-tab-current-order">
    @include('main.exchange.order-tabs.tab-menu')
    <div class="card-body">
        <div class="row my-md-2">
            <div class="col-12 col-md-12 col-lg-3 align-self-center d-flex d-lg-block">
                <button class="flex-fill btn mx-auto rounded-0 btn-tab-current-order-limit">{{__('Normal')}}</button>
                <button class="flex-fill btn mx-auto rounded-0 btn-tab-current-order-stop-limit">{{__('Stop Limit')}}</button>
            </div>
            <div class="col-12 col-md-12 col-lg-3 align-self-center">
                <div class="input-group my-1">
                    <div class="input-group-prepend">
                        <label class="input-group-text border-0 bg-transparent" for="inputGroup01">{{__('Trading Pair')}} :</label>
                    </div>
                    <input type="text" class="form-control rounded-0 input-search-pair" placeholder="" id="pair">
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
        </div>
        <div class="card-block mx-0">
            <div id="current-order-table" class="table table-sm border-top"></div>
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
</style>



@push('scripts')
<script type="text/javascript">
(function($) {
    $.fn.CurrentOrdersWidget = function(param) {
        var widget = this;
        var table = widget.find('.table');
        var btnCurrentLimit = widget.find('.btn-tab-current-order-limit');
        var btnCurrentStopLimit = widget.find('.btn-tab-current-order-stop-limit');
        var searchPair = widget.find('.input-search-pair');
        var sideFilter = widget.find('.side-filter-select');
        widget.active = 'normal';

        var opt = $.extend({
            baseCoin: '',
            targetCoin: '',
            showIn: 'USD',
            searchUrl: '',
            requestType: 'GET',
            requestUrl: '',
            height: 200,
            limit: 20,
            tableSelector: '',
            tableType: 'current-order',
            side: 'all',
            pair: '',
            currentUser : 0,
            exchangeUrl : ''
        }, param);

        var actionLinks = function (row) {
            var data = row.getData();
            var links =  "<a href='#order-details' rel='tooltip' data-placement='left' class='mr-1 btn btn-sm btn-outline-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(data) + ")' title='Details'><span class='fa fa-info'></span></a>";
            if (data.completed == 0 && typeof data.order_id != 'undefined' && data.user_id == opt.currentUser) {
                links = links + "<a href='#order-cancel' rel='tooltip' data-placement='left' class='mr-1 btn btn-sm btn-outline-danger rounded-0' onClick='cancelOrder(this,"+data.order_id+")' title='Cancel Order'><span class='fa fa-close'></span></a>";
            }
            return links; 
        };

        widget.getConfig = function() {
            return widget.active == 'normal' ? widget.CurrentOrderConfigTab1 : widget.CurrentOrderConfigTab2;
        };

        widget.CurrentOrderConfigTab1 = { //limit
            ajaxParams: {
                for: opt.tableType,
                filter: 'limit',
                limit: opt.limit
            },
            columns: [{
                    title: "Order Time",
                    field: "time",
                    headerSort: true
                },
                {
                    title: "Trading Pair",
                    field: "pair_name",
                    align: "left",
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
                    field: 'id',
                    headerSort: false,
                    formatter: actionLinks
                }
            ]
        };
        widget.CurrentOrderConfigTab2 = { //stop-limit
            ajaxParams: {
                for: opt.tableType,
                filter: 'stop-limit',
                limit: opt.limit
            },
            columns: [{
                    title: "Order Time",
                    field: "time",
                    headerSort: true
                },
                {
                    title: "Trading Pair",
                    field: "pair_name",
                    align: "left",
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
                    title: "Avg. Price",
                    field: "avg_price",
                    headerSort: true
                },
                {
                    title: "Action",
                    field: 'id',
                    headerSort: false,
                    formatter: actionLinks
                }
            ]
        };

        widget.tabulatorConfig = {
            pagination: "remote",
            paginationSize: opt.limit,
            height: opt.height,
            layout: "fitColumns",
            columnMinWidth: 80,
            responsiveLayout: 'collapse',
            placeholder: window.Templates.noDataAvailable(),
            layoutColumnsOnNewData: false,
            data: [],
            rowFormatter: function(row) {
                var data = row.getData();
                $(row.getCell('side').getElement()).addClass(data.side == 'buy' ? 'text-success' : 'text-danger');
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
        widget.datatabulator = null;

        widget.init = function() {
            var config = widget.CurrentOrderConfigTab1;
            config.ajaxParams.side = $('#side').val();
            config.ajaxParams.pair = $('#pair').val();

            widget.datatabulator = new Tabulator(opt.tableSelector, Object.assign({}, widget.tabulatorConfig,config));
            btnCurrentLimit.btnActive();
            widget.active = 'normal';
        }


        btnCurrentLimit.on('click', function() {
            var button = $(this);
            button.btnProcessing('.')
            var config = widget.CurrentOrderConfigTab1;
            widget.datatabulator = new Tabulator(opt.tableSelector, Object.assign({}, widget.tabulatorConfig, config));
            button.btnReset().btnActive();
            btnCurrentStopLimit.btnInActive();
            widget.active = 'normal';
        });

        btnCurrentStopLimit.on('click', function() {
            var button = $(this);
            button.btnProcessing('.')
            var config = widget.CurrentOrderConfigTab2;
            widget.datatabulator = new Tabulator(opt.tableSelector, Object.assign({}, widget.tabulatorConfig, config));

            button.btnReset().btnActive();
            btnCurrentLimit.btnInActive();
            widget.active = 'stop-limit';
        });

        sideFilter.on('change', function(e) {
            var config = widget.getConfig();
            config.ajaxParams.side = $(this).val();
            config.ajaxParams.pair = $('#pair_id').val();
            widget.datatabulator.setData(opt.requestUrl, config.ajaxParams);
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

        return widget;
    }
}(jQuery));

var cancelOrder = function(el, orderId) {
    $(el).btnProcessing('.');
    confirmation('Cancel this order ?', function(){
        $.post("{{ route('order.cancelOrder') }}", {
            order_id : orderId                
        })
        .done(function(response){
            window.currentOrdersTable.datatabulator.setData();
            $(el).btnReset();
        })
        .fail(function (xhr, status, error) {
            alert({
                title: window.Templates.getXHRMessage(xhr),
                html: window.Templates.getXHRErrors(xhr),
                type: 'error'
            });
            $(el).btnReset();
        });
    },function(){
        $(el).btnReset();
    })
}

var showDetails = function (row) {
    swal({
        title: '<span style="font-size:17px;">Order Details</span>',
        buttonsStyling: false,
        confirmButtonClass: 'btn btn-sm btn-primary px-5 rounded-0',
        confirmButtonText: 'Close',
        html: '<ul class="list-group">'+
                  '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Date <span class="badge">'+row.time+'</span> </li>'+
                  '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Remarks <span class="badge">'+(typeof row.remarks == 'undefined' || row.remarks == null ? '' : row.remarks)+'</span> </li>'+
                  '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Filled <span class="badge">'+row.executed+'</span> </li>'+
                  '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Fee <span class="badge">'+row.fee+'</span> </li>'+
                  '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Total <span class="badge">'+row.amount+'</span> </li>'+
                '<ul>',
        width: 800,
    })
}
$(document).ready(function() {

    window.currentOrdersTable = $("#card-tab-current-order").CurrentOrdersWidget({
        height: 0,
        searchUrl: "{{route('searchPair')}}",
        requestUrl: "{{route('currentOrder')}}",
        tableSelector: '#current-order-table',
        exchangeUrl: "{{route('exchange')}}",
        currentUser : parseInt('{{ auth()->check() ? auth()->user()->id : 0 }}')
    });

    window.currentOrdersTable.init();
});
</script>
@endpush