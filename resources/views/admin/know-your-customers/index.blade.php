@extends('masters.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Know Your Customers (<b>{{ ucwords($status) }}</b>)</div>
                <div class="card-body">
                    <div class="form-inline my-2 my-lg-0 float-left">
                        <select class="custom-select rounded-0" name="{{ uniqid()}}" id="jump-to">
                            <option {{ $status == 'pending' ? 'selected' : '' }} value="{{ route('kyc.list',['status'=>'pending', 'type' => 'all']) }}">Pending</option>
                            <option {{ $status == 'approved' ? 'selected' : '' }} value="{{ route('kyc.list',['status'=>'approved', 'type' => 'all']) }}">Approved</option>
                            <option {{ $status == 'rejected' ? 'selected' : '' }} value="{{ route('kyc.list',['status'=>'rejected', 'type' => 'all']) }}">Rejected</option>
                        </select>
                    </div>
                    {!! Form::open(['method' => 'GET', 'url' => route('kyc.list',['status'=>$status, 'type' => 'all']), 'class' => 'form-inline my-2 my-lg-0 float-right', 'role' => 'search'])  !!}
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search for name,id no., user id, email" value="{{ request('search') }}">
                        <span class="input-group-append">
                            <button class="btn btn-secondary" type="submit">
                            <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                    {!! Form::close() !!}
                    
                    <br/>
                    <br/>
                    <div class="table-responsive">
                        <table class="table tabulator table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th tabulator-formatter="html">Identification</th>
                                    <th>Nationality</th>
                                    <th>ID Number</th>
                                    <th>ID Type</th>
                                    <th tabulator-formatter="html" tabulator-width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($list)>0)
                                @foreach($list as $pending)
                                <tr>
                                    <td>{{$pending->user->first_name}} {{$pending->user->last_name}}</td>
                                    <td>{{$pending->first_name}} {{$pending->last_name}} </td>
                                    <td>{{$pending->countryNationality }}</td>
                                    <td>{{$pending->id_number}}</td>
                                    <td>{{$pending->id_type}}</td>
                                    <td>
                                        <button rel="tooltip" data-placement="left" title="View submitted info" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#kyc_verification_modal" data-remote="{{route('kyc.information_modal',['id'=>$pending->id ])}}">
                                            <i class="fa fa-user-secret" aria-hidden="true"></i>
                                        </button>
                                        <button rel="tooltip" data-placement="left" title="@if($pending->id_type=='driving-license') Front side of the ID @endif" class="btn btn-info btn-sm" data-toggle="modal" data-target="#kyc_verification_modal" data-remote="{{route('kyc.verification_modal',['id'=>$pending->id,'image'=>'front'])}}">
                                        <i class="fa fa-id-card" aria-hidden="true"></i>
                                        </button>

                                        @if($pending->id_type=='driving-license')
                                        <button rel="tooltip" data-placement="left" title="Back side of the ID" class="btn btn-info btn-sm" data-toggle="modal" data-target="#kyc_verification_modal" data-remote="{{route('kyc.verification_modal',['id'=>$pending->id,'image'=>'back'])}}">
                                        <i class="fa fa-id-card" aria-hidden="true"></i>
                                        </button>
                                        @endif
                                        
                                        <button rel="tooltip" data-placement="left" title="Selfie with the ID" class="btn btn-info btn-sm" data-toggle="modal" data-target="#kyc_verification_modal" data-remote="{{route('kyc.verification_modal',['id'=>$pending->id,'image'=>'selfie'])}}">
                                        <i class="fa fa-image" aria-hidden="true"></i>
                                        </button>
                                        
                                        @if($status!='approved')
                                        <a rel="tooltip" data-placement="left" title="Approve" class="btn btn-success btn-sm" href="{{route('kyc.action',['current'=>$status, 'action'=>'approved', 'type'=>$pending->id])}}">
                                            <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                        </a>
                                        @endif
                                        
                                        @if($status!='rejected')
                                        <a rel="tooltip" data-placement="left" title="Disapprove" class="btn btn-danger btn-sm" href="{{route('kyc.action',['current'=>$status, 'action'=>'rejected', 'type'=>$pending->id])}}">
                                            <i class="fa fa-thumbs-down" aria-hidden="true"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="pagination-wrapper">{!! $list->appends(['search' => Request::get('search')])->render() !!}</div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">
    $('body').on('click', '[data-toggle="modal"]', function(e){
        $( ".modal-content" ).empty();
        $('.tooltip').tooltip('hide');
        var button = $(this);
            button.btnProcessing('.');

        $.ajax({
            type: "get",
            url: button.attr("data-remote"),
            success: function(response){
                button.btnReset();
                swal({
                    width : '100%',
                    showCloseButton : true,
                    confirmButtonText : 'Close',
                    html : "<section class=''>"+response+"</section>"
                });
            },
            error: function (jqXHR, exception) {
                button.btnReset();
            }
        })
    })
    .on('change', '#jump-to', function(e){
        var location = $(this).val();
        var search = $("input[name='search']").val();
        if (search == '') {
            window.location = location;
        } else {
            window.location = location+'?search='+search;
        }
    })
</script>
@endsection