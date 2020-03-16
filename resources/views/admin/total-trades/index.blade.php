@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="card-news">
                    <div class="card-header">Total Trades</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-md-end">
                            <div id="searchform" class="input-group w-25 align-self-center" data-url="{{ route('news.search') }}">
                                <span class="fa fa-close text-danger align-self-center"></span>
                                <input type="text" class="form-control searchbox" name="search" placeholder="Search for user...">
                                <span class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                    <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="table my-4 lists-table">
                            
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
    
    var recordsUrl = "{{ route('events.get_records')}}";
    $(document).ready(function(argument) {
        var liststable = new Tabulator('.lists-table', { 
                        fitColumns: true,
                        layout: "fitColumns",
                        responsiveLayout: true,
                        index: 'id',
                        placeholder: window.Templates.noDataAvailable(),
                        data: [],
                        layoutColumnsOnNewData: false,
                        pagination: "remote",
                        paginationSize: 100,
                        ajaxURL: recordsUrl,
                        ajaxParams: {},
                        ajaxConfig: {
                            method: "GET",
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': window.csrfToken.content,
                            },
                        },
                        columns: [
                            {
                                title : 'ID',
                                field : 'user_id',
                                width : 75
                            },
                            {
                                title : 'User',
                                field : 'user_email'
                            },
                            {
                                title : 'Total # Trades fulfilled',
                                field : 'total_trades'
                            },
                            {
                                title : 'Total Worth in BTC',
                                field : 'total_btc'
                            },
                            {
                                title : 'Total Worth in USD',
                                field : 'total_usd'
                            },

                        ],
                        rowFormatter: function(row) {},
                        dataLoaded: function(data) {}
                    });
        
        $(document).on('keyup','.searchbox', _.debounce(function() {
            var keyword = $(this).val();
                liststable.setData(recordsUrl, {user: keyword});
            }, 500)
        );
    });
</script>
@endsection