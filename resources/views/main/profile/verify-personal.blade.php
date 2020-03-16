@extends('masters.app')

@section('content')
<form id="id_verification_form" method="POST" action="{{route('my.savePersonalVerification')}}" enctype="multipart/form-data">

@csrf

<style type="text/css">.conten-wrapper{width:100%;max-width:800px;margin:0px auto;} .card-select-image p, .card-select-image-back p, .card-select-image-selfie p{ position: absolute; width: 100%; padding: 16px 0; bottom: 0; background: rgba(255,255,255,0.7); font-size: 14px;  } </style>
<div class="container-fluid mt-5 mb-5 px-md-5 px-2">
 
        <div class="col-12 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="personal-id-verification">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start">
                        <div class="align-self-center conten-wrapper  " >
                            <h5>{{__('Personal ID verification') }}</h5>
                            <small class="d-block font-16"> {{__('Please make sure to use your own authentic ID documents for verification. All your submitted documents will NOT be provided to any unauthorized third party. Once approved, your verification type CANNOT be changed. Please pay attention.')}}</small>
                        </div>
                    </div>
                </div> 
            </div>
        </div>

        <div class="col-12 my-cards">
         
            <div class="card rounded-0 pt-3 px-0" id="personal-basic-info">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start">
                        <div class="align-self-center conten-wrapper">
                            <h5>{{__('Basic info') }} <span class="text-red">*</span> </h5> 
                        </div>
                    </div>
                </div>

                <!-- to do -->
                <div class="card-block p-4   ">
                    <div class=" justify-content-start">
                          
                            <div class="col-md-12 conten-wrapper mb-2" >
                                <label for="country">{{ __('Nationality') }}</label>
                                <select class="custom-select rounded-0 " id="country" required="" name="nationality">
                                  <option value="">Choose...</option>
                                  @foreach($countryOptions as $c => $countryOption)
                                    <option value="{{strtolower($c)}}" @if(old('nationality') == strtolower($c) || @$kyc->nationality == strtolower($c)) selected @endif>{{$countryOption.' - '.$c}}</option>
                                  @endforeach
                                </select> 
                            </div>

                          <!-- first name -->
                          <div class="col-md-12 conten-wrapper mb-2">
                            <label for="firstName">{{ __('First name') }}</label>
                            <input type="text" class="form-control rounded-0" id="firstName" name="first_name" value="{{ old('first_name') ? old('first_name') : @$kyc->first_name }}" required=""> 
                          </div>

                          <!-- last name -->
                          <div class="col-md-12 conten-wrapper mb-2">
                            <label for="lastName">{{ __('Last name') }}</label>
                            <input type="text" class="form-control rounded-0" id="lastName" name="last_name" value="{{ old('last_name') ? old('last_name') : @$kyc->last_name }}" required=""> 
                          </div>

                          <!-- Date of Birth -->

                          <div class="col-md-12 conten-wrapper mb-2">
                          
                    
                                 <label for="dateOfBirth">{{ __('Date of Birth') }}</label>

                                <div class="row">
                                  
                                  <div class="col-sm-6">
                                    @monthselect(['class' =>'custom-select rounded-0','id'=>'month'])
                                    @endmonthselect
                                  </div>

                                  <div class="col-sm-2">
                                    <select id="day" class="custom-select rounded-0" name="day" required="">
                                      <option value="" disabled selected="">Day</option>
                                      @for ($day = 1; $day <= 31; $day++)
                                        <option value="@if ($day < 10){{{'0'.$day }}}@else{{{ $day }}}@endif">@if ($day < 10){{{'0'.$day }}}@else{{{ $day }}}@endif</option>
                                      @endfor
                                    </select>
                                  </div>

                                  <div class="col-sm-4">
                                    <select id="year" class="custom-select rounded-0" name="year" required="">
                                      <option value="" disabled selected="">Year</option>
                                      @for ($year = 1900; $year <= 2016; $year++)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                      @endfor
                                    </select>
                                  </div>

                                </div>
                                </div>

                                 </div>

                          <!--Street Address-->

                          <div class="col-md-12 conten-wrapper mb-2">

                            <label for="streetAddress">{{ __('Street Address') }}</label>

                            <input type="text" class="form-control rounded-0" id="streetAddress" name="street_address" value="{{ old('street_address') ? old('street_address') : @$kyc->street_address }}" required=""> 
                          </div>

                         <!--Street Address 2-->

                          <div class="col-md-12 conten-wrapper mb-2">

                            <label for="streetAddress2">{{ __('Street Address 2') }}</label>

                            <input type="text" class="form-control rounded-0" id="streetAddress2" name="street_address2" value="{{ old('street_address2') ? old('street_address2') : @$kyc->street_address2 }}" required=""> 
                          </div>

                          <!-- city -->

                          <div class="col-md-12 conten-wrapper mb-2">

                            <label for="city">{{ __('City') }}</label>

                            <input type="text" class="form-control rounded-0" id="city" name="city" value="{{ old('city') ? old('city') : @$kyc->city }}" required=""> 
                          </div>

                          <!-- state -->

                          <div class="col-md-12 conten-wrapper mb-2">

                            <label for="state">{{ __('State') }}</label>

                            <input type="text" class="form-control rounded-0" id="state" name="state" value="{{ old('state') ? old('state') : @$kyc->state }}" required=""> 
                          </div>

                          <!-- Postal Code -->

                          <div class="col-md-12 conten-wrapper mb-2">

                            <label for="postalCode">{{ __('Postal Code') }}</label>

                            <input type="text" class="form-control rounded-0" id="postalCode" name="postal_code" value="{{ old('postal_code') ? old('postal_code') : @$kyc->postal_code }}" required=""> 
                          </div>

                          <!-- Country -->

                          <div class="col-md-12 conten-wrapper mb-2">

                             <label for="country">{{ __('Country') }}</label>

                                <select class="custom-select rounded-0 " id="country" required="" name="country">
                                  <option value="">Choose...</option>
                                  @foreach($countryOptions as $c => $countryOption)
                                    <option value="{{$countryOption}}" @if(old('country') == $countryOption || @$kyc->country == $countryOption) selected @endif>{{$countryOption}}</option>
                                  @endforeach
                                </select>

                           
                          </div>

                          <!-- Contact Number -->

                          <div class="col-md-12 conten-wrapper mb-2">

                            <label for="contactNumber">{{ __('Contact Number') }}</label>

                            <input type="text" class="form-control rounded-0" id="contactNumber" name="contact_number" value="{{ old('contact_number') ? old('contact_number') : @$kyc->contact_number }}" required="" placeholder="e.g. 123-456-7890"> 
                             <small>Allowed characters are <b>0-9</b> , <b>-</b> , <b>+</b>, <b>(</b>, <b>)</b> and <b>.</b></small><br><small>Following are examples</small>
                              <ul>
                                  <li><small>123-456-7890</small></li>
                                  <li><small>(123)456-7890</small></li>
                                  <li><small>1234567890</small></li>
                                  <li><small>123.456.7890</small></li>
                                  <li><small>+91(123)456-7890</small></li>
                              </ul>
                          </div>

                    </div>
                
                </div>
            </div>

        <div class="col-12 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="personal-id">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start conten-wrapper">
                        <div class="align-self-center">
                            <h5>{{__('ID Verification:') }} <span class="text-red">*</span> </h5> 
                        </div>
                    </div>
                </div>
                <div class="card-block p-4 d-flex border-top">
                    <div class="justify-content-start conten-wrapper">                       
                               <div class="col-md-12">
                                <label for="country">{{ __('ID type') }}:</label>
                                 
                                <div class="d-block  ">
                                  <div class="custom-control custom-radio">
                                    <input id="verify-passport" name="id_type" type="radio" class="custom-control-input" @if(old('id_type')=='passport' || @$kyc->id_type=='passport') checked @endif required="" value="passport">
                                    <label class="custom-control-label" for="verify-passport">{{__('Passport') }}</label>
                                  </div>
                                  <div class="custom-control custom-radio">
                                    <input id="verify-license" name="id_type" type="radio" class="custom-control-input" @if(old('id_type')=='driving-license' || @$kyc->id_type=='driving-license') checked @endif required="" value="driving-license">
                                    <label class="custom-control-label" for="verify-license">{{__('Driver\'s License') }}</label>
                                  </div> 
                                </div>
                            </div>
                    </div>
                </div> 
                <div class="card-block p-4 ">
                    <div class="justify-content-start conten-wrapper">

                          <div class="col-md-6 mb-3">
                            <label for="id_number">{{ __('ID Number') }}:</label>
                            <input type="text" class="form-control rounded-0" id="id_number" name="id_number" value="{{old('id_number')?old('id_number'):@$kyc->id_number}}" required=""> 
                          </div> 

                    </div>
                </div>
                <div class="card-block p-4 " id="card_select_image_holder">
                    <div class="row conten-wrapper">
                           <div class="col-lg-5 col-md-4 col-sm-4 mb-3">
                            <label class="font-weight-bold form-label">{{ __('Front of ID document') }}</label> 
                              <div class="card mb-4 box-shadow">
                                <div class="card-select-image position-relative" style="overflow: hidden; background-image: url({!!@$photos->front!!});">
                                    <p>
                                        <span class="fa fa-lg  fa-plus mr-1"></span> <span class="choose-file-text">{{ __('Choose File') }}</span>
                                    </p>
                                </div>                                 
                              </div>
                              <div class="invalid-feedback inv-front-id">
                                  {{ __('Please upload your front ID') }}
                              </div>  
                               @if($errors->has('front_id'))
                                <div class="invalid-feedback" style="display: block;">
                                    {{ __('Please upload a valid image file! File must be less than 2048KB') }}
                                </div>
                               @endif
                          </div>

                          <div class="col-lg-5 col-md-4 col-sm-4 mb-3">
                            <label class="font-weight-bold form-label">{{ __('Sample') }}</label> 
                              <div class="card mb-4 box-shadow">
                                <img class="card-img-top" alt="Sample Image" src="{{asset('img/front-id.png')}}" data-holder-rendered="true">
                              </div>
                          </div>
                    </div>  
                </div>
                <div class="card-block pb-5 pl-5  ">
                    <div class="justify-content-start conten-wrapper">
                        <span class="text-red font-18">* {{ __('Please make sure your ID documents are clear and show your name, ID number etc') }}.</span>
                        <br>
                        <span class="text-red font-18">* {{ __('File size limit is 2MB.') }}</span>
                    </div>
                </div>
                <div class="card-block p-4" id="card_select_image_holder">
                    <div class="row conten-wrapper back-id-wrapper" style="@if(@$kyc->id_type=='passport') display: none; @endif">
                           <div class="col-lg-5 col-md-4 col-sm-4 mb-3">
                            <label class="font-weight-bold form-label">{{ __('Back of ID document') }}</label> 
                              <div class="card mb-4 box-shadow">
                                <div class="card-select-image-back position-relative" style="overflow: hidden; background-image: url({!!@$photos->back!!});">
                                    <p>
                                        <span class="fa fa-lg  fa-plus mr-1"></span> <span class="choose-file-text">{{ __('Choose File') }}</span>
                                    </p>
                                </div>                                 
                              </div>
                              <div class="invalid-feedback inv-back-id">
                                  {{ __('Please upload the back photo of your ID') }}
                              </div>  
                               @if($errors->has('back_id'))
                                <div class="invalid-feedback" style="display: block;">
                                    {{ __('Please upload a valid image file! File must be less than 2048KB') }}
                                </div>
                               @endif
                          </div>

                          <div class="col-lg-5 col-md-4 col-sm-4 mb-3">
                            <label class="font-weight-bold form-label">{{ __('Sample') }}</label> 
                              <div class="card mb-4 box-shadow">
                                <img class="card-img-top" alt="Sample Image" src="{{asset('img/back-id.png')}}" data-holder-rendered="true">
                              </div>
                          </div>
                    </div>  
                </div>
                <div class="card-block pb-5 pl-5  ">
                    <div class="justify-content-start conten-wrapper">
                        <span class="text-red font-18">* {{ __('Please make sure your ID documents are clear and show your name, ID number etc') }}.</span>
                        <br>
                        <span class="text-red font-18">* {{ __('File size limit is 2MB.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 my-cards"  id="card_select_image_selfie_holder">
            <div class="card rounded-0 pt-3 px-0" id="statement-verification">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start conten-wrapper">
                        <div class="align-self-center">
                            <h5>{{__('Statement Verification:') }} <span class="text-red">*</span> </h5> 
                            <small class="d-block font-16"> {{__('Upload a photo of you handholding the front of ID document and statement; Please write down the date of submission and "Buzzex".')}}</small>
                        </div>
                    </div>
                </div>

                <div class="card-block p-4 d-flex border-top">
                    <div class="row conten-wrapper">
                           <div class="col-md-4 col-sm-8 mb-3">
                            <label class="font-weight-bold form-label">
                              {{ __('Photo of handhold statement') }}</label> 
                              <div class="card mb-4 box-shadow">
                                <div class="card-select-image-selfie position-relative" style="overflow: hidden; background-image: url({!!@$photos->selfie!!});">
                                    <p>
                                        <span class="fa fa-lg  fa-plus mr-1"></span> 
                                        <span class="choose-file-text">{{ __('Choose File') }}</span>
                                    </p>
                                </div>                                 
                              </div>
                              <div class="invalid-feedback inv-front-selfie-id">
                                  {{ __('Please upload photo of handhold statement') }}
                              </div>

                              @if($errors->has('selfie_id'))
                                <div class="invalid-feedback" style="display: block;">
                                    {{ __('Please upload a valid image file! File must be less than 2048KB') }}
                                </div>
                               @endif
                          </div>

                          <div class="col-md-4 col-sm-5 mb-3">
                            <label class="font-weight-bold form-label">{{ __('Correct') }}</label> 
                              <div class=" mb-4">
                                <img class="card-img-top" alt="Sample Image" src="{{ asset('img/c1.jpg')}}" data-holder-rendered="true">
                              </div>
                          </div>
                          <div class="col-md-4 col-sm-5 mb-3">
                            <label class="font-weight-bold form-label">{{ __('Correct') }}</label> 
                              <div class=" mb-4">
                                <img class="card-img-top" alt="Sample Image" src="{{asset('img/c2.jpg')}}" data-holder-rendered="true">
                              </div>
                          </div>

                    </div>  
                </div>

                <div class="card-block p-4">
                    <div class="row conten-wrapper">
                          <div class="col-md-6 mb-3">
                              <label class="font-weight-bold form-label">{{ __('Wrong') }}</label> 
                                <div class="card mb-4 box-shadow">
                                  <img class="card-img-top" alt="Sample Image" src="{{asset('img/c3.jpg')}}" data-holder-rendered="true">
                                </div>
                          </div>
                          <div class="col-md-6 mb-3">
                              <label class="font-weight-bold form-label">{{ __('Wrong') }}</label> 
                                <div class="card mb-4 box-shadow">
                                  <img class="card-img-top" alt="Sample Image" src="{{asset('img/c5.jpg')}}" data-holder-rendered="true">
                                </div>
                          </div>


                    </div>
                  </div>
                <div class="card-block pb-5 pl-5  ">
                    <div class="justify-content-start conten-wrapper">
                        <span class="text-red font-18">* {{ __('Please make sure your ID documents are clear and show your name, ID number etc') }}.</span>
                        <br>
                         <span class="text-red font-18">* {{ __('File size limit is 2MB.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 my-cards">
            <div class="card rounded-0 pt-3 px-0 " >
                <div class="card-block p-4 d-flex conten-wrapper">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="oath-checkbox" required="">
                      <label class="custom-control-label font-18" for="oath-checkbox">
                        {{ __('I promise to be the legitimate owner of these ID documents') }}.</label>
                    </div>
                </div>
                <div class="card-block p-4 d-flex justify-content-center">
                    <button type="submit" class="align-self-center btn btn-primary px-5 btn-gradient-green font-20 btn-lg">
                        <span class="fa fa-reply fa-flip-horizontal"></span> {{ __('Submit') }}
                      </button>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="file" id="front_id" name="front_id" style="visibility: hidden;" onchange="uploadIdentification(this,'front_id','.card-select-image');">
<input type="hidden" name="front_id_path" id="front_id_path" value="" />
<input type="file" id="back_id" name="back_id" style="visibility: hidden;" onchange="uploadIdentification(this,'back_id','.card-select-image-back');">
<input type="hidden" name="back_id_path" id="back_id_path" value="" />
<input type="file" id="selfie_id" name="selfie_id" style="visibility: hidden;" onchange="uploadIdentification(this,'selfie_id','.card-select-image-selfie');">
<input type="hidden" name="selfie_id_path" id="selfie_id_path" value="" />

</form>
@endsection

@section('scripts')
<script type="text/javascript">

    $(document).on('click', '.card-select-image', function(){
        if ( $(this).hasClass('processing')){
          return;
        }
        $('#front_id').trigger('click');  
    });

    $(document).on('click', '.card-select-image-back', function(){
        if ( $(this).hasClass('processing')){
          return;
        }
        $('#back_id').trigger('click');  
    });

    $(document).on('click', '.card-select-image-selfie', function(){
        if ( $(this).hasClass('processing')){
          return;
        }
        $('#selfie_id').trigger('click');  
    });
    
    $(document).on('click', 'input[name=id_type]:checked', function(){
        if($(this).val() == 'driving-license'){
            $('.back-id-wrapper').slideDown(1000);
        }else if($(this).val() == 'passport'){
            $('.back-id-wrapper').slideUp(1000);
        }
    });

    $('#id_verification_form').submit( function(e) {
        e.preventDefault();
        var front_id = $('#front_id').val();
        var back_id = $('#back_id').val();
        var selfie_id = $('#selfie_id').val();
        var id_type = $('input[name=id_type]:checked').val();

        if(!front_id){
            scrollMeUp('#card_select_image_holder')
            $('.card-select-image').css('border','1px solid red');
            $('.inv-front-id').attr('style','display:block !important;');
        }else if(!back_id && id_type == 'driving-license'){
            scrollMeUp('#card_select_image_holder')
            $('.card-select-image-back').css('border','1px solid red');
            $('.inv-back-id').attr('style','display:block !important;');
        }else if(!selfie_id){
            scrollMeUp('#card_select_image_selfie_holder')
            $('.card-select-image-selfie').css('border','1px solid red');
            $('.inv-front-selfie-id').attr('style','display:block !important;');
        }else{
            $(this).unbind('submit').submit();
        }
        
    })

    function readURL(input, target) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $(target).css('background-image','url("'+e.target.result+'")'); 
                //$('.card-select-image p').hide();
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    function readURLfromSrc(src, target) {
        
          var reader = new FileReader();

          reader.onload = function (e) {
              $(target).css('background-image','url("'+src+'")'); 
              //$('.card-select-image p').hide();
          }
        
    }

    function scrollMeUp(target){
        $('html, body').animate({
            scrollTop: $(target).offset().top
        }, 500);
    }

    function uploadIdentification(obj,name,target){
      var form = $('#id_verification_form');
      var formData = new FormData();
      var fileData = $(obj)[0].files[0];
      var hiddenFilePathElem = $('#'+name+'_path');
      var targetElem = $(target);
      var chooseFileElem = targetElem.find('.choose-file-text');
      var origText = chooseFileElem.text();
      
      if ( typeof(fileData) != 'undefined'){
        formData.append('file',fileData);

        $.ajax({
          url : "{{route('my.verifyUpload')}}",
          type : 'post',
          processData : false,
          contentType : false,
          data : formData,
          beforeSend : function(){
            //reset value file path
            hiddenFilePathElem.val('');
            targetElem.addClass('processing');
            form.find('button[type=submit]').attr('disabled','disabled');
            chooseFileElem.text('Uploading...');

          }

        }).done(function(data){
          
          if ( typeof(data.image_path_url) != 'undefined'){
            hiddenFilePathElem.val(data.image_path);

            $(target).css('background-image','url("'+data.image_path_url+'")'); 
            
          }

          form.find('button[type=submit]').removeAttr('disabled');
          chooseFileElem.text(origText);
          targetElem.removeClass('processing');

        }).fail(function(xhr){
            var data = xhr.responseJSON;
            
            form.find('button[type=submit]').removeAttr('disabled');
            chooseFileElem.text(origText);
            targetElem.removeClass('processing');

            swal('Error',data.message,'error');
        });
      }
    }

   $(document).ready(function(){

    $("#id_verification_form input[name='contact_number']").keypress(function (e) {
            var allowedChars = new RegExp("^[0-9.()+-]*$");
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (allowedChars.test(str)) {
                return true;
            }
            e.preventDefault();
            return false;
        })
     });



</script>
@endsection
