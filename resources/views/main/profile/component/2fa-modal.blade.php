<style type="text/css">
.modal-dialog {
    text-align:center;
    padding:10px 0px;
    background-image: linear-gradient(270deg,#22e6b8,#00c1ce);
    background-size:100% 10px ;
}
.modal-dialog  .modal-content {
    border-radius: 0;
    border: none;
    margin: 0;
}
</style>
<div class="modal fade py-5"  id="twofaModal" tabindex="-1" role="dialog" aria-labelledby="twofaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-body p-5">
                <h2 class="text-center mt-2 mb-3">{{__('Enter 2FA code')}}</h2>
                <div class="w-100 d-flex my-2 justify-align-center">
                    <span class="fa fa-check-square fa-1x  align-self-center mr-1 text-secondary"></span>
                    <span class="align-self-center font-13 mr-1">Verify</span>
                    <span class="align-self-center mx-2 fa fa-long-arrow-right mr-4 text-buzzex"></span> 
                    <span class="fa fa-check-square fa-1x  align-self-center mr-1 text-secondary"></span>
                    <span class="align-self-center font-13 mr-1">Enter New Password</span>
                    <span class="align-self-center mx-2 fa fa-long-arrow-right mr-4 text-buzzex"></span>
                    <span class="fa fa-check-square fa-1x  align-self-center mr-1 text-secondary"></span>
                    <span class="align-self-center font-13">Done </span>
                </div>
                <form class="form-2fa" action="{{route('twofa.authenticate')}}" method="POST">
                    {{ csrf_field() }}
                    <div class="input-group my-5 w-100 mx-auto">
    				  <input type="text" name="one_time_password" class="form-control rounded-0" placeholder="{{__('Enter 2FA code')}}" required />
    				  <div class="input-group-append">
    					<button class="btn btn-buzzex rounded-0" type="submit">
                            <i class="fa fa-unlock"></i>
                        </button>
    				  </div>
    				</div>
    			</form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('click', '.form-2fa button[type="submit"]', function(e){
            e.preventDefault();
            var button = $(this);
            button.btnProcessing('.');
        })
        $(document).on('click',".open-2fa-modal",function(){
            $('#twofaModal').modal('show');
        })
    })
</script>