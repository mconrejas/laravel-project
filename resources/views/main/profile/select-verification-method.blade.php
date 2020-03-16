@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-wallet">
                <div class="card-body">
                    <div class="w-100 d-flex justify-content-start">
                        <div class="align-self-center">
                            <h5>{{__('ID Verification') }}</h5>
                            <small class="d-block"> {{__('Buzzex ID verification includes Personal verification and Business verification. Each account is only available for ONE type of verification. Once approved, your verification type CANNOT be changed.')}}</small>

                            <div class="row d-flex justify-content-start pt-5">
                                <div class="col-12 col-md-4 mb-3"> 
                                    <div class="card mb-4 box-shadow">
                                        <a href="@if(!$personal_verification || $personal_verification->approved == 2) {{route('my.verifyPersonal')}} @else javascript:; @endif">
                                        <div class="card-verification-holder">
                                            <p>
                                               <span class="fa fa-lg fa-user mr-1"></span> Personal
                                               @if($personal_verification) 
                                                   @if($personal_verification->approved == 1) 
                                                    <br />
                                                    <small>
                                                        <span class="align-self-center mx-2 font-10 text-success">
                                                            <i class="fa fa-check"></i> {{__('Verified') }}
                                                        </span>
                                                    </small>
                                                   @else 

                                                    <br />
                                                        @if($personal_verification->approved == 2)
                                                        <small>
                                                            <span class="align-self-center mx-2 text-danger">{{__('Rejected') }}</span> <br />
                                                            <span style="font-size: 12px;">({{__('Click to Submit Again') }})</span>
                                                        </small>
                                                        @else
                                                        <span class="align-self-center mx-2 font-10 text-warning">
                                                             {{__('Under Review') }}
                                                        </span>
                                                        @endif

                                                   @endif
                                               @endif
                                            </p>
                                        </div>    
                                        </a>                             
                                    </div> 
                                </div>

                                <div class="col-12 col-md-6 mb-3"> 
                                    <div class="card mb-4 box-shadow"> 
                                        <div class="card-verification-holder">
                                            <p class="px-3">
                                               <span class="fa fa-lg fa-building mr-1"></span> Business (Coming Soon)
                                            </p>
                                        </div>                             
                                    </div> 
                                </div>
                            </div>
                             
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">

</script>
@endsection
