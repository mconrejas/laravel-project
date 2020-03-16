@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Edit Withdrawal Item #{{ $item->transaction_id }}</div>
                    <div class="card-body">

                        <a href="{{ route('withdrawals') }}" title="Back" class="btn btn-warning btn-sm">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back
                        </a>
                        <br />
                        <br />

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        {!! Form::model($item, [
                            'method' => 'POST',
                            'url' => ['/admin/withdrawals/update', $item->transaction_id],
                            'class' => 'form-horizontal',
                            'files' => true
                        ]) !!}

                        @include ('admin.withdrawals.form', ['formMode' => 'edit'])

                        {!! Form::close() !!}

                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Update History</div>
                    <div class="card-body">
                        @if($item->logs)
                            @foreach($item->logs as $log)
                                <div class="alert alert-secondary">
                                    <strong>Date: </strong><span>{{ $log['timestamp'] }}</span><br>
                                    <strong>Updated By: </strong>
                                    @if($user = \Buzzex\Models\User::find($log['updated_by']))
                                        <span>{{ $user->email }}</span><br>
                                    @else
                                        <span>{{ __('System') }} ({{ $log['updated_by'] }})</span><br>
                                    @endif
                                    <strong>Notes: </strong><span>{{ $log['notes'] ?? '' }}</span>
                                </div>
                            @endforeach
                        @else
                        <div class="alert alert-secondary">
                            No updates logs yet.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
