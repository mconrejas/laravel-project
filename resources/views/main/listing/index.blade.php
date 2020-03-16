@extends('masters.app')

@section('styles')
<style type="text/css">
	#listing-content {
		margin-top: -3rem;
		z-index: 5;
		position: relative;
	}
</style>
@endsection

@section('content')

@include('partials.sections.header-banner', [
	'header_text' => 'Submit your favorite coin to be added on exchange!'
])

<div class="card container mb-5 px-lg-5 py-5" id="listing-content">
	<div class="card-body">
		
		<div class="row">
			<div class="col-md-10 col-lg-8 col-12 mx-auto">
				@include('main.listing.components.form')
			</div>

			<div class="col-md-10 col-lg-8 col-12 mx-auto alert alert-info rounded-0">
				<ul class="my-0">
					<li>{{ __('Entries are subject for reviews') }}.</li>
					<li>{{ __('When approved it will be added to voting list') }}</li>
					<li>{{ __('We reserve the sole right to decide which projects will be accepted') }}</li>
				</ul>
			</div>
		</div>
		
	</div>
</div>
@endsection

@push('scripts')

@if ((int) parameter('recaptcha_enable', 1) == 1)
	{!! NoCaptcha::renderJs() !!}
@endif

<script type="text/javascript">
	var iconWidth = parseInt("{{parameter('exchangeitem.icon_width', 120)}}");
	var iconHeight = parseInt("{{parameter('exchangeitem.icon_height', 120)}}");
	var listingUrl  = '{{ route("listing.store") }}';

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
						url: listingUrl,
						type: "POST",
						cache: false,
						contentType: false,
						processData: false,
						data: formData
					}).done(function(data){
						// console.log(data);
						button.btnReset();
						toast({type:'success' , title : data.flash_message })
						.then(function(){
							window.location.href = "{{ route('listing.show',['id'=>'']) }}/"+data.id
						})
					}).fail(function (xhr, status, error) {
		              	alert({
		                  	title: window.Templates.getXHRMessage(xhr),
		                  	html: window.Templates.getXHRErrors(xhr),
		                  	type: 'error'
						  });
						  
						  window.grecaptcha.reset();
		              button.btnReset();
					});
				}
          })
				
		})
	})
</script>

@endpush