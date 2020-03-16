@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Create New News</div>
                    <div class="card-body">
                        <a href="{{ url('/admin/news') }}" title="Back"><button class="btn btn-warning btn-sm px-3"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
                        <br />
                        <br />

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        {!! Form::open([
                            'url' => '/admin/news', 
                            'class' => 'form-horizontal news-form', 
                            'files' => false
                            ]) !!}

                        @include ('admin.news.form', ['formMode' => 'create'])

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
