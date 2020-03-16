@if($formMode === 'create')
<style type="text/css">
    .select2-container--default .select2-selection--single {
        border-radius: 0px;
    }
</style>
<div class="form-group">
    <label>Select from submitted list.</label>
    <select class="form-control rounded-0 select2" id="select-from-list"></select>
</div>
@endif
<div class="form-group">
    {!! Form::label('transaction_id', 'Transaction ID: ', ['class' => 'control-label font-weight-bold']) !!} {{$item->transaction_id}}
</div>
<div class="form-group">
    {!! Form::label('coin', 'Item: ', ['class' => 'control-label font-weight-bold']) !!} {{$item->exchangeItem->symbol}}
</div>
<div class="form-group">
    {!! Form::label('amount', 'Amount: ', ['class' => 'control-label font-weight-bold']) !!} {{  currency(abs($item->amount)) }}
</div>
<div class="form-group">
    {!! Form::label('amount', 'Withdrawal Fee: ', ['class' => 'control-label font-weight-bold']) !!} {{currency($item->fee) }}
</div>
<div class="form-group">
    {!! Form::label('address', 'Withdrawal Address: ', ['class' => 'text-danger control-label font-weight-bold']) !!} {{$item->remarks}}
</div>
<div class="form-group">
    {!! Form::label('net amount', 'Net Amount: ', ['class' => 'text-danger control-label font-weight-bold']) !!} {{ currency(abs($item->amount + $item->fee)) }}
</div>
@if(array_key_exists($item->exchangeItem->symbol,$coins_with_tag))
<div class="form-group">
    {!! Form::label('tag', 'Tag: ', ['class' => 'control-label font-weight-bold']) !!} {{ $item->tag }}
</div>
@endif
<div class="form-group">
    {!! Form::label('remarks', 'Remarks: ', ['class' => 'control-label font-weight-bold']) !!} {{$item->remarks2}}
</div>
<div class="form-group">
    {!! Form::label('transaction_date', 'Transaction Date: ', ['class' => 'control-label font-weight-bold']) !!} {{ date('Y-m-d H:i', $item->created) }}
</div>
<div class="form-group">
    {!! Form::label('amount', 'User: ', ['class' => 'control-label font-weight-bold']) !!} 
    <a href="{{ route('users.show',['user' => $item->User->id]) }}">{{$item->User->email}} ({{$item->User->id}})</a>
</div>
<div class='row'>
    <div class="form-group{{ $errors->has('type') ? ' has-error' : ''}} col-md-4 col-12">
        {!! Form::label('status', 'Status: ', ['class' => 'control-label font-weight-bold']) !!}
        <select class="form-control mr-1 custom-select" id="item_type" name="status">
            @if(exchangeTxnStatuses())
                @foreach(exchangeTxnStatuses() as $index => $status_)
                    <option value="{{$index}}" @if(( ($status==$index) && $status != '' )) selected @endif>{{$status_}}</option>
                @endforeach
            @endif
        </select>
    </div>
</div>
<div class="form-group">
    {!! Form::label('notes', 'Updates Notes: ', ['class' => 'control-label font-weight-bold']) !!} 
    <textarea required class="form-control" name="notes" placeholder="Required."></textarea>
</div>


<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'px-5 btn btn-primary']) !!}
</div>