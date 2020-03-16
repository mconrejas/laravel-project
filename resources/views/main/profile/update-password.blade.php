@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
	<div class="row justify-content-center">
	    <div class="col-md-3">
	        @include('main.profile.component.side-menu')
	    </div>
	    <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-change-pass">
                <div class="card-body">
                	<form class="form w-100 mh-100 px-md-5 px-2" method="POST" action="{{route('password.change')}}">
                    	
                    	{{ csrf_field() }}
                    	
                    	<div class="lead row mb-4 mt-3 px-2">
                    		{{ __('Change Password') }}
                    	</div>

                    	
						<div class="row">
							<div class="col-md-8 offset-md-3">
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
							<div class="col-md-3">
							    <span class="form-label border-0 bg-transparent">{{ __('Email confirmation code') }}</span>
							</div>
							<div class="form-group col-md-8 pass-show">
							  	<span data-source="{{route('email.requestEmailCode')}}" class="request-code text-buzzex">{{__('Get Code')}}</span>
							  	<input type="text" name="email_confirmation_code" class="form-control rounded-0 text-md-center" placeholder="{{ __('Email confirmation code') }}" >
							  	<span class="w-100 py-1 text-success email_confirmation_code_message"></span>
							</div>
                        </div>
                        @if ($errors->has('email_confirmation_code'))
						<div class="row">
							<div class="col-md-8 offset-md-3">
								<span class="help-block">
									<small class="text-danger">{{ $errors->first('email_confirmation_code') }}</small>
								</span>
							</div>
						</div>
						@endif

                        <div class="row my-1">
							<div class="col-md-3">
							    <span class="form-label border-0 bg-transparent">{{ __('Current password') }}</span>
							</div>
							<div class="form-group col-md-8 pass-show p {{ $errors->has('current_password') ? ' has-error' : '' }}">
							  	<span class="pass-txt text-buzzex fa fa-eye"></span>
							  	<input type="password" name="current_password" class="form-control rounded-0" placeholder="{{ __('Current password') }}" >
							</div>
                        </div>
						@if ($errors->has('current_password'))
						<div class="row">
							<div class="col-md-8 offset-md-3">
								<span class="help-block">
									<small class="text-danger">{{ $errors->first('current_password') }}</small>
								</span>
							</div>
						</div>
						@endif
							
                        <div class="row my-1">
							<div class="col-md-3">
							    <span class="form-label border-0 bg-transparent">{{ __('New password') }}</span>
							</div>
							<div class="form-group col-md-8 pass-show {{ $errors->has('new_password') ? ' has-error' : '' }}">
							  	<span class="pass-txt text-buzzex fa fa-eye"></span>
							  	<input id="new_password" type="password" name="new_password" class="form-control rounded-0" placeholder="{{ __('New password') }}" >
							</div>
                        </div>
						@if ($errors->has('new_password'))
						<div class="row">
							<div class="col-md-8 offset-md-3">
								<span class="help-block">
									<small class="text-danger">{{ $errors->first('new_password') }}</small>
								</span>
							</div>
						</div>
						@endif

                        <div class="row my-1">
							<div class="col-md-3">
							    <span class="form-label border-0 bg-transparent">{{ __('Confirm password') }}</span>
							</div>
							<div class="form-group col-md-8 pass-show">
							  	<span class="pass-txt text-buzzex fa fa-eye"></span>
							  	<input type="password" name="new_password_confirmation" class="form-control rounded-0" placeholder="{{ __('Confirm password') }}" >
								<span class="w-100 py-1 text-danger confirm_password_message"></span>
							</div>
                        </div>
							
                        <div class="row my-3">
                        	<div class="col-md-8 offset-md-3">
								<button type="submit" class="rounded-0 btn btn-disabled btn-buzzex btn-block">{{__('Save')}}</button>
							</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    #card-change-pass .input-group .form-label {
    	min-width: 200px;
    }
    #card-change-pass .pass-show{	
    	position: relative
    } 
    #card-change-pass .pass-show .request-code,
	#card-change-pass .pass-show .pass-txt { 
		position: absolute; 
		top: 20px; 
		right: 25px; 
		z-index: 1; 
		color: #e8e8e8; 
		margin-top: -10px; 
		font-size: 13px;
		cursor: pointer; 
		transition: .3s ease all; 
	} 
	#card-change-pass .pass-show .pass-txt:hover { 
		color: #333333 !important;	
	} 
	#card-change-pass  .confirm_password_message,
	#card-change-pass .pass-text {
		font-size: 12px;
	}
	#card-change-pass .captcha-wrapper {
		height: 0px;
		overflow: hidden;
	}
	@media (max-width: ) {
		#card-change-pass .pass-show .request-code,
		#card-change-pass .pass-show .pass-txt {
			/*right: 8%; */
		}
	}
</style>
@endsection

@section('scripts')
<script type="text/javascript">
(function ( $ ) {

  	$.fn.ChangePasswordWidget = function(params) {
    	var widget      = this;
    	var opt         = $.extend({
                        messageUrl : '',
                        markReadurl: ''
                    },params);

		widget.find('#new_password').password({
	        shortPass: 'The password is too short',
	        badPass: 'Weak; try combining letters & numbers',
	        goodPass: 'Medium; try using special characters',
	        strongPass: 'Strong password',
	        containsUsername: 'The password contains the username',
	        enterPass: 'Type your password',
	        showPercent: false,
	        showText: true, // shows the text tips
	        animate: true, // whether or not to animate the progress bar on input blur/focus
	        animateSpeed: 'fast', // the above animation speed
	        username: false, // select the username field (selector or jQuery instance) for better password checks
	        usernamePartialMatch: true, // whether to check for username partials
	        minimumLength: 6 // minimum password length (below this threshold, the score is 0)
	    }).on('password.score', (e, score) => {
	        var button = $(e.target).parents('form').find("button[type='submit']");
	        if (score < 50 ) {
	            button.addClass('btn-disabled');
	        } else {
	            button.removeClass('btn-disabled');
	        }
	    });
		
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
		
		widget.on('click','.pass-show .request-code', function() {
			var button = $(this); 
			var source = button.data('source');
			var messageBox = widget.find('.email_confirmation_code_message');
			button.btnProcessing('.');
			$.post(source,{})
			.done(function(e){
				if (e.status == 200) {
					messageBox.html("<span class='text-success'>"+e.message+"</span>")
					button.remove();
				} else {
					button.btnReset();
					messageBox.html("<span class='text-danger'>Something went wrong. Please try again later</span>");
				}
			}).fail(function(e){
				button.btnReset();
				messageBox.html("<span class='text-danger'>Something went wrong. Please try again later</span>");
			})
		});

		widget.find("#card-change-pass").on('submit','form:not(.processing)', function(e) {
			var form = $(this);
			var button =  form.find("button[type='submit']");
			    button.btnProcessing('Submitting...');
			if (form.find("[name='new_password']").val() != form.find("[name='confirm_password']").val()) {
				form.find(".confirm_password_message").text("New password and confirm password does not match!");
				button.btnReset();
				e.preventDefault();
			}
		})
		return widget;  
	}

}( jQuery ));

$(document).ready(function(){
	$("#card-change-pass").ChangePasswordWidget({
		
	})
});
  
</script>
@endsection
