@if($formMode === 'create')
<style type="text/css">
    .select2-container--default .select2-selection--single {
        border-radius: 0px;
    }
    #token_address_wrapper { display: none; }
</style>
<div class="form-group">
    <label>Select from submitted list.</label>
    <select class="form-control rounded-0 select2" id="select-from-list"></select>
</div>
@endif
<div class="form-group{{ $errors->has('first_name') ? ' has-error' : ''}}">
    {!! Form::label('name', 'Item Name: ', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('symbol') ? ' has-error' : ''}}">
    {!! Form::label('name', 'Symbol: ', ['class' => 'control-label']) !!}
    {!! Form::text('symbol', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('symbol', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('description') ? ' has-error' : ''}}">
    {!! Form::label('description', 'Description: ', ['class' => 'control-label']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'required' => 'required', 'rows'=>2]) !!}
    {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('type') ? ' has-error' : ''}}">
    {!! Form::label('type', 'Type: ', ['class' => 'control-label']) !!}
    {!! Form::select('type', $types, isset($exchangeItem) ? "$exchangeItem->type" : "", ['class' => 'form-control custom-select', 'multiple' => false]) !!}
</div>
<div class="form-group{{ $errors->has('token_address') ? ' has-error' : ''}}" id="token_address_wrapper">
    {!! Form::label('token_address', 'Token Address: ', ['class' => 'control-label']) !!}
    {!! Form::text('token_address', null, ['class' => 'form-control']) !!}
    {!! $errors->first('token_address', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('withdrawal_fee') ? ' has-error' : ''}}">
    {!! Form::label('name', 'Withdrawal Fee: ', ['class' => 'control-label']) !!}
    {!! Form::text('withdrawal_fee', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('withdrawal_fee', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('alternative_deposit') ? ' has-error' : ''}}">
    {!! Form::label('alternative_deposit', 'Alternative Deposit:', ['class' => 'control-label']) !!}
    {!! Form::select('alternative_deposit', ['1' => 'Enable', '0' => 'Disable', '-1' => 'Default'], isset($exchangeItem) ? "$exchangeItem->alternative_deposit" : "", ['class' => 'form-control custom-select']) !!}
    {!! $errors->first('alternative_deposit', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('alternative_withdrawal') ? ' has-error' : ''}}">
    {!! Form::label('alternative_withdrawal', 'Alternative Withdrawal:', ['class' => 'control-label']) !!}
    {!! Form::select('alternative_withdrawal', ['1' => 'Enable', '0' => 'Disable', '-1' => 'Default'], isset($exchangeItem) ? "$exchangeItem->alternative_withdrawal" : "", ['class' => 'form-control custom-select']) !!}
    {!! $errors->first('alternative_withdrawal', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('exchange_api_id') ? ' has-error' : ''}}">
    {!! Form::label('exchange_api_id', 'Exchange API:', ['class' => 'control-label']) !!}
    {!! Form::select('exchange_api_id', $apis, isset($exchangeItem) ? "$exchangeItem->exchange_api_id" : "", ['class' => 'form-control custom-select']) !!}
    {!! $errors->first('exchange_api_id', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('deposits_off') ? ' has-error' : ''}}">
    {!! Form::label('deposits_off', 'Deposits:', ['class' => 'control-label']) !!}
    {!! Form::select('deposits_off', ['1' => 'Disable', '0' => 'Enable'], isset($exchangeItem) ? "$exchangeItem->deposits_off" : "", ['class' => 'form-control custom-select']) !!}
    {!! $errors->first('deposits_off', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('withdrawals_off') ? ' has-error' : ''}}">
    {!! Form::label('withdrawals_off', 'Withdrawals:', ['class' => 'control-label']) !!}
    {!! Form::select('withdrawals_off', ['1' => 'Disable', '0' => 'Enable'], isset($exchangeItem) ? "$exchangeItem->withdrawals_off" : "", ['class' => 'form-control custom-select']) !!}
    {!! $errors->first('withdrawals_off', '<p class="help-block">:message</p>') !!}
</div>

@if($formMode === 'create')
<div class="form-group{{ $errors->has('icon') ? ' has-error' : ''}}">
    {!! Form::label('type', 'Icon: ', ['class' => 'control-label']) !!}
    <input type="hidden" name="icon" value="">
    <div class="alert alert-info">
        <ul>
            <li>Icon can be added after the item is created.</li>
            <li>Icon will be automatically added if selected from submitted coin projects</li>
        </ul>
    </div>
</div>
@endif

<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'px-5 btn btn-primary']) !!}
</div>

@if($formMode === 'create')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $('select#type').on('change',function(e){
            e.preventDefault();
            var currVal = $(this).val();
            var tokenAddressElem = $('#token_address');
            var tokenAddressWrapperElem = $('#token_address_wrapper');
            if ( currVal != 5) {
                tokenAddressWrapperElem.hide();
                tokenAddressElem.removeAttr('required');
            }else {
                tokenAddressWrapperElem.show();
                tokenAddressElem.attr('required','required');
            }
        });
        $('#select-from-list').select2({
            ajax : {
                url : "{{ route('listing.search',['locale'=>'en']) }}",
                dataType : 'json',
                delay : 200,
                data : function(params){
                    return { q : params.term };
                },
                processResults : function(data, params){
                    return {  results : data };
                }
            },
            templateResult : function (repo){
                if(repo.loading) return repo.name;
                return "<img height='30' src='/storage/icons/"+repo.logo+"'/>&nbsp;"+ repo.name +' ('+ repo.symbol +')';
            },
            templateSelection : function(repo)
            {
                return repo.name || repo.text;
            },
            debug: true,
            selectOnClose : true,
            escapeMarkup: function (markup) { return markup; },
            cache: true,
            placeholder: 'Search for coin project',
            minimumInputLength: 1
        })
        .on('select2:select', function(e){
            var data = e.params.data;
            var info = $.parseJSON(data.info);

            console.log(info.project_description)
            $("form input[name='name']").val(data.name);
            $("form input[name='symbol']").val(data.symbol);
            $("form input[name='icon']").val(data.logo);
            $("form textarea[name='description']").val(info.project_description);
        });
    })
</script>
@endpush

@endif
