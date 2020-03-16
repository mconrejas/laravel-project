
<style type="text/css">
.croppie-container { overflow: hidden; }
img { max-width: 100%;  }
</style>

<form class="form" method="POST">

<div class="w-100 {{ $errors->has('logo') ? 'has-error' : ''}}">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Coin Logo') }} : </label>
	<small class="d-flex justify-content-end">{{ __('Upload and crop logo from image') }}.</small>
	<div class="card border-0 bg-transparent">
		<div class="card-block py-2 text-center">
			<div class="input-group mb-3">
				<div class="custom-file">
					<input type="file" class="custom-file-input" id="iconfileupload" accept="image/*">
					<label class="btn custom-file-label" onclick="$('#iconfileupload').click()" for="iconfileupload">{{ __('Choose image') }}</label>
				</div>
			</div>
		</div>
		<div class="card-block">
			<img id="image" src="{{$project->iconUrl ?: asset('img/logo.png')}}">
		</div>
	</div>
</div>

<div class="form-group {{ $errors->has('symbol') ? 'has-error' : ''}}">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Symbol/Ticker') }} : </label>
	{!! Form::text('symbol', $project->symbol, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'e.g. BTC']) !!}
	{!! $errors->first('symbol', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Coin Name') }} : </label>
	{!! Form::text('name', $project->name, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'e.g. Bitcoin'] ) !!}
	{!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group py-3">
	<label class="control-label d-block">{{ __('Coin Type') }} : </label>
	<div class="custom-control custom-radio custom-control-inline">
		<input type="radio" checked id="cointype1" value="{{ __('Public Chain') }}" name="coin_type" class="custom-control-input">
		<label class="custom-control-label" for="cointype1">{{ __('Public Chain') }}</label>
	</div>
	<div class="custom-control custom-radio custom-control-inline">
		<input type="radio" id="cointype2" value="{{ __('Non Public Chain') }}" name="coin_type" class="custom-control-input">
		<label class="custom-control-label" for="cointype2">{{ __('Non Public Chain') }}</label>
	</div>
</div>

<div class="form-group">
	<label class="control-label d-block">{{ __('Date of Issue') }} : </label>
	{!! Form::date('date_of_issue', $project->attribute('date_of_issue') , [ 'class' => 'form-control']) !!}
	{!! $errors->first('date_of_issue', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Total Supply') }} : </label>
	{!! Form::text('total_supply', $project->attribute('total_supply'),  ['class' => 'form-control', 'required' => 'required']) !!}
	{!! $errors->first('total_supply', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Official Website') }} : </label>
	{!! Form::text('official_website',$project->attribute('official_website'), ['class' => 'form-control', 'required' => 'required']) !!}
	{!! $errors->first('official_website', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Project Description') }} : </label>
	{!! Form::textarea('project_description', $project->attribute('project_description'), ['class' => 'form-control', 'required' => 'required', 'rows'=> 2]) !!}
	<small class="d-flex justify-content-end">{{ __('At least 100 characters') }}</small>
	{!! $errors->first('project_description', '<p class="help-block">:message</p>') !!}
</div>


<div class="form-group">
	<label class="control-label d-block">{{ __('Whitepaper') }} : </label>
	{!! Form::text('whitepaper',$project->attribute('whitepaper'),  ['class' => 'form-control']) !!}
	{!! $errors->first('whitepaper', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Source Code') }} : </label>
	{!! Form::text('source_code', $project->attribute('source_code'), ['class' => 'form-control', 'required' => 'required']) !!}
	{!! $errors->first('source_code', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
	<label class="control-label d-block"><sup class="text-danger">*</sup>{{ __('Blockchain Explorer') }} : </label>
	{!! Form::textarea('blockchain_explorer', $project->attribute('blockchain_explorer'), ['class' => 'form-control', 'required' => 'required', 'rows'=> 2]) !!}
	<small class="d-flex justify-content-end">{{ __('1 link per line') }}</small>
	{!! $errors->first('blockchain_explorer', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group d-flex align-item-center">
	<span class="px-3">
		<ul>
			<li><sup class="text-danger">*</sup> {{ __('are required fields')}}</li>
			<li>{{ __('Entries are subject for reviews') }}.</li>
			<li>{{ __('When approved it will be added to voting list') }}</li>
		</ul>
	</span>
</div>

<div class="form-group py-5 d-flex justify-content-center">
	<button class="btn btn-lg px-5 btn-primary btn-submit-listing" type="button"> {{ __("Update") }}</button>
</div>

</form>


@section('scripts')

<script type="text/javascript">
    var iconWidth = parseInt("{{parameter('exchangeitem.icon_width', 120)}}");
    var iconHeight = parseInt("{{parameter('exchangeitem.icon_height', 120)}}");
    var listingUpdateUrl  = '{{ route("project.update",["id" => $project->id]) }}';

    $(document).ready(function(){
        var $uploadCrop;

        function readFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $uploadCrop.croppie('bind', { url: e.target.result })
                    .then(function(){
                        console.log('jQuery bind complete');
                    });
                }
                reader.readAsDataURL(input.files[0]);
            }
            else {
                alert({ text: "Sorry - you're browser doesn't support the FileReader API" });
            }
        }

        $uploadCrop = $('#image').croppie({
            enableExif: true,
            viewport: {
                width: iconWidth,
                height: iconHeight,
                type: 'square'
            },
            boundary: {
                width:  $("#image").parents('.card-block').width(),
                height: 180,//$("#image").parents('.card-block').height()
            }
        });

        $('#iconfileupload').on('change', function () {
            readFile(this);
        });

        $(document).on("click",".btn-submit-listing", function(){
            var button = $(this);
            var form = button.parents('form')[0];
            var formData = new FormData(form);

            button.btnProcessing('Submitting ...');

            $uploadCrop.croppie('result', {
                type: 'blob',
                size: 'viewport'
            }).then(function (response) {
                if (response) {
                    formData.append('logo', response );

                    $.ajax({
                        url: listingUpdateUrl,
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData
                    }).done(function(data){
                        console.log(data);
                        button.btnReset();
                        toast({type:'success' , title : data.flash_message })
                        .then(function(){
                            window.location.href = "{{ route('project.show',['id'=>'']) }}/"+data.id
                        })
                    }).fail(function (xhr, status, error) {
                      alert({
                          title: window.Templates.getXHRMessage(xhr),
                          html: window.Templates.getXHRErrors(xhr),
                          type: 'error'
                      });
                      button.btnReset();
                    });
                }
          })
                
        })
    })
</script>
@endsection