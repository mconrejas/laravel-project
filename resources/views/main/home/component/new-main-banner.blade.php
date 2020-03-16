<section id="new-main-banner" class="pt-5 pb-3">
        <div class="row justify-content-center align-self-center">
            <div class="col-8 mb-5 mt-3">
                <h1 class="font-bold text-light text-center">{{ __('Welcome To The Buzzex Exchange') }}</h1>
            </div>
            <div class="col-7 mb-5">
                <form class="form register-via-email-form" data-action="{{ route('register.via.email') }}" method="post">
                    @csrf
                    <div class="row" >
                        <input type="email" name="email" class="col-12 col-md-9 form-control" placeholder="Enter your email address">
                        <button type="submit" class="col-12 col-md-3 btn btn-buzzex">
                            <span class="text-light">{{ __('Sign Up Now') }}</span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-12">
                <h3 class="text-center text-warning" style="text-shadow: 1px 1px black;">Join Today And Enter The $13 Million Trading Contest!</h3>
            </div>
        </div>
       
</section>

@push('scripts')
<script type="text/javascript">
    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    $(document).ready(function(){
        $('body').on('submit', '.register-via-email-form:not(.processing)', function(e){
            e.preventDefault();
            var form = $(this);
            var email = form.find('input[name="email"]').val();
            if (!validateEmail(email)) {
                alert('Invalid email format. Please check again');
                return;
            }
            form.addClass('processing');
            window.confirmation(
                'By clicking <b>Yes</b>, you agree to the <a href="">Terms of Service</a> set out by this site', 
            function(){
                $.post(form.attr('data-action'), form.serialize())
                .done(function(response){
                    alert(response.message);                })
                .fail(function (xhr, status, error) {
                    alert({
                        title: window.Templates.getXHRMessage(xhr),
                        html: window.Templates.getXHRErrors(xhr),
                        type: 'error'
                    });
                })
                form.removeClass('processing');
            }, function(){
                form.removeClass('processing');
            });
        });
    })
</script>
@endpush