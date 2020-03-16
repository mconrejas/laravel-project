@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-3">
            @include('main.wallet.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0 px-md-4" id="card-records">
                <h1 class="lead font-weight-bold py-4 mx-auto">
                {{__('Offline Wallet')}}
                </h1>

                <div class="alert alert-info my-3" role="alert">
                  <span class="fa fa-info-circle"></span>
                  {{__('Please follow these steps after downloading your wallet. ')}} <a href="https://support.buzzex.io/hc/en-us/articles/360010327953-First-Thing-To-Do-After-Downloading-Your-Buzzex-Coin-Wallet">{{__('First Thing To Do After Downloading Your Buzzex Coin Wallet')}}</a>
                </div>

                <div class="card-deck my-3 text-center">

                    <div class="card mb-4 shadow-sm border border-secondary">
                      <div class="card-header">
                        <h1 class="card-title pricing-card-title">{{__('Linux')}}</h1>
                      </div>
                      <div class="card-body">
                        <a href="{{url('/storage/downloads/wallets/linux.zip')}}">
                            <button type="button" class="btn btn-lg btn-block btn-buzzex rounded-0"><i class="fa fa-linux"></i> {{__('Download')}}</button>
                        </a>
                      </div>
                    </div>

                    <div class="card mb-4 shadow-sm border border-secondary">
                      <div class="card-header">
                        <h1 class="card-title pricing-card-title">{{__('Mac')}}</h1>
                      </div>
                      <div class="card-body">
                        <a href="{{url('/storage/downloads/wallets/mac.zip')}}">
                            <button type="button" class="btn btn-lg btn-block btn-buzzex rounded-0"><i class="fa fa-apple"></i> {{__('Download')}}</button>
                        </a>
                      </div>
                    </div>

                    <div class="card mb-4 shadow-sm border border-secondary">
                      <div class="card-header">
                        <h1 class="card-title pricing-card-title">{{__('Windows')}}</h1>
                      </div>
                      <div class="card-body">
                        <a href="{{url('/storage/downloads/wallets/windows32.zip')}}">
                            <button type="button" class="btn btn-lg btn-block btn-buzzex rounded-0"><i class="fa fa-windows"></i> {{__('Download 32Bit')}}</button>
                        </a>
                        <a href="{{url('/storage/downloads/wallets/windows.zip')}}">
                            <button type="button" class="btn btn-lg btn-block btn-buzzex rounded-0 mt-1"><i class="fa fa-windows"></i> {{__('Download 64Bit')}}</button>
                        </a>
                      </div>
                    </div>

                  </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
(function($) {
   
}(jQuery));

</script>
@endsection