@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="card-balances">
                    <div class="card-header">Internal Balances</div>
                    <div class="card-body">
                        <div class="card-block d-flex justify-content-lg-between mb-2 align-items-center">
                            <span data-toggle="tooltip" class="switch" title="{{__('Display only those available balance greater than 0.00000001') }}">
                                <input type="checkbox" name="{{uniqid()}}" checked name="hide_cancelled" class="switch switch-sm" id="hide-cancelled">
                                <label for="hide-cancelled">{{__('Show Zero or Negative Balances') }} 
                                </label> 
                            </span>
                            <div class="input-group w-25">
                                <input type="text" name="{{uniqid()}}" class="form-control search" placeholder="Search..." value="">
                                <span class="input-group-append">
                                <button class="btn btn-secondary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                                </span>
                            </div>
                        </div>
                        <div class="card-block table table-sm table-striped" id="balance-table">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style type="text/css">
        .tabulator-cell {
            font-size: 0.8rem;
        }
    </style>
@endsection

@section('scripts')
<script type="text/javascript">
    var balanceUrl = "{{ route('balances') }}";

    $(document).ready(function(){
        var balanceTable = new Tabulator('#balance-table', {
            pagination: "remote",
            paginationSize: 150,
            fitColumns: true,
            layout: "fitColumns",
            index: 'symbol',
            responsiveLayout: 'collapse',
            columnMinWidth: 80,
            placeholder: window.Templates.noDataAvailable(),
            data: [], 
            columns : [
                {
                    title: "Coin",
                    field: "symbol",
                    width: 70,
                    resizable: false,
                    sorter: 'string',
                    headerFilter:false
                },
                {
                    title: "Deposits",
                    field: "deposits"
                },
                {
                    title: "Withdrawals",
                    field: "withdrawals"
                }
                ,
                {
                    title: "Withdrawal Fees",
                    field: "withdrawal_fee"
                }
                ,
                {
                    title: "Trade Fees",
                    field: "trade_fees"
                },
                {
                    title: "Total Balances",
                    field: "total_balance"
                },
                {
                    title: "Reserved In Orders",
                    field: "reserved_in_orders"
                },
                {
                    title: "Available Balance",
                    field: "available_balance"
                },
                {
                    title: "History",
                    field: "history",
                    width: 25,
                    resizable: false,
                    headerSort : false,
                    formatter: function(cell) {
                        return '<a rel="tooltip" title="History" class="btn btn-sm btn-secondary" href="'+cell.getValue()+'" >H</a>';
                    }
                }
            ],
            layoutColumnsOnNewData: false,
            ajaxURL: balanceUrl,
            ajaxParams: { },
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
                balanceTable.clearFilter();
            } else {
                balanceTable.setFilter('available_balance', '>=', 0.0000001);
            }
        });

        $('#card-balances').on('keyup', 'input.search', function(e) {
            var keyword = $(this).val()
            balanceTable.setFilter('symbol', 'like', keyword);
        });
    });
</script>
@endsection

