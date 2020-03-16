@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Pending Submission</div>
                    <div class="card-body">
                        <table class="table tabulator table-sm table-hover">
                            <thead>
                                <tr>
                                    <th  tabulator-width="50">ID</th>
                                    <th tabulator-formatter="html">Icon</th>
                                    <th>Symbol</th>
                                    <th>Name</th>
                                    <th tabulator-formatter="html">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                        @foreach($pendings as $item)
                            <tr>
                                <td>{{ $item->id }} </td>
                                <td><img width="40" src="{{ $item->iconUrl }}"> </td>
                                <td>{{ $item->symbol}} </td>
                                <td>{{ $item->name }} </td>
                                <td>
                                    <a href="{{ route('project.show',['id' => $item->id]) }}" rel="tooltip" title="View details"  data-placement="bottom" class="btn btn-sm btn-info"><span class="fa fa-eye"></span></a>
                                    <a href="{{ route('project.edit',['id' => $item->id]) }}" rel="tooltip" title="Edit" data-placement="bottom" class="btn btn-sm btn-primary"><span class="fa fa-edit"></span></a>

                                    <form class="d-inline-block" action="{{ route('project.approve',['id' => $item->id]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="1">
                                        <button type="submit" data-placement="bottom" rel="tooltip" title="Add to voting list" class="btn btn-sm btn-success">
                                            <span class="fa fa-thumbs-up"></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                            </tbody>
                        </table>
                        <div class="pagination-wrapper"> {!! $pendings->appends(['search' => Request::get('search')])->render() !!} </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
