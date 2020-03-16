@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="card-balances">
                    <div class="card-header">External Balances</div>
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
    var balanceUrl = "{{ route('get.external.balances') }}";

    $(document).ready(function(){
        var balanceTable = new Tabulator('#balance-table', {
            pagination:"local",
            paginationSize:20,
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
                    field: "asset",
                    resizable: false,
                    sorter: 'string',
                    headerFilter:false
                },
                {
                    title: "Available Balance",
                    field: "free",
                    resizable: false,
                    headerFilter:false
                },
                {
                    title: "Pending Balance",
                    field: "locked",
                    resizable: false,
                    headerFilter:false
                },
                {
                    title: "Total Balance",
                    field: "total",
                    resizable: false,
                    headerFilter:false
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
                balanceTable.setFilter('total', '>=', 0.0000001);
            }
        });

        $('#card-balances').on('keyup', 'input.search', function(e) {
            var keyword = $(this).val()
            balanceTable.setFilter('asset', 'like', keyword);
        });
    });

</script>
@endsection

