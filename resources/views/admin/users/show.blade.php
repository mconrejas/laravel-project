@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">User</div>
                    <div class="card-body">

                        <a href="{{ url('/admin/users') }}" title="Back"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
                        <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" title="Edit User"><button class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></a>
                        {!! Form::open([
                            'method' => 'DELETE',
                            'url' => ['/admin/users', $user->id],
                            'style' => 'display:inline'
                        ]) !!}

                        @if(parameter('admin.delete_user',0) == 1)
                        {!! Form::button('<i class="fa fa-trash-o" aria-hidden="true"></i> Delete', array(
                                    'type' => 'submit',
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'Delete User',
                                    'onclick'=>'return confirm("Confirm delete?")'
                        )) !!}
                        @endif
                        {!! Form::close() !!}
                        <br/>
                        <br/>

                        <div class="table-responsive">
                            <table class="table  table-sm table-hover ">
                                <tbody>
                                    <tr>
                                        <th>ID.</th>
                                        <td>{{ $user->id }}</td> 
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td> {{ $user->name }} </td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td> {{ $user->email }} </td>
                                    </tr>
                                    <tr>
                                        <th>Referred by</th>
                                        <td> {{ $user->referred_by ?? '' }} </td>
                                    </tr>
                                    <tr>
                                        <th>Roles</th>
                                        <td> {{ implode(", ",$user->roles->pluck('name')->toArray()) }} </td>
                                    </tr>
                                    <tr>
                                        <th>Permission</th>
                                        <td> {{ implode(", ",$user->getAllPermissions()->pluck('name')->toArray()) }} </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="card my-3">
                    <div class="card-header">
                        <span class="card-title">Account Changes History</span>
                    </div>
                    <div class="card-block">
                        <div id="account_changes_table" class="table table-sm table-hover "></div>
                        <div class="mx-2">
                            <ul>
                                <li>Empty old values indicates that the field is being created.</li>
                            </ul>
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
    var historyUrl = "{{ route('users.account-history', ['user' => $user->id ])}}";

    $(document).ready(function(){
        var table = new Tabulator('#account_changes_table', {
                layout: "fitColumns",
                responsiveLayout:"collapse",
                placeholder: window.Templates.noDataAvailable(),
                data: [],
                layoutColumnsOnNewData: false,
                ajaxParams: { user : "{{$user->id}}"},
                ajaxURL: historyUrl,
                    ajaxConfig: {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': window.csrfToken.content,
                        },
                    },
                columns : [
                    {
                        title : "ID",
                        field : 'id',
                        width : 50
                    },
                    {
                        title : "Field Affected",
                        field : 'field',
                        width : 150
                    },
                    {
                        title : "Old Value",
                        field : 'old_value',
                    },
                    {
                        title : "New Value",
                        field : 'new_value',
                    },
                    {
                        title : "Updated At",
                        field : 'updated_at',
                        width : 140
                    },
                    {
                        title : "Updated By",
                        field : 'updated_by',
                        width : 100
                    },
                    {
                        title : "Details",
                        field : 'details',
                        width : 100,
                        formatter : function(row) {
                            var data = row.getData().details;
                            return "<button data-json='"+data+"' class='btn-details btn btn-outline-dark btn-sm'><span class='fa fa-list'></span></button>"
                        }
                    },
                ],
            pagination:"remote",
            paginationSize:25,

        });

        $(document).on('click', '.btn-details', function(e){
            var json = $(this).data('json');
            if (json == "") {
                json = "{'revision' : 'No details.'}";
            }
            swal({
                title: '<span style="font-size:17px;">Changes Details</span>',
                buttonsStyling: false,
                confirmButtonClass: 'btn btn-sm btn-primary px-5 rounded-0',
                confirmButtonText: 'Close',
                html: '<div class="py-5">'+
                            '<pre><code class="text-left">'+
                              JSON.stringify(json,null, 2)+
                            '</code></pre>'+
                        '<div>',
                width: 800,
            })
        })
    })

</script>
@endsection