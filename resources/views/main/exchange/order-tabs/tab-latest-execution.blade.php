

<div class="card my-3 border-0" id="card-tab-latest-execution">
    @include('main.exchange.order-tabs.tab-menu')
    <div class="card-body">
        <div class="row my-2 my-md-1">
            <div class="col-12 col-md-12 col-lg-10 align-self-center">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-2">
                        <div class="input-group my-1">
                            <div class="input-group-prepend">
                                <label class="input-group-text border-0 bg-transparent" for="inputGroup01">{{__('Pair')}} :</label>
                            </div>
                            <input type="text" class="input-search-pair form-control rounded-0" placeholder="" id="pair">
                            <input type="hidden" id="pair_id">
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-2">
                        <div class="input-group my-1">
                            <select class="custom-select rounded-0 side-filter-select" id="side">
                                <option value="all" selected>{{__('All')}}</option>
                                <option value="buy">{{__('Buy')}}</option>
                                <option value="sell">{{__('Sell')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-3 align-self-center">
                        <div class="input-group my-1 align-self-center">
                            <div class="input-group-prepend">
                                <label class="input-group-text border-0 bg-transparent" for="fromDatepicker">{{__('From')}} :</label>
                            </div>
                            <input class="form-control rounded-0 py-0 border datepicker-input" readonly
                                placeholder="yyyy-mm-dd" id="fromDatepicker" value="">
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-3 align-self-center">
                        <div class="input-group my-1 align-self-center">
                            <div class="input-group-prepend">
                                <label class="input-group-text border-0 bg-transparent" for="toDatepicker">{{__('To')}} :</label>
                            </div>
                            <input class="form-control rounded-0 py-0  border datepicker-input" readonly
                                placeholder="yyyy-mm-dd" id="toDatepicker" value="">
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-2 align-self-center">
                        <button class="btn btn-block btn-buzzex rounded-0 btn-check-filter" data-active-tab="normal">{{ __('Check') }}</button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-2 align-self-center my-1 my-md-1 my-md-0">
                <button class="btn btn-block btn-secondary rounded-0 border btnDownload align-self-center float-md-right" type="button"><span
                    class="fa fa-download"></span> {{__('Export')}}</button>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="card-block mx-0">
            <div id="latest-execution-table" class="table table-sm border-left border-top border-right  border-bottom"></div>
        </div>
    </div>
</div>


<style type="text/css">
#card-tab-latest-execution .card-header .btn {
    min-width: 100px;
}

#card-tab-latest-execution .card-header .btn:not(.buzzex-active) {
    background: transparent;
}

#card-tab-latest-execution .datepicker-input {
    /*max-width: 120px;*/
    background-color: #fff;
}
</style>

@push('scripts')
<script type="text/javascript">
(function($) {

    $.fn.LatestExecutionTabWidget = function(param) {
        var widget = this;
        var opt = $.extend({
            baseCoin: '',
            targetCoin: '',
            showIn: 'USD',
            requestType: 'GET',
            searchUrl: '',
            requestUrl: '',
            height: 150,
            limit: 20,
            tableType: 'latest-execution',
            tableSelector: '',
            side: 'all',
            pair: '0',
            fulfilled_only : 1,
            exchangeUrl : ''
        }, param);

        var table = widget.find('.table');
        var buttons = widget.find('.btn-latest-exec');
        var FromDate = widget.find('#fromDatepicker');
        var ToDate = widget.find('#toDatepicker');
        var btnCheck = widget.find('.btn-check-filter');
        var btnDownload = widget.find('.btnDownload');
        var searchPair = widget.find('.input-search-pair');
        var sideFilter = widget.find('.side-filter-select');

        widget.tabulatorConfig = {
            height: opt.height,
            pagination: "remote",
            paginationSize: opt.limit,
            layout: "fitColumns",
            index: 'date',
            columnMinWidth: 80,
            responsiveLayout: 'collapse',
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            columns: [{
                    title: "Execution Time",
                    field: "date",
                    resizable: false,
                    headerSort: true
                },
                {
                    title: "Trading Pair",
                    field: "pair_name",
                    headerSort: true,
                    resizable: false,
                    formatter: function(cell, formatterParams, onRendered) {
                        var pair = cell.getValue().split("/");
                        return '<a href="'+opt.exchangeUrl+'?base='+pair[0]+'&target='+pair[1]+'">' + pair[1]+'/'+pair[0]+ '</a>';
                    }
                },
                {
                    title: "Side",
                    field: "side",
                    headerSort: true,
                    resizable: false
                },
                {
                    title: "Executed Price",
                    field: "price",
                    headerSort: true,
                    resizable: false
                },
                {
                    title: "Executed Amount",
                    field: "amount",
                    headerSort: true,
                    resizable: false
                },
                {
                    title: "Fees",
                    field: "fees",
                    headerSort: true,
                    resizable: false
                }
            ],
            layoutColumnsOnNewData: false,
            ajaxURL: opt.latestExecutionUrl,
            ajaxParams: {
                limit: opt.limit,
                side: opt.side,
                pair_id: opt.pair,
                target: "self",
                to: "",
                from: "",
                fulfilled_only : opt.fulfilled_only
            },
            ajaxConfig: {
                method: "GET",
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
            },
            rowFormatter: function(row) {
                var data = row.getData();
                $(row.getCell('side').getElement()).addClass(data.side == 'buy' ? 'text-success' : 'text-danger')
            },
        };

        buttons.on('click', function(e) {
            buttons.btnInActive();
            var button = $(this);
            button.btnProcessing('.');
            var target = button.data('id');
            widget.dataTable.replaceData(opt.latestExecutionUrl, {
                limit: opt.limit,
                fulfilled_only : opt.fulfilled_only
            });
            button.btnReset().btnActive();
        })

        btnCheck.on('click', function(e) {
            var button = $(this);
            var tabFilter = button.attr('data-active-tab');
            button.btnProcessing('Checking...');

            widget.init();

            button.btnReset();
        })

        searchPair.on('keyup', function(e) {
            if ($(this).val().length < 1) {
                $('#pair_id').val('');
                widget.init();
            }
        });

        sideFilter.on('change', function(e) {
            widget.init();
        });

        btnDownload.click(function() {
            widget.datatabulator.download("csv", "latest-execution.csv");
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
                widget.init();
                return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>").append("<div>" + item.label + "</div>").appendTo(ul);
        };

        widget.init = function() {

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

            var fromDate = widget.from.datepicker('getDate', true);
            var toDate = widget.to.datepicker('getDate', true);

            var config = widget.tabulatorConfig;
            config.ajaxParams.side = $('.side-filter-select').val();
            config.ajaxParams.pair_id = $('#pair_id').val();

            if ($('#fromDatepicker').val() != "") {
                config.ajaxParams.from = fromDate;
            }
            if ($('#toDatepicker').val() != "") {
                config.ajaxParams.to = toDate;
            }

            widget.datatabulator = new Tabulator(opt.tableSelector, widget.tabulatorConfig);
        }

        return widget;
    }
}(jQuery));

$(document).ready(function() {
    var LatestExecutionWidget = $("#card-tab-latest-execution").LatestExecutionTabWidget({
        height: 0,
        searchUrl: "{{route('searchPair')}}",
        latestExecutionUrl: "{{route('latestExecution')}}",
        tableSelector: '#latest-execution-table',
        exchangeUrl: "{{route('exchange')}}"
    })
    LatestExecutionWidget.init();
})
</script>
@endpush