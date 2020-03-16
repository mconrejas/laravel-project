@extends('masters.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Exchange Items</div>
                <div class="card-body">
                    <a href="{{ url('/admin/exchange-items/create') }}" class="btn btn-success" title="Add New exchange-items">
                        <i class="fa fa-plus" aria-hidden="true"></i> Add New
                    </a>
                    {!! Form::open(['method' => 'GET', 'url' => '/admin/exchange-items', 'class' => 'form-inline my-2 my-lg-0 float-right', 'role' => 'search', 'id'=>'filters'])  !!}
                    <div class="input-group">
                        <span data-toggle="tooltip" class="switch pt-1 mr-2" title="" data-original-title="display deleted items">
                            <input type="checkbox" name="deleted" class="switch switch-sm" id="show_deleted" data-toggle="switch" @if((Request::get('deleted'))) checked @endif>
                            <label for="show_deleted">Show Deleted</label> 
                        </span>
                        <select class="form-control mr-1" id="item_type" name="type">
                            <option value="all">All Types</option>
                            @if(exchangeTypeOptions())
                                @foreach(exchangeTypeOptions() as $index => $exchangeType)
                                    <option value="{{$index}}" @if(( (Request::get('type')==$index && Request::get('type') != 'all') && Request::get('type') != '' )) selected @endif>{{$exchangeType}}</option>
                                @endforeach
                            @endif
                        </select>
                        <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ request('search') }}">
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
                                    <th tabulator-width="50">ID</th>
                                    <th tabulator-formatter="html">Icon</th>
                                    <th tabulator-formatter="html">Name</th>
                                    <th>Symbol</th>
                                    <th tabulator-formatter="html">Type</th>
                                    <th tabulator-formatter="html">Alt Deposit</th>
                                    <th tabulator-formatter="html">Exchange API</th>
                                    <th tabulator-formatter="html">Active</th>
                                    <th tabulator-formatter="html">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exchangeItems as $item)
                                @php
                                    $filter = '';

                                    if($item->alternative_deposit < 0) $filter = 'Default';
                                    if($item->alternative_deposit == 0) $filter = 'Disabled';
                                    if($item->alternative_deposit > 0) $filter = 'Enabled';
                                @endphp
                                <tr>
                                    <td>{{ $item->item_id }}</td>
                                    <td>
                                        <img src="{{ $item->iconUrl }}" height="30">
                                    </td>
                                    <td><span data-toggle="tooltip" title="{{$item->name}}">{{ $item->name }}</span></td>
                                    <td>{{ $item->symbol }}</td>
                                    <td><span data-toggle="tooltip" title="{{ exchangeTypeOptions()[$item->type] }}">{{ exchangeTypeOptions()[$item->type] }}</span></td>
                                    <td>
                                        {{ $filter }}
                                    </td>
                                    <td>
                                        {{ $item->getExchangeApi() != null ? ucfirst($item->getExchangeApi()->name) : "N/A" }}
                                    </td>
                                    <td>
                                        @if($item->deleted == 0 )
                                            <i class='fa text-success fa-check'></i>
                                        @else
                                            <i class='fa text-danger fa-close'></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->deleted == 0 )
                                            <a href="{{ route('exchange-items.destroy',['id'=>$item->item_id]) }}" title="Deactivate exchange item" class="btn btn-danger btn-sm">
                                                <i class="fa fa-times" aria-hidden="true"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('exchange-items.inlist',['id'=>$item->item_id]) }}" title="Activate exchange item" class="btn btn-success btn-sm">
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                            </a>
                                        @endif

                                        <a href="{{ route('exchange-items.show',['id'=>$item->item_id]) }}" title="View exchange item" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </a>
                                        <a href="{{ route('exchange-items.edit',['id'=>$item->item_id]) }}" title="Edit exchange item" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                        </a>
                                        <a data-placement="left" href="{{ route('exchangeitems.uploadForm',['id'=>$item->item_id]) }}" rel="tooltip" title="Upload Icon" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-upload" aria-hidden="true"></i>
                                        </a>
                                        <!-- {!! Form::open([
                                        'method' => 'DELETE',
                                        'url' => ['/admin/exchange-items', $item->item_id],
                                        'style' => 'display:inline'
                                        ]) !!}
                                        {!! Form::button('<i class="fa fa-trash-o" aria-hidden="true"></i>', array(
                                        'type' => 'button',
                                        'class' => 'btn btn-danger btn-sm',
                                        'title' => 'Delete Item',
                                        'onclick'=>'confirmDelete(this)'
                                        )) !!}
                                        {!! Form::close() !!} -->
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="pagination-wrapper"> {!! $exchangeItems->appends(['search' => Request::get('search')])->render() !!} </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function() {
            $(document).on('change','#item_type', function(){
                $('#filters').submit();
            });

            $(document).on('click','#show_deleted', function(){
                $('#filters').submit();
            });
        });
    </script>
@endsection