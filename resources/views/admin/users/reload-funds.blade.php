@extends('masters.admin')

@section('content')
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Reload Funds</div>
          <div class="card-body">
            <div class="row mb-2">
              <div class="col-12 col-md-5 col-lg-5">
                <a href="{{ url('/admin/users') }}" title="Back">
                  <button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button>
                </a>
              </div>
              <div class="col-12 col-md-7 col-lg-7">
                <form class="form-inline float-right" method="post" action="{{ route('users.reload-funds', ['user' => $user]) }}">
                  @csrf
                  <div class="form-group mb-2">
                    <label class="sr-only">{{ __('Coin') }}</label>
                    {!! Form::select('coin', $items, '', ['class' => 'form-control']) !!}
                  </div>
                  <div class="form-group mx-sm-3 mb-2">
                    <label class="sr-only">{{ __('Amount') }}</label>
                    <input type="text" class="form-control numeric" name="amount" value="" placeholder="Amount">
                  </div>
                  <button type="submit" class="btn btn-primary mb-2">{{ __('Add Funds') }}</button>
                </form>
              </div>
            </div>

            @if ($errors->any())
              <ul class="alert alert-danger">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            @endif


            <table class="table table-striped">
              <thead>
              <tr>
                <th scope="col">{{ __('Coin') }}</th>
                <th scope="col">{{ __('Balance') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($funds as $symbol => $balance)
              <tr>
                <th>{{ $symbol }}</th>
                <td>{{ currency($balance) }}</td>
              </tr>
              @endforeach
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
