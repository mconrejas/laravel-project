<select name="month" id="{{$id ?? uniqid()}}" class="custom-select {{$class ??''}}">
	<option disabled selected>{{__('Select Month') }}</option>
	@foreach(getMonths() as $key => $month)
		<option value="{{($key+1)}}">{{$month}}</option>
	@endforeach
</select>
