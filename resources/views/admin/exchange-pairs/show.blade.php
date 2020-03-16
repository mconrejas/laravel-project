@extends('masters.admin')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Exchange Pair {{ $exchangePair->item_id }}</div>
                <div class="card-body">
                    <a  class="btn btn-warning btn-sm" href="{{ route('exchange-pairs.index') }}" title="Back">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Back
                    </a>
                    <a class="btn btn-primary btn-sm" href="{{ route('exchange-pairs.edit',['id'=>$exchangePair->pair_id]) }}" title="Edit Pair">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                    </a>
                    <!--  {!! Form::open([
                    'method'=>'DELETE',
                    'url' => ['admin/exchange-items', $exchangePair->item_id],
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
                                <tr> <th>Pair ID</th><td>{{ $exchangePair->pair_id }}</td></tr>
                                <tr><th> Item 1 </th><td> {{ $exchangePair->exchangeItemOne->name }} </td></tr>
                                <tr><th> Item 2 </th><td> {{ $exchangePair->exchangeItemTwo->name }} </td></tr>
                                <tr><th> Symbol </th><td> {{ $exchangePair->exchangeItemOne->symbol }}/{{ $exchangePair->exchangeItemTwo->symbol }} </td></tr>
                                <tr><th> Fee Percentage </th><td> {{ $exchangePair->fee_percentage }} </td></tr>
                                <tr><th> Dynamic Pricing </th><td> {{ $exchangePair->dynamic_pricing }}</td></tr>
                                <tr><th> Minimum Trade Total </th><td> {{ $exchangePair->minimum_trade_total }}</td></tr>
                                <tr><th> Tolerance Level </th><td> {{ $exchangePair->tolerance_level < 0 ? 'Default' : $exchangePair->tolerance_level }}</td></tr>
                                <tr><th> Created </th><td> {{ $exchangePair->created }}</td></tr>
                                <tr><th> Deleted </th><td>{{ $exchangePair->deleted }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
