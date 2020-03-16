@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="card-balances">
                    <div class="card-header">External History</div>
                    <div class="card-body">
                        <div class="card-block d-flex justify-content-lg-start mb-2 align-items-center">
                            @coinselect(['includeAll' => true, 'class' => 'w-25 mr-2 rounded-0 coin-select'])
                            @endcoinselect
                            <!-- <div class="btn-group mr-2 border">
                                <a href="{{route('history.pair', ['pair_id' => 'all', 'type'=> 'orders'])}}" class="btn">Orders</a>
                                <a href="{{route('history.pair', ['pair_id' => 'all', 'type'=> 'bids'])}}" class="btn">Bids</a>
                            </div> -->
                            <div class="btn-group mr-2 border">
                                <button data-type="deposits" class="btn btn-type {{ $type == 'deposits' ? 'buzzex-active' : ''}}">Deposits</button>
                            </div>
                            <button class="btn btn-info btn-resync"><span class="fa fa-refresh"></span> Resync Deposit History</button>
                        </div>
                        <div class="card-block table table-sm table-striped" id="history-table">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style type="text/css">
        .swal2-popup #swal2-content {
            text-align: left !important;
        }
    </style>
@endsection


@section('scripts')
<script type="text/javascript">
    var historyUrl = "{{ route('external_history.item',['ticker' => $ticker ?? 'all']) }}";
    var resyncUrl = "{{ route('resyncDeposits') }}";
    var type = '{{$type ?? "deposits"}}';
    var ticker ='{{ $ticker ?? "all"}}';

    var showDetails = function (row) {
        swal({
            title: '<span style="font-size:17px;">Transaction Details</span>',
            buttonsStyling: false,
            confirmButtonClass: 'btn btn-sm btn-primary px-5 rounded-0',
            confirmButtonText: 'Close',
            html: '<div>'+
                        '<pre><code class="text-left">'+
                          JSON.stringify(row,null, 2)+
                        '</code></pre>'+
                    '<div>',
            width: 800,
        })
    };

    $(document).ready(function(){
        const columns = [
                {
                    title: "ID",
                    field: "id",
                    width:  30,
                    resizable: false, 
                },
                {
                    title: "TXID",
                    field: "txid",
                    width: 150,
                    resizable: false,
                },
                {
                    title: "Timestamp",
                    field: "timestamp"
                },
                {
                    title: "Address",
                    field: "address"
                },
                {
                    title: "Item",
                    width: 25,
                    field: "item"
                },
                {
                    title: "Amount",
                    field: "amount"
                },
                {
                    title: "Status",
                    field: "status",
                    width: 25,
                }
            ];
        const depositColumn = _.union(columns,[{
                title : 'Source',
                field : 'source'
            },{
                title : 'More Info',
                field : 'details',
                width : 100,
                formatter : function(cell){
                    return "<a href='#order-details' rel='tooltip' title='Details' class='mx-1 btn btn-sm btn-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(cell.getData().raw_data) + ")'>Details</a>"; 
                }
        }]);
        const withdrawalColumn = _.union(columns,[{
            title : 'More Info',
            field : 'details',
            width : 100,
            formatter : function(cell){
                return "<a href='#order-details' rel='tooltip' title='Details' class='mx-1 btn btn-sm btn-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(cell.getData()) + ")'>Details</a>"; 
            }
        }]);
        var itemHistoryTable = new Tabulator('#history-table', {
            pagination: "remote",
            paginationSize: 150,
            fitColumns: true,
            layout: "fitColumns",
            index: 'symbol',
            responsiveLayout: 'collapse',
            columnMinWidth: 80,
            placeholder: window.Templates.noDataAvailable(),
            data: [], 
            columns : type == 'deposits' ? depositColumn : withdrawalColumn,
            layoutColumnsOnNewData: false,
            ajaxURL: historyUrl+'/'+type,
            ajaxParams: { ticker : ticker },
            ajaxConfig: {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                },
            }

        });

        $('#card-balances input.switch').change(function(e) {
            if (this.checked) {
                itemHistoryTable.clearFilter();
            } else {
                itemHistoryTable.setFilter('available_balance', '>=', 0.0000001);
            }
        });

        $('#card-balances').on('click', '.btn-type', function(e) {
            $('.btn-type').btnInActive();
            $(this).btnActive();
            var type = $(this).data('type');
            if (type == 'deposits') {
                itemHistoryTable.setColumns(depositColumn);
            } else {
                itemHistoryTable.setColumns(withdrawalColumn);
            }

            itemHistoryTable.setData(historyUrl+'/'+type, {ticker : ticker});
        })
        .on('change', '.coin-select', function(e){
            var value = $(this).val()
            itemHistoryTable.setData(historyUrl+'/'+type, { ticker : value });
        })

        $('.btn-resync').on('click', function(e){
            var button = $(this);
            button.btnProcessing('Resyncing... Please wait...');
            $.post(resyncUrl,{})
            .done(function(res){
                button.btnReset();
                window.location.reload();
            })
            .fail(function (xhr, status, error) {
                 alert({
                    title: window.Templates.getXHRMessage(xhr),
                    html: window.Templates.getXHRErrors(xhr),
                    type: 'error'
                });
                button.btnReset();
            });
        })
    });
</script>
@endsection
