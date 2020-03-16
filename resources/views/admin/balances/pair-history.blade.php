@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="card-balances">
                    <div class="card-header">History</div>
                    <div class="card-body">
                        <div class="card-block d-flex justify-content-lg-start mb-2 align-items-center">
                            @pairselect(['includeAll' => true, 'class' => 'w-25 mr-2 rounded-0 pair-select'])
                            @endpairselect
                            <div class="btn-group mr-2 border">
                                <button data-type="orders" class="btn btn-type {{ $type == 'orders' ? 'buzzex-active' : ''}}">Orders</button>
                                <button data-type="bids" class="btn btn-type {{ $type == 'bids' ? 'buzzex-active' : ''}}">Bids</button>
                            </div>
                            <div class="btn-group mr-2 border">
                                <a href="{{route('history.item',['ticker' => 'all', 'type' => 'deposits']) }}" class="btn">
                                    Deposits
                                </a>
                                <a href="{{route('history.item',['ticker' => 'all', 'type' => 'withdrawals']) }}" class="btn">
                                    Withdrawals
                                </a>
                            </div>
                            <div class="input-group" style="max-width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text rounded-0">User :</span>
                                </div>
                                <input type="text" id="search_for_user" class="form-control" placeholder="Search for User ID/email/name" value="{{ $user ?? ''}}">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary btn-search"  type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-block table table-sm table-striped" id="history-table">
                            
                        </div>
                        @if($type =='orders')
                        <div class="card-block my-1">
                            <ul>
                                <li><sup>1</sup> = These are orders (SELL/BUY) that has no price specified. These are fulfilled as counter orders are placed regardless of price.
                                <li><sup>2</sup> = This is the target amount of the order which shows up if the order is placed with only the target amount specified and price is dynamic. </li>
                            </ul>
                        </div>
                        @endif
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
    var historyUrl = "{{ route('history.pair',['pair_id' => $pair_id ?? 'all']) }}";
    var type = '{{$type ?? "orders"}}';
    var pair_id ='{{ $pair_id ?? "all"}}';
    var users = $('#search_for_user').val();

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

    var bidsColumns = [
        {
            title : 'Date',
            field : 'date'
        },
        {
            title : 'Pair',
            field : 'pair'
        },
        {
            title : 'Type',
            field : 'type'
        },
        {
            title : 'Price',
            field : 'price'
        },
        {
            title : 'Amount',
            field : 'amount'
        },
        {
            title : 'Total',
            field : 'total'
        },
        {
            title : 'User ID',
            field : 'user_id',
            formatter : function(row){
                var id = row.getData().user_id;
                return "<a class='btn-link' target='__blank' href='/admin/users/"+id+"'>"+id+"</a>"
            }
        }
    ];
    var ordersColumns = [
        {
            title : 'Date',
            field : 'date'
        },
        {
            title : 'Close',
            field : 'closed_date'
        },
        {
            title : 'Pair',
            field : 'pair',
            width : 80
        },
        {
            title : 'Type',
            field : 'type',
            width : 50
        },
        {
            title : 'Price',
            field : 'price'
        },
        {
            title : 'Amount',
            field : 'amount'
        },
        {
            title : 'Order ID',
            field : 'order_id',
            width : 55
        },
        {
            title : 'Completed',
            field : 'fulfilled_amount',
            width : 100
        },
        {
            title : 'Info',
            width : 20,
            formatter : function(cell){
                return "<a href='#order-details' rel='tooltip' title='Details' class='mx-1 btn btn-sm btn-info rounded-0 details-btn' onClick='showDetails(" + JSON.stringify(cell.getData()) + ")'><span class='fa fa-info'></span></a>";
            }
        }
    ];

    $(document).ready(function(){
        var pairHistoryTable = new Tabulator('#history-table', {
            pagination: "remote",
            paginationSize: 100,
            fitColumns: true,
            layout: "fitColumns",
            responsiveLayout: 'collapse',
            columnMinWidth: 80,
            placeholder: window.Templates.noDataAvailable(),
            data: [], 
            columns : type == 'orders' ? ordersColumns : bidsColumns,
            layoutColumnsOnNewData: false,
            ajaxURL: historyUrl +'/'+ type,
            ajaxParams: { user : users },
            ajaxConfig: {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                },
            }
        });
        $('#card-balances').on('click', '.btn-type', function(e) {
            $('.btn-type').btnInActive();
            $(this).btnActive();
            var type = $(this).data('type');
            if (type == 'orders') {
                pairHistoryTable.setColumns(ordersColumns);
            } else {
                pairHistoryTable.setColumns(bidsColumns);
            }
            var user = $('#search_for_user').val();
            pairHistoryTable.setData(historyUrl+'/'+type, {pair_id : pair_id, user : user});
        });

        $('#card-balances').on('change', '.pair-select', function(e){
            var value = $(this).val()
            var user = $('#search_for_user').val();
            pairHistoryTable.setData(historyUrl +'/'+ type, { pair_id : value, user : user});
        })
        .on('click', '.btn-search:not(.processing)', function(e){
            var button = $(this);
                button.btnProcessing('.');
            var user = $('#search_for_user').val();
            var value = $('.pair-select').val();
            pairHistoryTable.setData(historyUrl+'/'+type, { pair_id : value, user : user  })
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
