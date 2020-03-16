<select name="pair" id="{{$id ?? uniqid()}}" class="custom-select {{$class ??''}}">
	@if($includeAll ?? false)
	<option value="all">{{__('All Pairs') }}</option>
	@endif
	@foreach(getPairs() as $id => $name)
		<option value="{{$id}}">{{$name}}</small></option>
	@endforeach
</select>
