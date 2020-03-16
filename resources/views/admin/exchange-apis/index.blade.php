@extends('masters.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Exchange API's</div>
                <div class="card-body">
                    <a href="{{ route('exchangeapi.create') }}" class="btn btn-success" title="Add New exchange-items">
                        <i class="fa fa-plus" aria-hidden="true"></i> Add New
                    </a>


                    <br/>
                    <br/>
                    <div class="table-responsive">
                        <table class="table tabulator table-sm table-hover">
                            <thead>
                                <tr>
                                    <th tabulator-width="50">ID</th>
                                    <th tabulator-formatter="html">Name</th>
                                    <th tabulator-formatter="html">Base URL</th>
                                    <th tabulator-formatter="html">Trade URL</th>
                                    <th tabulator-formatter="html">Orderbook URL</th>
                                    <th tabulator-formatter="html">Profit Margin</th>
                                    <th tabulator-formatter="html">Balance Filter</th>
                                    <th tabulator-formatter="html">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($apis)
                                    @foreach($apis as $api)
                                        @php
                                            $filter = '';

                                            if($api->balance_filter < 0) $filter = 'Default';
                                            if($api->balance_filter == 0) $filter = 'Disabled';
                                            if($api->balance_filter > 0) $filter = 'Enabled';
                                        @endphp
                                        <tr>
                                            <td>{{$api->id}}</td>
                                            <td>{{$api->name}}</td>
                                            <td>{{$api->base_url}}</td>
                                            <td>{{$api->trade_url}}</td>
                                            <td>{{$api->orderbook_url}}</td>
                                            <td>{{$api->profit_margin}}</td>
                                            <td>{{$filter}}</td>
                                            <td>
                                                <a href="{{ route('exchangeapi.edit',['id'=>$api->id]) }}" title="Edit api settings" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                </a>
                                                <a href="{{ route('exchangeapi.destroy',['id'=>$api->id]) }}" title="Delete api settings" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="pagination-wrapper">  </div>
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
