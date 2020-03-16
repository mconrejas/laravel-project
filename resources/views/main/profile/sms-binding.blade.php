@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
	<div class="row justify-content-center">
	    <div class="col-md-3">
	        @include('main.profile.component.side-menu')
	    </div>
	    <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-sms-binding">
                <div class="card-body">
                	<form class="form w-100 mh-100 px-md-5" method="POST" action="{{route('sms.bind')}}">
                    	
                    	{{ csrf_field() }}
                    	
                    	<div class="lead row mb-4 mt-3 px-2 text-center text-md-left">
                    		{{ __('Bind Mobile Number') }}
                    	</div>
                    	
						<div class="row">
							<div class="col-12 col-md-8 offset-md-3">
								@if (session('error'))
								<div class="my-1 alert alert-danger rounded-0">
									{{ session('error') }}
								</div>
								@endif

								@if (session('success'))
								<div class="my-1 alert alert-success rounded-0">
									{{ session('success') }}
								</div>
								@endif
							</div>
						</div>

						<div class="row mt-1">
							<div class="col-12 col-md-3">
							    <span class="form-label border-0 bg-transparent">{{ __('Enter Mobile Number') }}</span>
							</div>
							<div class="col-12 col-md-3 px-md-0">
								@smsprefix(['class'=>'border rounded-0 w-100 my-2 my-md-0'])
		                    	@endsmsprefix
							</div>
								
							<div class="col-12 col-md-4 px-md-0">
							  	<input type="text" name="number" class="my-2 my-md-0 form-control rounded-0 border" value="" required placeholder="{{ __('Enter Mobile Number') }}" >
							</div>
							
							<div class="col-12 col-md-2 px-md-0">
							  	<button class="btn-block btn btn-primary border rounded-0 btn-verify-mobile-number" type="button">
							  		<span class="fa fa-shield"></span> {{__('Verify')}}
							  	</button>
								
							</div>
								
							<div class="col-12 col-md-9 offset-md-3">
								<span class="d-block message my-2"></span>
							</div>
                        </div>
                        @if ($errors->has('number'))
						<div class="row">
							<div class="col-md-8 offset-md-3">
								<span class="help-block">
									<small class="text-danger">{{ $errors->first('number') }}</small>
								</span>
							</div>
						</div>
						@endif
	
						<section class="collapse">
	                        <div class="row my-1">
								<div class="col-md-3">
								    <span class="form-label border-0 bg-transparent">{{ __('One Time Password') }}</span>
								</div>
								<div class="form-group col-md-9 pass-show p-0 {{ $errors->has('otp') ? ' has-error' : '' }}">
								  	<span class="pass-txt text-buzzex fa fa-eye"></span>
								  	<input type="password" required name="otp" class="form-control rounded-0" placeholder="{{ __('OTP') }}" >
								</div>
	                        </div>
							@if ($errors->has('otp'))
							<div class="row">
								<div class="col-md-8 offset-md-3">
									<span class="help-block">
										<small class="text-danger">{{ $errors->first('otp') }}</small>
									</span>
								</div>
							</div>
							@endif
								
	                        <div class="row my-3">
	                        	<div class="col-md-3 mx-auto">
									<button type="submit" class="rounded-0 btn btn-disabled btn-buzzex btn-block">{{__('Bind')}}</button>
								</div>
	                        </div>
						</section>
                    </form>
                </div>

                <div class="card-block my-3 px-md-4">
					<div class="col-md-12 mx-auto">
						<div class="alert alert-info text-secondary rounded-0">
						<span class="d-block">Notes:</span>
						<p>In case you had lost your phone number, contact us at <a class="btn-link" href="{{ $buzzexLinks->help_desk->url }}">Buzzex Help Desk</a> for assistance.</p>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    #card-sms-binding .input-group .form-label {
    	min-width: 200px;
    }
    #card-sms-binding .pass-show{	
    	position: relative
    } 
    #card-sms-binding .pass-show .request-code,
	#card-sms-binding .pass-show .pass-txt { 
		position: absolute; 
		top: 20px; 
		right: 5%; 
		z-index: 1; 
		color: #e8e8e8; 
		margin-top: -10px; 
		font-size: 13px;
		cursor: pointer; 
		transition: .3s ease all; 
	} 
	#card-sms-binding .pass-show .pass-txt:hover { 
		color: #333333 !important;	
	} 
	#card-sms-binding  .confirm_password_message,
	#card-sms-binding .pass-text {
		font-size: 12px;
	}
	#card-sms-binding .captcha-wrapper {
		height: 0px;
		overflow: hidden;
	}
</style>
@endsection

@section('scripts')
<script type="text/javascript">
(function ( $ ) {

  	$.fn.BindSmsWidget = function(params) {
    	var widget  = this;
    	var opt     = $.extend({
    					verifyUrl : ''
                    },params);
    	var form = widget.find('form');

    	widget.on('click','.pass-show .pass-txt', function() { 
			if($(this).hasClass('fa-eye') ){
				$(this).removeClass('fa-eye').addClass('fa-eye-slash');
			} else {
				$(this).removeClass('fa-eye-slash').addClass('fa-eye');
			}
			$(this).parents('.pass-show').find('input').attr('type', function(index, attr){
				return attr == 'password' ? 'text' : 'password'; 
			}); 
		});

		widget.on('click', '.btn-verify-mobile-number', function(e){
			var button = $(this);
				button.btnProcessing('.');
			var message = button.parents('#card-sms-binding').find('.message');

			$.post(opt.verifyUrl,{
				countryCode : form.find("[name='countryCode']").val(),
				number : form.find("[name='number']").val()
			}).done(function(data){
				widget.find('.collapse').collapse('show')
				button.btnReset();
				message.text(data.message);
			}).fail(function(xhr, status, error){
				alert({
					type: 'error',
					text: xhr.responseJSON.message 
				});
				button.btnReset();
			})
		})

		return widget;  
	}

}( jQuery ));

$(document).ready(function(){
	$('#card-sms-binding').BindSmsWidget({
		verifyUrl: "{{route('sms.requestOTP')}}"
	});
	
});
  
</script>
@endsection
