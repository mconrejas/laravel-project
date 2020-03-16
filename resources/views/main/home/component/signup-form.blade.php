<section id="signup-form">
	<div class="container py-5 ">
        <h1 class="my-3 text-light text-center">{{__('Join Today And Enter The $13 Million Trading Contest!')}}</h1>
        <div class="col-md-8 mx-auto py-3">
			<form class="form register-via-email-form" action="{{ route('register.via.email') }}" method="post">
	            @csrf
	            <div class="row" >
	                <input type="email" name="email" class="col-md-9 col-12 form-control" placeholder="Enter your email address">
	                <button type="submit" class="col-md-3 col-12 btn btn-buzzex">
	                    <span class="text-light">{{ __('Sign Up Now') }}</span>
	                </button>
	            </div>
	        </form>
        </div>
    </div>
</section>
