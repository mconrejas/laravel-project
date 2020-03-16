<div class="form-group{{ $errors->has('name') ? ' has-error' : ''}}">
    {!! Form::label('name', 'Name: ', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('guard_name') ? ' has-error' : ''}}">
    {!! Form::label('guard_name', 'Guard Name: ', ['class' => 'control-label']) !!}
    {!! Form::select('guard_name', $guards, isset($guards) ? $guards : [], ['class' => 'form-control custom-select', 'multiple' => false]) !!}
    {!! $errors->first('guard_name', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group{{ $errors->has('permissions') ? ' has-error' : ''}}">
    {!! Form::label('permissions', 'Permissions: ', ['class' => 'control-label']) !!}
    {!! Form::select('permissions[]', $permissions, isset($role->permissions) ? $role->permissions->pluck('name') : [], ['class' => 'form-control static-select2', 'multiple' => true]) !!}
    {!! $errors->first('permissions', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>
