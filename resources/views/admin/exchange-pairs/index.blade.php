@extends('masters.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Exchange Pairs</div>
                <div class="card-body">
                    <a href="{{ route('exchange-pairs.create') }}" class="btn btn-success btn-sm" title="Add New exchange-items">
                        <i class="fa fa-plus" aria-hidden="true"></i> Add New
                    </a>
                    {!! Form::open(['method' => 'GET', 'url' => '/admin/exchange-pairs', 'class' => 'form-inline my-2 my-lg-0 float-right', 'role' => 'search', 'id'=>'filters'])  !!}
                    <div class="input-group">
                        <span data-toggle="tooltip" class="switch pt-1 mr-2" title="" data-original-title="display inactive pairs">
                            <input type="checkbox" name="inactive" class="switch switch-sm" id="show_inactive" data-toggle="switch" @if((Request::get('inactive'))) checked @endif>
                            <label for="show_inactive">Show Inactive</label> 
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ request('search') }}">
                        <span class="input-group-append">
                            <button class="btn btn-secondary" type="submit">
                            <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                    {!! Form::close() !!}
                    <br><br>
                    <div class="table-responsive">
                        <table class="table tabulator table-sm table-hover">
                            <thead>
                                <tr>
                                    <th  tabulator-width="75">Pair ID</th>
                                    <th>Symbol</th>
                                    <th>Item 1</th>
                                    <th>Item 2</th>
                                    <th tabulator-formatter="html" tabulator-width="75">Active</th>
                                    <th tabulator-formatter="html" tabulator-width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exchangePairs as $item)
                                <tr>
                                    <td>{{ $item->pair_id }}</td>
                                    <td>{{ $item->exchangeItemOne->symbol }}/{{ $item->exchangeItemTwo->symbol }}</td>
                                    <td>{{ $item->exchangeItemOne->name }}</td>
                                    <td>{{ $item->exchangeItemTwo->name }}</td>
                                    <td >
                                        @if($item->deleted == 0 )
                                            <i class='fa text-success fa-check'></i>
                                        @else
                                            <i class='fa text-danger fa-close'></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->deleted == 0 )
                                            <a href="{{ route('exchange-pairs.destroy',['id'=>$item->pair_id]) }}" title="Delist exchange pair" class="btn btn-danger btn-sm">
                                                <i class="fa fa-times" aria-hidden="true"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('exchange-pairs.inlist',['id'=>$item->pair_id]) }}" title="Inlist exchange pair" class="btn btn-success btn-sm">
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                            </a>
                                        @endif

                                        
                                        <a href="{{ url('/admin/exchange-pairs/' . $item->pair_id) }}" title="View exchange-items"  class="btn btn-info btn-sm">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </a>
                                        <a href="{{ url('/admin/exchange-pairs/' . $item->pair_id . '/edit') }}" title="Edit exchange-items"  class="btn btn-primary btn-sm">
                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i></
                                        </a>
                                        <a href="" class="btn btn-info btn-sm ml-1" title="Trade History">
                                            <i class="fa fa-history"></i>
                                        </a>
                                        <!-- {!! Form::open([
                                        'method' => 'DELETE',
                                        'url' => ['/admin/exchange-pairs', $item->pair_id],
                                        'style' => 'display:inline'
                                        ]) !!}
                                        {!! Form::button('<i class="fa fa-trash-o" aria-hidden="true"></i>', array(
                                        'type' => 'button',
                                        'class' => 'btn btn-danger btn-sm',
                                        'title' => 'Delete Iteam',
                                        'onclick'=>'confirmDelete(this)'
                                        )) !!}
                                        {!! Form::close() !!} -->
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="pagination-wrapper">
                            {!! $exchangePairs->appends(['search' => Request::get('search')])->render() !!}
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
            $(document).on('click','#show_inactive', function(){
                $('#filters').submit();
            });
        });
    </script>
@endsection