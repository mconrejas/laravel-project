@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-wallet">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start">
                        <div class="align-self-center">
                            <h5>{{__('Notification setting') }}</h5>
                            <small class="d-block"> {{__('You can switch off notification when not needed.')}}</small>
                        </div>
                    </div>
                </div>
                <div class="card-block p-4 d-flex border-top">
                    <div class="w-75 d-flex justify-content-start">
                        <span class="font-weight-bold form-label align-self-center">
                            {{__('Announcement')}} 
                            ({{__('via email')}}) 
                        </span>
                    </div>
                    <div class="w-25 d-flex justify-content-end">
                        <div class="align-self-center">
                            <span class="switch">
                                @if( auth()->user()->settings('announcement_enable', 1) == 1 )
                                <input type="checkbox" name="{{uniqid()}}" class="switch switch-sm" id="announcement_enable" checked>
                                @else
                                <input type="checkbox" name="{{uniqid()}}" class="switch switch-sm" id="announcement_enable">
                                @endif

                                <label for="announcement_enable">{{ (auth()->user()->settings('announcement_enable', 1) == 1 ) ? __('On') : __('Off') }} 
                                </label>
                            </span>
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
    var on_label = "{{__('On') }}";
    var off_label = "{{ __('Off') }}";

$(document).ready(function () {
    $(document).on('change', "#announcement_enable:checkbox",function(e){
        var isCheck = this.checked;
        $.post('{{route("my.settings")}}',{
            setting_key : 'announcement_enable',
            setting_value : isCheck ? 1 : 0
        }).done(function(data){
            $('.switch').find('label').text(isCheck ? on_label  : off_label ); 
            toast({ type: 'success', title: 'Setting updated' })
        }).fail(function(argument) {
            toast({ type: 'error', title: 'Oops!. Setting not updated!' })
        })
    })
});
</script>
@endsection
