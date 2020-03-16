@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">User Request Deposits</div>
                    <div class="card-body">
                        <!-- <div class="card-block d-flex justify-content-lg-end mb-2 align-items-center">
                            <div class="input-group w-25">
                                <input type="text" name="{{uniqid()}}" class="form-control search" placeholder="Search..." value="">
                                <span class="input-group-append">
                                <button class="btn btn-secondary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                                </span>
                            </div>
                        </div> -->
                        <div class="card-block table table-sm table-striped" id="user-request-table">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
    var depositsUrl = "{{ route('userrequests.deposit.all') }}";

    $(document).ready(function(){
        var depositTable = new Tabulator('#user-request-table', {
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
                    title: "ID",
                    field: "request_id"
                },
                {
                    title: "User ID",
                    field: "user_id"
                }
                ,
                {
                    title: "User",
                    field: "user"
                }
                ,
                {
                    title: "API",
                    field: "api"
                },
                {
                    title: "Currency",
                    field: "item"
                },
                {
                    title: "Amount",
                    field: "amount"
                },
                {
                    title: "Created",
                    field: "created"
                }
            ],
            layoutColumnsOnNewData: false,
            ajaxURL: depositsUrl,
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

    });
</script>
@endsection