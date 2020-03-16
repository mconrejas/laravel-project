<div class="card mt-2 rounded-0 " style="min-height: 200px" id="card-current-order">
  <div class="card-header bg-transparent">
    <h5 class="lead card-title font-16 d-inline-block">{{ __('Current Orders') }}</h5>
    <button
      class="btn btn-sm btn-outline-dark mx-2 rounded buzzex-active btn-tab-current-order-limit">{{ __('Normal') }}</button>
    <button
      class="btn btn-sm btn-outline-dark mx-2 rounded btn-tab-current-order-stop-limit">{{ __('Stop-Limit')}}</button>
    <a href="{{route('orderTab',['tab'=>'current-order'])}}" class="btn-link float-right text-buzzex">More »</a>
  </div>
  <div class="card-block">
    <div class="table table-sm table-compressed" id="current-order-table"></div>
  </div>
</div>

@push('scripts')

  @guest
    <script type="text/javascript">
        var columns = [
            {title: "Order Time"},
            {title: "Type"},
            {title: "Side"},
            {title: "Price"},
            {title: "Amount"},
            {title: "Unexecuted"},
            {title: "Executed"},
            {title: "Avg. Price"},
            {title: "Action"}
        ];
        window.Templates.generateEmptyTable('#current-order-table', columns);
    </script>
  @endguest

  @auth
    <script type="text/javascript">
        (function ($) {
            $.fn.OrdersWidget = function (param) {
                var widget = this;
                var table = widget.find('.table');
                var btnCurrentLimit = widget.find('.btn-tab-current-order-limit');
                var btnCurrentStopLimit = widget.find('.btn-tab-current-order-stop-limit');
                var btnOrderNormal = widget.find('.btn-tab-order-history-normal');
                var btnOrderStopLimit = widget.find('.btn-tab-order-history-stop-limit');

                var opt = $.extend({
                    baseCoin: '',
                    targetCoin: '',
                    showIn: 'USD',
                    requestType: 'GET',
                    requestUrl: '',
                    height: 200,
                    limit: 20,
                    tableType: 'order-history', // or 'current-order'
                    tableSelector: '',
                    pair_id: '',
                    currentUser : 0
                }, param);

                var actionLinks = function (row) {
                    var data = row.getData();
                    return "<a href='#order-details' rel='tooltip' data-placement='left' title='Details' class='mx-1 btn btn-sm btn-outline-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(data) + ")'><span class='fa fa-info'></span></a>";
                };
                var actionLinksWithCancel = function (row) {
                    var data = row.getData();
                    var links =  "<a href='#order-details' rel='tooltip' data-placement='left' class='mr-1 btn btn-sm btn-outline-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(data) + ")' title='Details'><span class='fa fa-info'></span></a>";
                    if (data.completed == 0 && typeof data.order_id != 'undefined' && data.user_id == opt.currentUser) {
                        links = links + "<a href='#order-cancel' rel='tooltip' data-placement='left' class='mr-1 btn btn-sm btn-outline-danger rounded-0' onClick='cancelOrder(this,"+data.order_id+")' title='Cancel Order'><span class='fa fa-close'></span></a>";
                    }
                    return links; 
                };
                widget.CurrentOrderConfigTab1 = { //limit
                    ajaxParams: {for: opt.tableType, limit: opt.limit, pair_id: opt.pair_id},
                    columns: [
                        {title: "Order Time", field: "time", align: 'left', headerSort: false, width: 130},
                        {title: "Type", field: "type", align: 'left', headerSort: false, width:75},
                        {title: "Side", field: "side", align: 'left', headerSort: false, width:50},
                        {title: "Price(" + opt.baseCoin + ")", field: "price", align: 'left', headerSort: false},
                        {title: "Amount(" + opt.targetCoin + ")", field: "amount", align: 'left', headerSort: false},
                        {
                            title: "Unexecuted(" + opt.targetCoin + ")",
                            field: "unexecuted",
                            align: 'left',
                            headerSort: false
                        },
                        {
                            title: "Executed(" + opt.targetCoin + ")",
                            field: "executed",
                            align: 'left',
                            headerSort: false
                        },
                        {
                            title: "Total Price(" + opt.baseCoin + ")",
                            field: "avg_price",
                            align: 'left',
                            headerSort: false
                        },
                        {title: "Action", align: 'left', headerSort: false, formatter: actionLinksWithCancel }
                    ]
                };
                widget.CurrentOrderConfigTab2 = { //stop-limit
                    ajaxParams: {for: opt.tableType, stoplimit: 1, limit: opt.limit, pair_id: opt.pair_id},
                    columns: [
                        {title: "Order Time", field: "time", align: 'left', headerSort: false, width: 130},
                        {title: "Type", field: "type", align: 'left', headerSort: false, width:75},
                        {title: "Side", field: "side", align: 'left', headerSort: false, width:50},
                        {title: "Price(" + opt.baseCoin + ")", field: "price", align: 'left', headerSort: false},
                        {title: "Amount(" + opt.targetCoin + ")", field: "amount", align: 'left', headerSort: false},
                        {
                            title: "Total Price(" + opt.baseCoin + ")",
                            field: "avg_price",
                            align: 'left',
                            headerSort: false
                        },
                        {title: "Action", align: 'left', headerSort: false, formatter: actionLinksWithCancel }
                    ]
                };
                widget.OrderHistoryConfigTab1 = { //normal
                    ajaxParams: {for: opt.tableType, limit: opt.limit, pair_id: opt.pair_id, order_by: 'order_id', order_as : 'DESC'},
                    columns: [
                        {title: "Execution Time", field: "time", align: 'left', headerSort: false, width: 130},
                        {title: "Type", field: "type", align: 'left', headerSort: false, width:75},
                        {title: "Side", field: "side", align: 'left', headerSort: false, width:50},
                        {title: "Price(" + opt.baseCoin + ")", field: "price", align: 'left', headerSort: false},
                        {title: "Amount(" + opt.targetCoin + ")", field: "amount", align: 'left', headerSort: false},
                        {
                            title: "Unexecuted(" + opt.targetCoin + ")",
                            field: "unexecuted",
                            align: 'left',
                            headerSort: false
                        },
                        {
                            title: "Executed(" + opt.targetCoin + ")",
                            field: "executed",
                            align: 'left',
                            headerSort: false
                        },
                        {
                            title: "Total Price(" + opt.baseCoin + ")",
                            field: "avg_price",
                            align: 'left',
                            headerSort: false
                        },
                        {title: "Action", align: 'left', headerSort: false, formatter: actionLinks}
                    ]
                };
                widget.OrderHistoryConfigTab2 = { //stop-limit
                    ajaxParams: {for: opt.tableType, stoplimit: 1, limit: opt.limit, pair_id: opt.pair_id, order_by: 'order_id', order_as : 'DESC'},
                    columns: [
                        {title: "Order Time", field: "time", align: 'left', headerSort: false, width: 130},
                        {title: "Type", field: "type", align: 'left', headerSort: false, width:75},
                        {title: "Side", field: "side", align: 'left', headerSort: false, width:50},
                        {title: "Price(" + opt.baseCoin + ")", field: "price", align: 'left', headerSort: false},
                        {title: "Amount(" + opt.targetCoin + ")", field: "amount", align: 'left', headerSort: false},
                        {
                            title: "Stop Price(" + opt.baseCoin + ")",
                            field: "stop_price",
                            align: 'left',
                            headerSort: false
                        },
                        {title: "Execution", field: "execution", align: 'left', headerSort: false}
                    ]
                };

                widget.tabulatorConfig = {
                    height: opt.height,
                    layout: "fitColumns",
                    responsiveLayout: true,
                    placeholder: window.Templates.noDataAvailable(),
                    layoutColumnsOnNewData: false,
                    data: [],
                    rowFormatter: function (row) {
                        var data = row.getData();
                        $(row.getCell('side').getElement()).addClass(data.side == 'buy' ? 'text-success' : 'text-danger');
                    },
                    ajaxLoader:false,
                    ajaxURL: opt.requestUrl,
                    ajaxConfig: {
                        method: opt.requestType,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': window.csrfToken.content,
                        },
                    },
                    ajaxError: function (xhr, textStatus, errorThrown) {
                        if (xhr.status == 500 || xhr.status == 0 || xhr.status == 403) {
                            widget.datatabulator.clearData();
                        }
                    }
                }
                widget.datatabulator = null;

                // prepend to current orders
                widget.prependOrders = function (data) {
                    widget.datatabulator.replaceData();
                };

                widget.init = function (config = false) {
                    if (!config) {
                        config = (opt.tableType == 'order-history' ? widget.OrderHistoryConfigTab1 : widget.CurrentOrderConfigTab1);
                    }
                    widget.datatabulator = new Tabulator(opt.tableSelector, Object.assign({}, widget.tabulatorConfig, config));
                }


                if (opt.tableType == 'current-order') {
                    btnCurrentLimit.on('click', function () {
                        var button = $(this);
                        button.btnProcessing('.')
                        var config = widget.CurrentOrderConfigTab1;
                        widget.init(config);
                        button.btnReset().btnActive();
                        btnCurrentStopLimit.btnInActive();
                    });

                    btnCurrentStopLimit.on('click', function () {
                        var button = $(this);
                        button.btnProcessing('.')
                        var config = widget.CurrentOrderConfigTab2;
                        widget.init(config);
                        button.btnReset().btnActive();
                        btnCurrentLimit.btnInActive();
                    });
                } else { // order-history
                    btnOrderNormal.on('click', function () {
                        var button = $(this);
                        button.btnProcessing('.')
                        var config = widget.OrderHistoryConfigTab1;
                        widget.init(config);
                        button.btnReset().btnActive();
                        btnOrderStopLimit.btnInActive();
                    });
                    btnOrderStopLimit.on('click', function () {
                        var button = $(this);
                        button.btnProcessing('.')
                        var config = widget.OrderHistoryConfigTab2;
                        widget.init(config);
                        button.btnReset().btnActive();
                        btnOrderNormal.btnInActive();
                    });
                }
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
                    window.orderHistory.datatabulator.setData();
                    if (typeof response.balance != 'undefined') {
                        window.tradingForm.setBaseBalance(response.balance.base);
                        window.tradingForm.setTargetBalance(response.balance.target);
                    }
                    window.cardTradeDepth.refreshTable();
                    $(el).btnReset();
                    $('.tooltip').tooltip('hide');
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
                          '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Trading Price <span class="badge">'+row.price+'</span> </li>'+
                          '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Filled <span class="badge">'+row.executed+'</span> </li>'+
                          '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Fee <span class="badge">'+row.fee+'</span> </li>'+
                          '<li class="list-group-item rounded-0 d-flex justify-content-between align-items-center"> Total <span class="badge">'+row.amount+'</span> </li>'+
                        '<ul>',
                width: 800,
            })
        }
        $(document).ready(function () {

            // assigned to window to be called globally
            window.currentOrdersTable = $("#card-current-order").OrdersWidget({
                baseCoin: "{{$base}}",
                targetCoin: "{{$target}}",
                tableType: 'current-order',
                requestUrl: "{{route('currentOrder')}}",
                tableSelector: '#current-order-table',
                pair_id: '{{ $pair_id }}',
                height : 302,
                currentUser : parseInt('{{ auth()->check() ? auth()->user()->id : 0 }}')
            });

            // initialize
            window.currentOrdersTable.init();
        });
    </script>
  @endauth

@endpush