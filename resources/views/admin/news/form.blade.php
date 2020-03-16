<div class="form-group {{ $errors->has('text') ? 'has-error' : ''}}">
    {!! Form::label('text', 'Text (HTML supported):', ['class' => 'control-label']) !!}
    {!! Form::textarea('text', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 2]) !!}
    {!! $errors->first('text', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('link') ? 'has-error' : ''}}">
    {!! Form::label('link', 'Link :', ['class' => 'control-label']) !!}
    {!! Form::url('link', null, ('required' == 'required') ? ['class' => 'form-control', 'required' => 'required'] : ['class' => 'form-control']) !!}
    {!! $errors->first('link', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('class') ? 'has-error' : ''}}">
    <label class="d-block">Class : Use Bootstrap 4 class</label>
    {!! Form::text('class', null, ['class' => 'form-control', 'placeholder' => 'default is btn-link']) !!}
    {!! $errors->first('class', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
    <div class="custom-control custom-checkbox">
        @if(isset($news) && $news->target == '_blank')
        <input type="checkbox" checked="checked" name="target" class="custom-control-input" id="target" value="_blank">
        @else
        <input type="checkbox" name="target" class="custom-control-input" id="target" value="_blank">
        @endif
        <label class="custom-control-label" for="target">Open link in new page</label>
    </div>
</div>

<div class="form-group preview">
    {!! Form::label('', 'Preview :', ['class' => 'control-label']) !!}
    <div class="w-100 border p-4 d-flex justify-content-between">
        <span class="fa fa-angle-double-left align-self-center"></span>
        <a href="" class="align-self-center">Sample Text</a>
        <span class="fa fa-angle-double-right align-self-center"></span>
    </div>
</div>

<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'btn btn-primary px-4']) !!}
</div>

@push('scripts')
<script type="text/javascript">

    $(document).on('keyup paste click', '.news-form input, .news-form textarea', function(e) {
        var form = $(this).parents('form');

        var sample = form.find('.preview').find('a');

        var classes = form.find("[name='class']").val();
        var text = form.find("[name='text']").val();
        var link = form.find("[name='link']").val();
        var target = form.find("[name='target']");

        sample.html(text);
        sample.attr('class', classes +' align-self-center');
        sample.attr('href', link);
        sample.attr('target', target.is(":checked") ? target.val() : '');
    });

</script>
@endpush