@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Edit Parameter #{{ $parameter->id }}</div>
                    <div class="card-body">
                        <a href="{{ url('/admin/parameters') }}" title="Back"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
                        <br />
                        <br />

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        {!! Form::model($parameter, [
                            'method' => 'PATCH',
                            'url' => ['/admin/parameters', $parameter->id],
                            'class' => 'form-horizontal',
                            'files' => true
                        ]) !!}

                        @include ('admin.parameters.form', ['formMode' => 'edit'])

                        {!! Form::close() !!}

                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <ul>
                                <li>Naming convention: lowercase, no space (use dot or underscore)</li>
                                <li>Use numeric value for boolean values. e.g. 0 for false, 1 for true</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
