@if ($errors->any())
  <div class="alert alert-danger">
    @if ($errors->count() === 1)
      {!! $errors->all()[0] !!}
    @else
    <ul class="errors">
      @foreach ($errors->all() as $error)
        <li>{!! $error !!}</li>
      @endforeach
    </ul>
    @endif
  </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif