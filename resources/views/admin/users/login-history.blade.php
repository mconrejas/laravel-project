@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card my-3">
                    <div class="card-header">
                        <span class="card-title">Login History for <b>{{$user->email}} ({{$user->id}})</b></span>
                    </div>
                    <div class="card-body p-0">
                        <div id="login-history-table" class="mx-0 table-compressed"></div>
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
    var loginHistoryurl = "{{ route('users.login-records',['user' => $id]) }}";

    $(document).ready(function(){
        $logintable = new Tabulator('#login-history-table', {
            tooltips:true,
            pagination: "remote",
            paginationSize: 50,
            fitColumns: true,
            layout: "fitColumns",
            responsiveLayout: 'collapse',
            columnMinWidth: 80,
            // index: 'time',
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            layoutColumnsOnNewData: false,
            ajaxURL: loginHistoryurl,
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
                    title: 'Sign-in Time',
                    field: "time",
                    width : 150
                },
                {
                    title: "IP",
                    field: "ip",
                    width : 150
                },
                {
                    title: "Device",
                    field: "device"
                },
                {
                    title: "Location",
                    field: "location"
                },
                {
                    title: "Details",
                    field: "details",
                    sortable: false,
                    width: 100,
                    formatter : function(cell, formatterParams, onRendered){
                        var data = cell.getData().details;
                        return "<a data-json='"+data+"' class='btn-view-info btn btn-sm btn-outline-secondary' title='View details'><i class='fa fa-eye'></i></span>";
                    }
                },
            ],
            rowFormatter: function(row) {},
            dataLoaded: function(data) {}
        });

        $(document).on('click','.btn-view-info', function(){
            var data = $(this).data('json');
            swal({
                title: '<span style="font-size:17px;">Login Details</span>',
                buttonsStyling: false,
                confirmButtonClass: 'btn btn-sm btn-primary px-5 rounded-0',
                confirmButtonText: 'Close',
                html: '<div>'+
                            '<pre><code class="text-left">'+
                              JSON.stringify(data,null, 2)+
                            '</code></pre>'+
                        '<div>',
                width: 800,
            })
        })
    });
</script>
@endsection
