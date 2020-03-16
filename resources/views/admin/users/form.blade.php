<div class="form-group{{ $errors->has('first_name') ? ' has-error' : ''}}">
    {!! Form::label('name', 'First Name: ', ['class' => 'control-label']) !!}
    {!! Form::text('first_name', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('first_name', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('last_name') ? ' has-error' : ''}}">
    {!! Form::label('name', 'Last Name: ', ['class' => 'control-label']) !!}
    {!! Form::text('last_name', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('last_name', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('email') ? ' has-error' : ''}}">
    {!! Form::label('email', 'Email: ', ['class' => 'control-label']) !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('email', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group $errors->has('password') ? ' has-error' : ''}}">
     {!! Form::label('password', 'Password: ', ['class' => 'control-label']) !!}
     @if($formMode === 'edit')
     <small class="text-danger"> If password is set it will change the current password.</small>
     @endif
    @php
        $passwordOptions = ['class' => 'form-control', 'autocomplete' => 'new-password'];
        if ($formMode === 'create') {
            $passwordOptions = array_merge($passwordOptions, ['required' => 'required']);
        }
    @endphp
  {!! Form::password('password', $passwordOptions) !!}
  {!! $errors->first('password', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('roles') ? ' has-error' : ''}}">
    {!! Form::label('role', 'Role: ', ['class' => 'control-label']) !!}
    {!! Form::select('roles[]', $roles, isset($user_roles) ? $user_roles : [], ['class' => 'rounded-0 static-select2 form-control', 'multiple' => true]) !!}
</div>
@if($formMode === 'edit')
<div class="form-group{{ $errors->has('email_verified_at') ? ' has-error' : ''}}">
    {!! Form::label('name', 'Email Verification: ', ['class' => 'control-label']) !!}
    
    @if(empty($user->email_verified_at))
        <span class="switch ml-5" rel="tooltip" title="Manually verify email">
            <input type="checkbox" name="email_verified_at" class="switch switch-sm checkbox-switch" id="switch-id">
            <label for="switch-id"><small>{{__('Verify email') }}</small></label>
        </span>
    @else
    <input type="datetime-local" class="form-control" value="{{$user->email_verified_at ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $user->email_verified_at)->format('Y-m-d H:i:s') : ''}}" readonly>
    @endif
    {!! $errors->first('email_verified_at', '<p class="help-block">:message</p>') !!}
</div>
@endif
<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>
