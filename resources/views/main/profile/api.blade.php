@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0" id="card-api">
            	<div class="card-body mt-3 d-flex justify-content-between">
            		<h5 class="lead align-self-center">{{ __('API Settings') }}</h5>
            		<button class="btn btn-buzzex rounded-0 btn-create-new-api">
            			<span class="fa fa-plus"></span> {{ __('Create New')}}
            		</button>
            	</div>
                <div class="card-body card-each-wrapper">
            		@forelse($existing_apis as $key => $api)
            			@include('main.profile.component.api-setting-card', $api)
            		@empty
                        <div class="alert alert-dark rounded-0 py-5" role="alert">No API key-pair yet. Create one now.</div>
                    @endforelse
                </div>
                <div class="card-body">
                	<p>Note:</p>
                	<ul>
						<li>{{ __('Secret key pair is to account/password pair. Please keep it safely.') }} </li>
						<li>{{ __('Each user can create 5 API secret key pairs at MOST.') }} </li>
                	</ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
/**
 * Api setting widget
 */
(function($) {

    $.fn.ApiSettings = function(params) {
    	var widget = this;
        var opt = $.extend({
            is2FAEnabled : 0
        }, params);
        
        widget.checkIfEmpty = function() {
        	if (widget.find('.card-api-each').length == 0) {
        		widget.find('.card-each-wrapper').html('<div class="alert alert-dark" role="alert">No API key yet. Create one now.</div>');
        	} else {
        		widget.find('.card-each-wrapper .alert').remove();
        	}
        };

        widget.on('click', '.btn-delete-api', function(e){
        	var button = $(this);
        	var api_id = button.data('id');
        	button.btnProcessing('.');

        	if (opt.is2FAEnabled == 0) {
        		requestEmailCode(); //fallback incase 2fa is disabled after creating api
        	}

        	confirmTemplate({
		        text: opt.is2FAEnabled == 1 ? 'Enter 2FA code to confirm.' : 'Check your email for confirmation code.',
		        input: 'text',
		        inputPlaceholder: opt.is2FAEnabled == 1 ? 'Enter 2FA code.' : 'Enter email code.',
		        inputClass: 'text-center rounded-0 form-control w-75 ',
		        confirmButtonColor: 'linear-gradient(270deg, #22e6b8, #00c1ce)',
		        confirmButtonText: 'Confirm',
		        cancelButtonText: 'Cancel',
		        inputValidator: function(value) {
		            return !value && 'Code cannot be empty'
		        }
		    }).then((result) => {
		        if (result.value) {
		            $.post(window.location.origin + '/en/api/delete', {
		        		client_id : api_id,
		        		code : result.value,
		        		via : opt.is2FAEnabled == 1 ? '2fa' : 'email'
		        	}).done(function(response){
		        		notifications({message : response.message });

	        			$('#'+api_id).slideUp('fast', function(){
	        				$('#'+api_id).remove();
	        				widget.checkIfEmpty();
	        			});
		        		
		        		button.btnReset();
					}).fail(function(xhr, status, error) {
		                alert({
		                    title: window.Templates.getXHRMessage(xhr),
		                    html: window.Templates.getXHRErrors(xhr),
		                    type: 'error'
		                });
		                button.btnReset();
		            })
		        }
                if (typeof result.dismiss !== 'undefined') {
                    button.btnReset();
                }
		    })
        });

        widget.on('click','.btn-create-new-api', function(e) {
        	var button = $(this);
        	button.btnProcessing('Checking...');

        	if (opt.is2FAEnabled == 0) {
                alert({text : 'Please enable 2FA.', type: 'error' });
                button.btnReset();
                return;
            }

    		$.get(window.location.origin + '/en/api/counts')
    		.done(function(response){
    			if (response.counts >= 5) {
    				alert({text : 'Limit exceed! Cannot create more API key.', type: 'error' });
    			} else {
                    confirmTemplate({
                        text: 'Enter 2FA code to confirm.' ,
                        input: 'text',
                        inputPlaceholder: 'Enter 2FA code.',
                        inputClass: 'text-center rounded-0 form-control w-75 ',
                        confirmButtonColor: 'linear-gradient(270deg, #22e6b8, #00c1ce)',
                        confirmButtonText: 'Confirm',
                        cancelButtonText: 'Cancel',
                        inputValidator: function(value) {
                            return !value && 'Code cannot be empty'
                        }
                    }).then((result) => {
                        if (result.value) {
                            $.post(window.location.origin + '/en/api/create',{
                                code : result.value
                            })
                            .done(function(response){
                                alert({
                                    title : response.message,
                                    confirmButtonText : 'Done', 
                                    type: 'success',
                                    html : '<div>'
                                        +'<h5 class="text-danger">Important! Copy below details as these are only shown once.</h5>'
                                        +'<textarea readonly class="form-control" rows="3">API Key : '+response.key+'\n'
                                        +'API Secret : '+response.secret+'</textarea>'
                                    +'</div>' 
                                })
                                .then(function(arg) {
                                    window.location.reload()
                                })
                            })
                        }
                        if (typeof result.dismiss !== 'undefined') {
                            button.btnReset();
                        }
                    })
    			}
    			widget.checkIfEmpty();
    			button.btnReset();
    		})
    		.fail(function(xhr, status, error) {
                alert({
                    title: window.Templates.getXHRMessage(xhr),
                    html: window.Templates.getXHRErrors(xhr),
                    type: 'error'
                });
                widget.checkIfEmpty();
                button.btnReset();
            })
        	
        })
        return widget;
    }

}(jQuery));

$(document).ready(function() {
	$('#card-api').ApiSettings({
		is2FAEnabled : parseInt('{{auth()->user()->is2FAEnable() ? 1 : 0 }}')
	});
});
</script>
@endsection