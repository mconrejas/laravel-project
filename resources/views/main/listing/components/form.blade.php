
<style type="text/css">
.croppie-container { overflow: hidden; }
img { max-width: 100%;  }
</style>

{!! Form::open(['url' => '', 'files' => true]) !!}

<div class="w-100 {{ $errors->has('logo') ? 'has-error' : ''}}">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Coin/Token Logo') }} : </label>
	<small class="d-flex justify-content-end">{{ __('Upload and crop logo from image') }}.</small>
	<div class="card border-0">
		<div class="card-block py-2 text-center">
			<div class="input-group mb-3">
				<div class="custom-file">
					<input type="file" class="custom-file-input" id="iconfileupload" accept="image/*">
					<label class="btn custom-file-label" onclick="$('#iconfileupload').click()" for="iconfileupload">{{ __('Choose image') }}</label>
				</div>
			</div>
		</div>
		<div class="card-block">
			<img id="image" src="{{asset('img/logo.png')}}">
		</div>
	</div>
</div>

<div class="form-group {{ $errors->has('symbol') ? 'has-error' : ''}}">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Symbol/Ticker') }} : </label>
	{!! Form::text('symbol', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'e.g. BTC']) !!}
	{!! $errors->first('symbol', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Coin/Token Name') }} : </label>
	{!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'e.g. Bitcoin'] ) !!}
	{!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group py-3">
	<label class="control-label d-block">{{ __('Coin/Token Type') }} : </label>
	<div class="custom-control custom-radio custom-control-inline">
		<input type="radio" checked id="cointype1" value="Public Chain" name="coin_type" class="custom-control-input">
		<label class="custom-control-label" for="cointype1">{{ __('Public Chain') }}</label>
	</div>
	<div class="custom-control custom-radio custom-control-inline">
		<input type="radio" id="cointype2" value="Non Public Chain" name="coin_type" class="custom-control-input">
		<label class="custom-control-label" for="cointype2">{{ __('Non Public Chain') }}</label>
	</div>
</div>

<div class="form-group">
	<label class="control-label d-block">{{ __('Date of Issue') }} : </label>
	{!! Form::date('date_of_issue', null, ['class' => 'form-control']) !!}
	{!! $errors->first('date_of_issue', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Total Supply') }} : </label>
	{!! Form::number('total_supply', null,  ['class' => 'form-control', 'value' => 0, 'required' => 'required']) !!}
	{!! $errors->first('total_supply', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Official Website URL') }} : </label>
	{!! Form::text('official_website', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'https://']) !!}
	{!! $errors->first('official_website', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Project Description') }} : </label>
	{!! Form::textarea('project_description', null, ['class' => 'form-control', 'required' => 'required', 'rows'=> 2]) !!}
	<small class="d-flex justify-content-end">{{ __('At least 100 characters') }}</small>
	{!! $errors->first('project_description', '<p class="help-block">:message</p>') !!}
</div>


<div class="form-group">
	<label class="control-label d-block">{{ __('Whitepaper URL') }} : </label>
	{!! Form::text('whitepaper', null,  ['class' => 'form-control', 'placeholder' => 'https://']) !!}
	{!! $errors->first('whitepaper', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Source Code URL') }} : </label>
	{!! Form::text('source_code', null,['class' => 'form-control', 'required' => 'required', 'placeholder' => 'https://']) !!}
	{!! $errors->first('source_code', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Blockchain Explorer URLs') }} : </label>
	{!! Form::textarea('blockchain_explorer', null, ['class' => 'form-control', 'required' => 'required', 'rows'=> 2, 'placeholder'=>'e.g https://coin.com/source1
	https://coin.com/source2']) !!}
	<small class="d-flex justify-content-end">{{ __('1 URL per line') }}</small>
	{!! $errors->first('blockchain_explorer', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group d-flex flex-wrap flex-md-nowrap align-item-md-center">
	@if ((int) parameter('recaptcha_enable', 1) == 1)
	{!! NoCaptcha::display(['data-theme' => $user_theme ]) !!}
	@endif
	<span class="px-3">
		<ul>
			<li><sup class="text-danger">*</sup> {{ __('are required fields')}}</li>
		</ul>
	</span>
</div>

<div class="form-group py-5 d-flex justify-content-center">
	<button class="btn btn-lg px-5 btn-buzzex btn-submit-listing" type="button"> {{ __("Submit") }}</button>
</div>

{!! Form::close() !!}


