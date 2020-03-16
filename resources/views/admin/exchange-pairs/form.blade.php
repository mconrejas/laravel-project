
<div class="form-group{{ $errors->has('item1') ? ' has-error' : ''}}">
    {!! Form::label('item1', 'Item 1: ', ['class' => 'control-label']) !!}
    {!! Form::select('item1', $items, isset($items) ? $items : [], ['class' => 'form-control custom-select', 'multiple' => false]) !!}
    {!! $errors->first('item1', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('item2') ? ' has-error' : ''}}">
    {!! Form::label('item2', 'Item 2: ', ['class' => 'control-label']) !!}
    {!! Form::select('item2', $items, isset($items) ? $items : [], ['class' => 'form-control custom-select', 'multiple' => false]) !!}
    {!! $errors->first('item2', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('fee_percentage') ? ' has-error' : ''}}">
    {!! Form::label('fee_percentage', 'Exchange Fee (-1 = default fee from global settings) : ', ['class' => 'control-label']) !!}
    {!! Form::text('fee_percentage', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('fee_percentage', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('minimum_trade_total') ? ' has-error' : ''}}">
    {!! Form::label('minimum_trade_total', 'Minimum Trade Total: ', ['class' => 'control-label']) !!}
    {!! Form::text('minimum_trade_total', null, ['class' => 'form-control numeric', 'required' => 'required']) !!}
    {!! $errors->first('minimum_trade_total', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('tolerance_level') ? ' has-error' : ''}}">
    {!! Form::label('tolerance_level', 'Tolerance Level (-1 = default fee from global settings): ', ['class' => 'control-label']) !!}
    {!! Form::text('tolerance_level', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('tolerance_level', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('maxPrice') ? ' has-error' : ''}}">
    {!! Form::label('maxPrice', 'Max Price: ', ['class' => 'control-label']) !!}
    {!! Form::text('maxPrice', $filters['PRICE_FILTER']['maxPrice'], ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('maxPrice', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('minPrice') ? ' has-error' : ''}}">
    {!! Form::label('minPrice', 'Min Price: ', ['class' => 'control-label']) !!}
    {!! Form::text('minPrice', $filters['PRICE_FILTER']['minPrice'], ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('minPrice', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('maxQty') ? ' has-error' : ''}}">
    {!! Form::label('maxQty', 'Max Amount: ', ['class' => 'control-label']) !!}
    {!! Form::text('maxQty', $filters['LOT_SIZE']['maxQty'], ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('maxQty', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('minQty') ? ' has-error' : ''}}">
    {!! Form::label('minQty', 'Min Amount: ', ['class' => 'control-label']) !!}
    {!! Form::text('minQty', $filters['LOT_SIZE']['minQty'], ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('minQty', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('minNotional') ? ' has-error' : ''}}">
    {!! Form::label('minNotional', 'Min Cost: ', ['class' => 'control-label']) !!}
    {!! Form::text('minNotional', $filters['MIN_NOTIONAL']['minNotional'], ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('minNotional', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

@section('scripts')
	@if($formMode === 'edit')
<script type="text/javascript">
	var item1 = parseInt("{{$exchangePair->item1}}");
	var item2 = parseInt("{{$exchangePair->item2}}");
	$(document).ready(function(){
		$("select[name='item1']").attr('disabled','disabled').val(item1);
		$("select[name='item2']").attr('disabled','disabled').val(item2);
	})
</script>
	@endif
@endsection
