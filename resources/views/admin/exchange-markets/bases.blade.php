@extends('masters.admin')
@section('styles')
 
@endsection
@section('content')
<div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Base Market</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-5 col-md-5">
                                <select name="from[]" id="multiselect" class="form-control" size="8" multiple="multiple">
                                    @if($markets)
                                        @foreach($markets as $market)
                                            <option value="{{$market->item_id}}" @if(in_array($market->symbol, getBases())) selected @endif data-toggle="tooltip" title="{{$market->name}}">{{$market->symbol." (".$market->name.")"}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <div class="col-sm-2 col-md-2 align-self-center">
                                <button type="button" id="multiselect_rightAll" class="btn btn-block"><i class="fa fa-forward"></i></button>
                                <button type="button" id="multiselect_rightSelected" class="btn btn-block"><i class="fa fa-chevron-right"></i></button>
                                <button type="button" id="multiselect_leftSelected" class="btn btn-block"><i class="fa fa-chevron-left"></i></button>
                                <button type="button" id="multiselect_leftAll" class="btn btn-block"><i class="fa fa-backward"></i></button>
                            </div>
                            
                            <div class="col-sm-5 col-md-5">
                                <select name="to[]" id="multiselect_to" class="form-control" size="8" multiple="multiple"></select>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <button type="button" id="multiselect_move_up" class="btn btn-block"><i class="fa fa-arrow-up"></i></button>
                                    </div>
                                    <div class="col-sm-6">
                                        <button type="button" id="multiselect_move_down" class="btn btn-block"><i class="fa fa-arrow-down"></i></button>
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

@push('styles')
<style type="text/css">
    #multiselect, #multiselect_to {
        min-height: 450px;
    }
</style>
@endpush

@section('scripts')
<script type="text/javascript">
    jQuery(document).ready(function($) {

        // render multiselect
        $('#multiselect').multiselect({
            keepRenderingSort: false,
            afterMoveToRight: delaying,
            afterMoveToLeft: delaying,
            afterMoveUp: delaying,
            afterMoveDown: delaying
        });

        // trigger move when there are selected
        $('#multiselect_rightSelected').trigger('click');

    });

    function delaying(){
        clearTimeout($.data(this, 'timer'));
        var wait = setTimeout(updateBase(), 500);
        $(this).data('timer', wait);
    }

    function updateBase(){
        var optionValues = [];
        $('#multiselect_to option').each(function() {
            optionValues.push($(this).val());
        });

        $.post( "{{route('exchangemarkets.update')}}", {bases: optionValues} )
          .done(function( data ) {
            if(data.status=='OK')
                toast({type:'success' , title : data.flash_message }); 
          }); 
    }
</script>
@endsection