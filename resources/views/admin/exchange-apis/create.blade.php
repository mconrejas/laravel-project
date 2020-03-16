@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Edit External Api  </div>
                    <div class="card-body">

                        <a href="{{ route('exchangeapi') }}" title="Back" class="btn btn-warning btn-sm">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back
                        </a>
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
                            'method' => 'POST',
                            'url' => route('exchangeapi.store'),
                            'class' => 'form-horizontal',
                            'files' => true
                        ]) !!}

                        <div class="col-md-6 form-group{{ $errors->has('name') ? ' has-error' : ''}}">
                            {!! Form::label('name', 'Name: ', ['class' => 'control-label']) !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                        </div>

                        <div class="col-md-6 form-group{{ $errors->has('base_url') ? ' has-error' : ''}}">
                            {!! Form::label('base_url', 'Base URL: ', ['class' => 'control-label']) !!}
                            {!! Form::text('base_url', null, ['class' => 'form-control', 'required' => 'required']) !!}
                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                        </div>

                        <div class="col-md-6 form-group{{ $errors->has('trade_url') ? ' has-error' : ''}}">
                            {!! Form::label('trade_url', 'Trade URL: ', ['class' => 'control-label']) !!}
                            {!! Form::text('trade_url', null, ['class' => 'form-control', 'required' => 'required']) !!}
                            {!! $errors->first('trade_url', '<p class="help-block">:message</p>') !!}
                        </div>

                        <div class="col-md-6 form-group{{ $errors->has('orderbook_url') ? ' has-error' : ''}}">
                            {!! Form::label('orderbook_url', 'Orderbook URL: ', ['class' => 'control-label']) !!}
                            {!! Form::text('orderbook_url', null, ['class' => 'form-control', 'required' => 'required']) !!}
                            {!! $errors->first('orderbook_url', '<p class="help-block">:message</p>') !!}
                        </div>

                        <div class="col-md-6 form-group{{ $errors->has('orderbook_url') ? ' has-error' : ''}}">
                            {!! Form::label('profit_margin', 'Profit Margin: ', ['class' => 'control-label']) !!}
                            {!! Form::text('profit_margin', null, ['class' => 'form-control', 'required' => 'required']) !!}
                            {!! $errors->first('profit_margin', '<p class="help-block">:message</p>') !!}
                        </div>

                        <div class="col-md-6 form-group{{ $errors->has('balance_filter') ? ' has-error' : ''}}">
                            {!! Form::label('balance_filter', 'Filter Balance:', ['class' => 'control-label']) !!}
                            {!! Form::select('balance_filter', ['1' => 'Enable', '0' => 'Disable', '-1' => 'Use global'], "1", ['class' => 'form-control custom-select']) !!}
                            {!! $errors->first('balance_filter', '<p class="help-block">:message</p>') !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Update', ['class' => 'px-5 btn btn-primary']) !!}
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
