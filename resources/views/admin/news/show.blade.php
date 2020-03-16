@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">News {{ $news->id }}</div>
                    <div class="card-body">

                        <a href="{{ url('/admin/news') }}" title="Back"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
                        <a href="{{ url('/admin/news/' . $news->id . '/edit') }}" title="Edit News"><button class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></a>

                        @if($news->trashed())
                            <button onClick="confirmAction(this,'Confirm restore ?')" class="btn btn-sm btn-warning mr-1" rel="tooltip" data-href="{{ route('news.restore',['id' => $news->id]) }}" title="Restore News"><i class="fa fa-thumbs-o-up"></i> Restore News</button>
                        @else
                            <button onClick="confirmAction(this,'Confirm remove ?')" class="btn btn-sm btn-danger mr-1" rel="tooltip" data-href="{{ route('news.remove',['id' => $news->id]) }}" title="Remove News"><i class="fa fa-thumbs-o-down"></i> Remove News</button>
                        @endif

                        <div class="my-3 table-responsive">
                            <table class="table table-sm table-hover ">
                                <tbody>
                                    <tr><th>ID</th><td>{{ $news->id }}</td></tr>
                                    <tr><th>Link  </th><td> {{ $news->link }} </td></tr>
                                    <tr><th>Text </th><td> {!! $news->text !!} </td></tr>
                                    <tr><th>Classes </th><td> {{ $news->class }} </td></tr>
                                    <tr><th>Target </th><td> {{ $news->target == '_blank' ? 'New page' : 'Self page' }} </td></tr>
                                    <tr><th>Created by </th><td> {{ Buzzex\Models\User::find($news->created_by)->name ?? ""}} </td></tr>
                                    <tr><th>Active </th><td> {{ !$news->trashed() ? 'Yes' : 'No' }} </td></tr>
                                    <tr><th> Last updated by </th><td>{{ Buzzex\Models\User::find($news->updated_by)->name ?? "" }}</td></tr>
                                </tbody>
                            </table>
                            <div class="form-group preview">
                                <label class="font-weight-bold">Preview :</label>
                                <div class="w-100 border p-4 d-flex justify-content-between">
                                    <span class="fa fa-angle-double-left align-self-center"></span>
                                    <a href="{{ $news->link }}" target="_blank" class="{{ $news->class }} align-self-center">{!! $news->text !!}
                                    </a>
                                    <span class="fa fa-angle-double-right align-self-center"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
