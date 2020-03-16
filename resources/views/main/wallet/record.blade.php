@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-3">
            @include('main.wallet.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-records">
            	<h4 class="lead py-4 mx-auto">
                    @if ( $type == 'deposit' )
                        {{ __('Deposit Records') }}
                    @else
                        {{ __('Withdrawal Records') }}
                    @endif
                </h4>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="input-group border w-100 searchbox-wrapper">
                                <input type="text" class="form-control border-0 rounded-0 searchbox-input" placeholder="Enter coin or name">
                                <div class="input-group-append">
                                    <button class="btn bg-transparent border-0 rounded-0 border-left-0 btn-searchbox" type="button"><span class="fa fa-search"></span></button>
                                 </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 align-self-center mt-2">

                            <span data-toggle="tooltip" class="switch" title="{{__('Display approved status only') }}" {{ $type == 'withdrawal' ? 'hidden' : '' }}>
                                <input type="checkbox" name="hide_cancelled" class="switch switch-sm" id="hide-cancelled">
                                <label for="hide-cancelled">{{__('Approved Only') }} 
                                </label> 
                            </span>

                        </div>
                        <div class="col-12 col-md-4 col-lg-2 offset-lg-2 align-self-center">
                            <button type="button" class="float-md-right btn-block btn btn-sm rounded-0 btn-buzzex btn-download">
                                <span class="fa fa-download"></span> Export
                            </button>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
                <div class="card-block">
                    <div id="record-table" class="table table-sm mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
(function($) {
    $.fn.RecordsWidget = function(param) {

        var widget = this;
        var opt = $.extend({
            recordUrl: '',
            showIn: 'USD',
            type: '{{$type}}',
            searchUrl: '',
            limit: 25,
            tableSelector: ''
        }, param);
        var table = widget.find('.table');
        var search = widget.find('.searchbox-input');
        var searcBtn = widget.find('.btn-searchbox');
        var downloadBtn = widget.find('.btn-download');
        var cancelledBtn = widget.find('#hide-cancelled');
        var approved = (cancelledBtn.attr("checked") == "checked") ? 1 : 0;

        // initialize
        widget.datatable = new Tabulator(opt.tableSelector, {
            tooltips:true,
            pagination: "remote",
            paginationSize: opt.limit,
            fitColumns: true,
            layout: "fitColumns",
            responsiveLayout: 'collapse',
            columnMinWidth: 80,
            index: 'coin',
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            layoutColumnsOnNewData: false,
            ajaxURL: opt.recordUrl,
            ajaxParams: {
                limit: opt.limit,
                approved: approved,
                text: search.val(),
                type: opt.type
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
                    title: (opt.type == 'deposit' ? 'Deposit Time' : 'Withdrawal Time'),
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
                    title: (opt.type == 'deposit' ? 'Deposit Address' : 'Withdrawal Address'),
                    field: "address"
                },
                {
                    title: "Status",
                    field: "status",
                    sortable: false,
                    /*formatter: function(cell, formatterParams, onRendered) {
                        var status = '<span class="text-success py-1 px-2">approved</span>';
                        if (cell.getValue() == 'cancelled')
                            status = '<span class="text-warning py-1 px-2">cancelled</span>';
                        return status;
                    }*/
                },
                {
                    title: "TXID",
                    field: 'txid',
                    align: 'left'
                },
            ],
            rowFormatter: function(row) {},
            dataLoaded: function(data) {}
        });


        // autocomplete functionality
        search.autocomplete({
            classes: {
                "ui-autocomplete": "record-autocomplete",
            },
            source: function(request, response) {
                $.post(opt.searchUrl, {
                        term: request.term,
                    })
                    .done(function(data) {
                        response(data);
                    });
            },
            minLength: 1,
            select: function(event, ui) {
                search.val(ui.item.value);
                // replace data in table
                widget.datatable.replaceData(opt.recordUrl, {
                    limit: opt.limit,
                    text: ui.item.value,
                    type: opt.type
                });
                return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>").append("<div data-toggle='tooltip' title='" + item.label + "'><img src='"+item.icon+"' width='20'> " + item.value + "</div>").appendTo(ul);
        };

        // trigger search
        search.keyup(function(e) {
            var searchTxt = $(this).val();
            if (e.keyCode == 13 || searchTxt == "") {
                // replace data in table
                widget.datatable.replaceData(opt.recordUrl, {
                    limit: opt.limit,
                    text: searchTxt,
                    type: opt.type
                });
            }
        });

        // download entire rows to csv
        downloadBtn.on('click', function() {
            widget.datatable.download("csv", opt.type + "-data.csv");
        })

        // switch button display approved only
        cancelledBtn.on('click', function() {

            // check if switch button is checked
            if (cancelledBtn.attr("checked") == 'checked') {
                cancelledBtn.removeAttr('checked');
                widget.datatable.setFilter('status', 'like', '');
            } else {
                cancelledBtn.attr("checked", "checked");
                widget.datatable.setFilter('status', 'like', 'approved');
            }

            // replace data in table
            var searchTxt = search.val();
            widget.datatable.replaceData(opt.recordUrl, {
                limit: opt.limit,
                text: searchTxt,
                type: opt.type
            });
        });

        // put focus inside search textbox
        searcBtn.on('click', function() {
            search.focus();
        })

        return widget;
    }
}(jQuery));

// overwrite current settings
$(document).ready(function() {
    $("#card-records").RecordsWidget({
        recordUrl: "{{route('my.getRecords',['type'=>$type])}}",
        searchUrl: "{{route('searchCoin')}}",
        type: "{{$type}}",
        tableSelector: '#record-table'
    });
});

</script>
@endsection
