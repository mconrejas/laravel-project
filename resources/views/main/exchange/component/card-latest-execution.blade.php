<div class="card rounded-0 mt-2" id="execution-widget">
    <div class="card-block px-0 pt-3">
        <h5 class="lead card-title font-16 d-block text-left pl-2">{{ __('Latest Execution') }}</h5>
        <div class="btn-group w-100 bases">
            <button class="btn-latest-exec btn btn-sm border rounded-0 buzzex-active"
            data-id="market">{{ __('Market') }}</button>
            <button class="btn-latest-exec btn btn-sm border rounded-0" data-id="self">{{ __('Mine') }}</button>
        </div>
    </div>
    <div class="card-block px-0 table-wrapper">
        <div id="execution-widget-table" class="table table-sm border table-compressed mb-0"></div>
    </div>
</div>
@push('scripts')
<script type="text/javascript">

/**
 * Left market widget
 */
(function($) {

    $.fn.LatestExecutionWidget = function(params) {
        var widget = this;
        var opt = $.extend({
            pair_id: 0,
            baseCurrency: '',
            targetCurrency: '',
            latestExecutionUrl: '',
            tableSelector: '',
            fulfilled_only: 1,
            selfId : 0
        }, params);
        var table = widget.find('#execution-widget-table');
        var buttons = widget.find('.btn-latest-exec');

        widget.activetab = 'market';
        widget.dataTable = new Tabulator(opt.tableSelector, {
            layout: "fitColumns",
            index: 'id',
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            columns: [
                {
                    field : 'id',
                    visible: false
                },
                {
                    title: "Time",
                    field: "time",
                    align: 'left',
                    resizable: false,
                    headerSort: false
                },
                {
                    title: "Price(" + opt.baseCurrency + ")",
                    field: "price",
                    align: 'left',
                    headerSort: false,
                    resizable: false
                },
                {
                    title: "Amount(" + opt.targetCurrency + ")",
                    field: "amount",
                    align: 'right',
                    headerSort: false,
                    resizable: false
                },
            ],
            layoutColumnsOnNewData: false,
            ajaxURL: opt.latestExecutionUrl,
            ajaxParams: {
                pair_id: opt.pair_id,
                target: 'market',
                limit : 26
            },
            ajaxLoader:false,
            ajaxConfig: {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                },
            },
            rowFormatter: function(row) {
                var data = row.getData();
                $(row.getCell('price').getElement()).addClass(data.type == 'buy' ? 'text-success' : 'text-danger')
            },
            ajaxError: function(xhr, textStatus, errorThrown) {
                if (xhr.status == 500 || xhr.status == 0) {
                    widget.dataTable.clearData();
                }
            }
        });

        widget.updateTable = function(data) {
            widget.dataTable.setData(opt.latestExecutionUrl, {
                pair_id: opt.pair_id,
                target: widget.activetab,
                limit : 26
            })
        };


        buttons.on('click', function(e) {
            buttons.btnInActive();
            var button = $(this);
            button.btnProcessing('.');
            var target = button.data('id');
            widget.dataTable.setData(opt.latestExecutionUrl, {
                pair_id: opt.pair_id,
                target: target,
                limit : 26
            });
            button.btnReset().btnActive();
            widget.activetab = target;
        })

        widget.init = function() {
            // listener
            window.Echo.channel('LatestExecutionChannel_' + opt.baseCurrency + '_' + opt.targetCurrency)
                .listen('LatestExecutionEvent', (data) => {
                    // console.log('LatestExecutionEvent',data)
                    if (typeof data != 'undefined' ) {
                        widget.updateTable(data);
                    }
                });
        };
        return widget;
    }
}(jQuery));

$(document).ready(function() {
    window.latestExecutionWidget = $("#execution-widget").LatestExecutionWidget({
        pair_id: '{{$pair_id}}',
        baseCurrency: '{{$base}}',
        targetCurrency: '{{$target}}',
        latestExecutionUrl: "{{route('latestExecution')}}",
        tableSelector: '#execution-widget-table',
        selfId : parseInt('{{auth()->check() ? auth()->user()->id : 0}}')
    });

    window.latestExecutionWidget.init();

})
</script>
@endpush