@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="card-balances">
                    <div class="card-header">Internal History</div>
                    <div class="card-body">
                        <div class="card-block d-flex justify-content-lg-start mb-2 align-items-center">
                            @coinselect(['includeAll' => true, 'class' => 'w-25 mr-2 rounded-0 coin-select'])
                            @endcoinselect
                            <div class="btn-group mr-2 border">
                                <a href="{{route('history.pair', ['pair_id' => 'all', 'type'=> 'orders'])}}" class="btn">Orders</a>
                                <a href="{{route('history.pair', ['pair_id' => 'all', 'type'=> 'bids'])}}" class="btn">Bids</a>
                            </div>
                            <div class="btn-group mr-2 border">
                                <button data-type="deposits" class="btn btn-type {{ $type == 'deposits' ? 'buzzex-active' : ''}}">Deposits</button>
                                <button data-type="withdrawals" class="btn btn-type {{ $type == 'withdrawals' ? 'buzzex-active' : ''}}">Withdrawals</button>
                            </div>
                            <div class="input-group" style="max-width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text rounded-0">User :</span>
                                </div>
                                <input type="text" id="search_for_user" class="form-control" placeholder="Search for User ID/email/name" value="{{ $user }}">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary btn-search"  type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
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
    var historyUrl = "{{ route('history.item',['ticker' => $ticker ?? 'all']) }}";
    var type = '{{$type ?? "deposits"}}';
    var ticker ='{{ $ticker ?? "all"}}';

    var showDetails = function (row) {
        swal({
            title: '<span style="font-size:17px;">Order Details</span>',
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
        var users = $('#search_for_user').val();
        const columns = [
                {
                    title: "TXID",
                    field: "txid",
                    width: 50,
                    resizable: false,
                },
                {
                    title: "Date",
                    field: "date"
                },
                {
                    title: "User ID",
                    field: "user_id",
                    formatter : function(row){
                        var id = row.getData().user_id;
                        return "<a class='btn-link' target='__blank' href='/admin/users/"+id+"'>"+id+"</a>"
                    }
                },
                {
                    title: "Item",
                    field: "item"
                },
                {
                    title: "Amount",
                    field: "amount"
                },
                {
                    title: "Status",
                    field: "status"
                }
            ];
        const depositColumn = _.union(columns,[{
            title : 'Source',
            field : 'source'
        }]);
        const withdrawalColumn = _.union(columns,[{
            title : 'More Info',
            field : 'details',
            width : 100,
            formatter : function(cell){
                return "<a href='#order-details' rel='tooltip' data-placement='left' title='Details' class='mx-1 btn btn-sm btn-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(cell.getData()) + ")'>Details</a>"; 
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
            ajaxParams: { ticker : ticker , user : users },
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
            var user = $('#search_for_user').val();
            itemHistoryTable.setData(historyUrl+'/'+type, {ticker : ticker, user : user });
        })
        .on('change', '.coin-select', function(e){
            var value = $(this).val()
            var user = $('#search_for_user').val();
            itemHistoryTable.setData(historyUrl+'/'+type, { ticker : value, user : user  });
        })
        .on('click', '.btn-search:not(.processing)', function(e){
            var button = $(this);
                button.btnProcessing('.');
            var user = $('#search_for_user').val();
            var value = $('.coin-select').val();
            itemHistoryTable.setData(historyUrl+'/'+type, { ticker : value, user : user  })
            .then(function(){
                button.btnReset();
            })
            .catch(function(error){
                button.btnReset();
            });
        })

    });
</script>
@endsection
