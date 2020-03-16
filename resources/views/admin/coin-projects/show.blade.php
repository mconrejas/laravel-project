@extends('masters.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-block p-3">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-warning">
                        <span class="fa fa-arrow-left"></span> Back
                    </a>
                    <a href="{{ route('project.edit',['id' => $project->id])  }}" class="btn btn-sm btn-primary">
                        <span class="fa fa-edit"></span> Edit
                    </a>

                    @if($project->status == 0 )
                        <form class="d-inline-block" action="{{ route('project.approve',['id' => $project->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="1">
                            <button type="submit" class="btn btn-sm btn-success">
                                <span class="fa fa-thumbs-up"></span> Approve
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-group">
                <div class="card" id="left-card">
                    <div class="card-body p-5">
                        <div class="card-block w-75 mx-auto media my-2">
                            <img class="align-self-center mr-5" src="{{ $project->iconUrl }}">
                            <div class="d-flex flex-column align-self-center">
                                <h2 class="mt-0 text-uppercase"> {{ $project->symbol }}</h2>
                                <h4 class="">{{ $project->name }}</h4>
                            </div>
                        </div>
                        <div class="card-block w-75 mx-auto my-2 justify-content-center">
                            <div class="row">
                                <div class="col-md-6 py-2">
                                    <h6>{{ __('Coin Type') }}</h6>
                                    <h4>{{ $project->infos['coin_type'] }}</h4>
                                </div>
                                <div class="col-md-6 py-2">
                                    <h6>{{ __('Date of Issue') }}</h6>
                                    <h4>{{ $project->infos['date_of_issue'] ?? 'Not set' }}</h4>
                                </div>
                                <div class="col-md-12 py-2">
                                    <h6>{{ __('Total Supply') }}</h6>
                                    <h4>{{ $project->infos['total_supply'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-5">
                        <div class="card-block d-flex justify-content-center flex-wrap my-3">
                            
                            <a href="{{ $project->infos['official_website'] }}" class="btn mx-1 my-1 btn-sm border">
                                {{ __('Official Website') }}
                            </a>
                            <a href="{{ $project->infos['whitepaper'] ?? '#' }}" class=" btn mx-1 my-1 btn-sm border">
                                {{ __('Whitepaper') }}
                            </a>
                            
                            @if($project->blockExplorer)
                            @foreach($project->blockExplorer as $index => $explorer )
                            <a href="{{ $explorer }}" class="btn mx-1 my-1 btn-sm border">
                                {{ __('Block Explorer') }} {{ $index+1 }}
                            </a>
                            @endforeach
                            @endif
                            <a href="{{ $project->infos['source_code'] }}" class="btn mx-1 my-1 btn-sm border">
                                {{ __('Source Code') }}
                            </a>
                        </div>
                        
                        <div class="card-block my-3">
                            <h5>{{ __('Project Description') }}</h5>
                            <p class="">
                                {{ $project->infos['project_description'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection