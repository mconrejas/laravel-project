@extends('masters.admin')
@section('content')
@php
    $filter = '';

    if($exchangeItem->alternative_deposit < 0) $filter = 'Default';
    if($exchangeItem->alternative_deposit == 0) $filter = 'Disabled';
    if($exchangeItem->alternative_deposit > 0) $filter = 'Enabled';
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Exchange Item {{ $exchangeItem->item_id }}</div>
                <div class="card-body">
                    <a  class="btn btn-warning btn-sm" href="{{ route('exchange-items.index') }}" title="Back">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Back
                    </a>
                    <a class="btn btn-primary btn-sm" href="{{ route('exchange-items.edit',['id'=>$exchangeItem->item_id]) }}" title="Edit Item">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                    </a>
                    <a class="btn btn-warning btn-sm" href="{{ route('exchangeitems.uploadForm',['id'=>$exchangeItem->item_id]) }}" title="Edit Item">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Upload Icon
                    </a>
                    <!--  {!! Form::open([
                    'method'=>'DELETE',
                    'url' => ['admin/exchange-items', $exchangeItem->item_id],
                    'style' => 'display:inline'
                    ]) !!}
                    {!! Form::button('<i class="fa fa-trash-o" aria-hidden="true"></i> Delete', array(
                    'type' => 'submit',
                    'class' => 'btn btn-danger btn-sm',
                    'title' => 'Delete Parameter',
                    'onclick'=>'return confirm("Confirm delete?")'
                    ))!!}
                    {!! Form::close() !!} -->
                    <br/>
                    <br/>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover ">
                            <tbody>
                                <tr><th>ID</th><td>{{ $exchangeItem->item_id }}</td></tr>
                                <tr><th>Icon</th><td><img src="{{ $exchangeItem->iconUrl }}" height="60"></td></tr>
                                <tr><th> Name </th><td> {{ $exchangeItem->name }} </td></tr>
                                <tr><th> Symbol </th><td> {{ $exchangeItem->symbol }} </td></tr>
                                <tr><th> Type </th><td> {{ $exchangeItem->type }} </td></tr>
                                <tr><th> Description </th><td> {{ $exchangeItem->description }} </td></tr>
                                <tr><th> Token Address </th><td> {{ $exchangeItem->token_address }}</td></tr>
                                <tr><th> Deposit Off </th><td> {{ $exchangeItem->deposit_off }}</td></tr>
                                <tr><th> Withdrawal Off </th><td> {{ $exchangeItem->withdrawal_off }}</td></tr>
                                <tr><th> Index Price USD </th><td>{{ $exchangeItem->index_price_usd}}</td></tr>
                                <tr><th> Index Price BTC </th><td>{{ $exchangeItem->index_price_btc }}</td></tr>
                                <tr><th> Alternative Deposit </th><td>{{ $filter }}</td></tr>
                                <tr><th> Exchange API </th><td>{{ $exchangeItem->getExchangeApi() != null ? ucfirst($exchangeItem->getExchangeApi()->name) : "N/A" }}</td></tr>
                                <tr><th> Created </th><td>{{ $exchangeItem->created }}</td></tr>
                                <tr><th> Deleted </th><td>{{ $exchangeItem->deleted }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
