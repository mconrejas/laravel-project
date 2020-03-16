@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">To be listed</div>
                    <div class="card-body">
                        <table class="table tabulator table-sm table-hover">
                            <thead>
                                <tr>
                                    <th tabulator-width="30">ID</th>
                                    <th tabulator-formatter="html">Icon</th>
                                    <th>Symbol</th>
                                    <th>Name</th>
                                    <th tabulator-formatter="html">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                        @foreach($to_be_listed as $item)
                            <tr>
                                <td>{{ $item->id }} </td>
                                <td><img width="40" src="{{ $item->iconUrl }}"> </td>
                                <td>{{ $item->symbol}} </td>
                                <td>{{ $item->name }} </td>
                                <td>
                                    <a href="{{ route('project.show',['id' => $item->id]) }}" rel="tooltip" title="View details" class="btn btn-sm btn-info"><span class="fa fa-eye"></span></a>
                                    <a href="{{ route('project.edit',['id' => $item->id]) }}" rel="tooltip" title="Edit" class="btn btn-sm btn-primary"><span class="fa fa-edit"></span></a>
                                   <!--  <a href="" rel="tooltip" title="Approved" class="btn btn-sm btn-success"><span class="fa fa-thumbs-up"></span></a> -->
                                </td>
                            </tr>
                        @endforeach
                            </tbody>
                        </table>
                        <div class="pagination-wrapper"> {!! $to_be_listed->appends(['search' => Request::get('search')])->render() !!} </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
