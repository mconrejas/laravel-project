@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-message-center">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start">
                        <div class="lead align-self-center"> {{__('Sign In Records')}} ({{__('30 Days')}})</div>
                    </div>
                </div>
                <div class="card-block py-1 d-flex ">
                    <table class="table table-sm border" id="login-record-table">
                      <thead>
                        <tr>
                          <th scope="col">{{ __('Sign-in Time') }}</th>
                          <th scope="col">{{ __('IP') }}</th>
                          <th scope="col">{{ __('Device') }}</th>
                          <th scope="col">{{ __('Location') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if (!$logs->isEmpty())
                            @foreach($logs as $log)
                            <tr>
                                <td>{{$log->created_at ?? ''}}</td>
                                <td>{{$log->ip ?? ''}}</td>
                                <td>
                                    {{$log->device['device'] ?? ''}}
                                    {{$log->device['platform'] ?? ''}}, 
                                    {{$log->device['browser'] ?? ''}}
                                </td>
                                <td>
                                    {{$log->location['country'] ?? ''}},
                                    {{$log->location['state_name'] ?? ''}},
                                    {{$log->location['city'] ?? ''}}
                                </td>
                            </tr>
                            @endforeach
                        @endif
                      </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function(argument) {
        var loginRecord = new Tabulator('#login-record-table', {
            fitColumns: true,
            layout: "fitColumns",
            columnMinWidth:80,
            responsiveLayout: "collapse",
            placeholder: window.Templates.noDataAvailable(),
        });
    });
</script>
@endsection

