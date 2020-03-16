<select name="{{ $name ?? 'ticker' }}" id="{{$id ?? uniqid()}}" class="custom-select {{$class ??''}}">
	@if($includeAll ?? false)
	<option value="all">{{__('All Coins') }}</option>
	@endif
	@foreach(getCoins() as $symbol => $name)
		@if(isset($selected))
			@if($selected == strtoupper($symbol))
				<option value="{{strtoupper($symbol)}}" selected >{{strtoupper($symbol)}} <small>({{$name}})</small></option>
			@else
				<option value="{{strtoupper($symbol)}}">{{strtoupper($symbol)}} <small>({{$name}})</small></option>
			@endif
		@else 
		<option value="{{strtoupper($symbol)}}">{{strtoupper($symbol)}} <small>({{$name}})</small></option>
		@endif
	@endforeach
</select>
