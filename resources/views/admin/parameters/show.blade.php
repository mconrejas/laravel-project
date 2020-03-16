@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Parameter {{ $parameter->id }}</div>
                    <div class="card-body">

                        <a href="{{ url('/admin/parameters') }}" title="Back"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
                        <a href="{{ url('/admin/parameters/' . $parameter->id . '/edit') }}" title="Edit Parameter"><button class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></a>
                        {!! Form::open([
                            'method'=>'DELETE',
                            'url' => ['admin/parameters', $parameter->id],
                            'style' => 'display:inline'
                        ]) !!}
                            {!! Form::button('<i class="fa fa-trash-o" aria-hidden="true"></i> Delete', array(
                                    'type' => 'submit',
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'Delete Parameter',
                                    'onclick'=>'return confirm("Confirm delete?")'
                            ))!!}
                        {!! Form::close() !!}
                        <br/>
                        <br/>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover ">
                                <tbody>
                                    <tr> <th>ID</th><td>{{ $parameter->id }}</td></tr>
                                    <tr><th> Name </th><td> {{ $parameter->name }} </td></tr>
                                    <tr><th> Value </th><td> {{ $parameter->value }} </td></tr>
                                    <tr><th> Description </th><td> {{ $parameter->description }} </td></tr>
                                    <tr><th> Code </th><td class="text-danger"> parameter('{{ $parameter->name }}'); </td></tr>
                                    <tr><th> Last updated by </th><td>{{ Buzzex\Models\User::find($parameter->updated_by)->name }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
