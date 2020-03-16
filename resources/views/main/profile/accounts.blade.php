@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
<div class="row justify-content-center">
    <div class="col-md-3">
        @include('main.profile.component.side-menu')
    </div>
    <div class="col-12 col-md-12 col-lg-9 my-cards">
        <div class="card rounded-0 pt-3 px-0" id="card-wallet">
            <div class="card-block p-4">
                <div class="row pb-md-5">
                    <div class="col-sm-9">
                        <div class="media profile-picture w-100 flex-wrap">
                            <div class="profile-picture-container mx-auto border border-secondary text-center">
                                <img id="profile_picture" src="{{ asset($profile_picture) }}" class="rounded" width="75">
                                <span class=" profile-picture-replacement-icon">
                                <i class="fa fa-edit"></i>
                                </span>
                            </div>
                            <div class="crop-container border border-secondary text-center">
                                <img id="profile_picture_crop" src="{{ asset($profile_picture) }}" class="rounded" width="75">
                                <span class=" profile-picture-replacement-icon">
                                <i class="fa fa-edit" onClick="replacePicture();"></i>
                                <i class="fa fa-save" onClick="savePicture();"></i>
                                </span>
                            </div>
                            <form id="profile-picture-replacement" method="post">
                                <input type="file" name="profile_picture" class="profile-picture-replacement">
                            </form>
                            <div class="media-body px-2 py-3 py-md-0 ">
                                <h5 class="mt-0">{{Auth::user()->name }} <button class="btn-update-name btn btn-link"><span class="fa fa-edit"> Edit</span></button></h5>
                                <form class="form-update-name d-none" method="post" action="{{ route('my.update_name') }}">
                                    {{ csrf_field() }}
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control rounded-0" name="first_name" required placeholder="First Name" value="{{ $user->first_name}}">
                                        <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="{{ $user->last_name}}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn-submit-update btn btn-buzzex rounded-0 text-light-on-dark text-dark input-group-button">Save</button>
                                        </div>
                                    </div>
                                </form>
                                <h6 class="mt-0">{{Auth::user()->email }}</h6>
                                <div class="d-flex flex-column d-md-block">
                                    <small>Last sign in at :</small>
                                    <small class="mr-1">{{$last_signin}}</small><br>
                                    <small>Last sign in IP :</small>
                                    <small class="mr-1">{{$last_ip}}</small><br>
                                    <a href="{{route('my.signin')}}" class="my-2 btn-link">{{ __('Sign-in Records') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                    </div>
                </div>
                <div class="card-block p-4 d-flex border-top">
                    <div class="w-75 d-flex justify-content-md-start flex-wrap">
                        <span class="font-weight-bold form-label align-self-md-center">
                            {{__('Bind Email Address')}} :
                        </span>
                        <span class="align-self-md-center">
                            {{Auth::user()->email}}
                        </span>
                        @if(Auth::user()->hasVerifiedEmail())
                        <span class="align-self-md-center mx-2 font-10 text-success">
                            <i class="fa fa-check"></i> {{__('Verified') }}
                        </span>
                        @else
                        <span class="align-self-md-center mx-2 font-10 text-warning">
                            <i class="fa fa-close"></i> {{__('Not verified') }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="card-block p-4 border-top">
                    <div class="row">
                        <div class="col-12 col-md-9">
                            <span class="font-weight-bold form-label align-self-center">
                                {{__('KYC Verification')}} :
                            </span>
                            <span class="align-self-center">
                            @if($personal_verification)
                                @if($personal_verification->approved == 0) 
                                <span class="align-self-center mx-2 font-10 text-warning">
                                    {{ __('Under Review') }}
                                </span> ( {{ $personal_verification->id_type }} )
                                @else 
                                    {{$personal_verification->id_type}}
                                    
                                    @if($personal_verification->approved == 1) 
                                    <span class="align-self-center mx-2 font-10 text-success">
                                        <i class="fa fa-check"></i> {{__('Verified') }}
                                    </span>
                                    @else
                                    <span class="align-self-center mx-2 font-10 text-danger">
                                        <i class="fa fa-times"></i> {{__('Rejected') }}
                                    </span>
                                    @endif
                                @endif
                            @else
                                {{__('Not Submitted')}}
                            @endif
                            </span>
                        </div>
                        <div class="col-12 col-md-3 ">
                            <div class="d-flex justify-content-md-end">
                                <div class="custom-control custom-checkbox custom-control-inline align-self-center">
                                    <input type="checkbox" class="custom-control-input" disabled>
                                    <label class="custom-control-label font-15 pointer-cursor ">
                                        <a href="{{route('my.selectMethod')}}" class="btn-link">{{__('Basic')}}</a>
                                    </label>
                                </div>
                                <span class="font-30 fa fa-long-arrow-right mx-3 align-self-center"></span>
                                <div class="custom-control custom-checkbox custom-control-inline align-self-center">
                                    <input type="checkbox" class="custom-control-input" disabled>
                                    <label class="custom-control-label font-15 pointer-cursor ">{{__('Advanced')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
.card-block .form-label {
    display: inline-block;
    min-width: 200px;
}
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var $uploadCrop;
    $(document).ready(function() {
        $(document).on('click','.btn-update-name',function(e){
            var button = $(this);
            var form = $('.form-update-name');
            button.removeClass('btn-update-name').addClass('btn-cancel-edit-name');
            button.find('span').removeClass('fa-edit').addClass('fa-close').text(' Cancel');
            form.removeClass('d-none');
        });
        $(document).on('click','.btn-cancel-edit-name',function(e){
            var button = $(this);
            var form = $('.form-update-name');
            button.removeClass('btn-cancel-edit-name').addClass('btn-update-name');
            button.find('span').removeClass('fa-close').addClass('fa-edit').text(' Edit');
            form.addClass('d-none');
        });
        $(document).on('click','.btn-submit-update',function(e){
            var button = $(this);
            button.btnProcessing('Saving...');
            var form = $('.form-update-name');
            $.post(form.attr('action'), form.serialize())
            .done(function(response){
                toast({ title : response.message, type : 'success', timer: 1000 })
                .then(function(){
                    window.location.reload();
                })
                button.btnReset();
            })
            .fail(function(xhr, status, error){
                alert({
                    type: 'error',
                    text: xhr.responseJSON.message 
                });
                button.btnReset();
            });
        });
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
            } else {
                alert({ text: "Sorry - you're browser doesn't support the FileReader API" });
            }
        }

        $uploadCrop = $('#profile_picture_crop').croppie({
            enableExif: true,
            viewport: {
            width:  150,
            height: 150,
            type: 'square'
            },
            boundary: {
            width:  150,
            height: 150
            }
        });

        $(document)
            .on('mouseenter', 'div.profile-picture-container', function(e) {
                var width = $(this).innerWidth();
                var left = width - $('span.profile-picture-replacement-icon').outerWidth();

                $('span.profile-picture-replacement-icon')
                .css('left', `${left - 38}px`)
                .animate({
                    opacity: 1
                }, 100)
            })
            .on('mouseenter', 'div.crop-container', function(e) {
                var width = $(this).innerWidth();
                var left = width - $('span.profile-picture-replacement-icon').outerWidth();
                
                $('span.profile-picture-replacement-icon')
                .css('left', `${left - 128}px`)
                .animate({
                    opacity: 1
                }, 100)
            })
            .on('mouseleave', 'div.profile-picture-container, div.crop-container', function(e) {
                $('span.profile-picture-replacement-icon')
                .animate({
                    opacity: 0
                }, 100)
            })
            .on('click', 'div.profile-picture-container', function(e) {
                replacePicture();
            })
            .on('change', '.profile-picture-replacement', function(e) {
                readFile(this);
                $('div.profile-picture-container').hide();
                $('div.crop-container').show();
            });
    });

    function replacePicture() {
        $('form#profile-picture-replacement')
        .find('input.profile-picture-replacement')
        .trigger('click');
    }

    function savePicture() {
        var formData = new FormData();

        $uploadCrop.croppie('result', {
            type: 'blob',
            size: 'viewport'
        }).then(function (response) {
            if (response) {
                formData.append('profile_picture', response );
                $.ajax({
                    url: "{{route('my.profile_picture')}}", 
                    type: "POST", 
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData
                }).done(function(data){
                    toast({type:'success' , title : data.flash_message })
                    $('img#profile_picture').attr('src', data.image_path)
                    $('div.crop-container').hide();
                    $('div.profile-picture-container').show();
                }).fail(function(){
                    toast({type:'error' , title : "Oops! Something went wrong" })
                });
            }
        })
    }
</script>
@endsection