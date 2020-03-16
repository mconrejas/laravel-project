@extends('masters.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-block p-3">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-warning"><span class="fa fa-arrow-left"></span> Back</a>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-primary"><span class="fa fa-edit"></span> Edit</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('admin.coin-projects.form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

