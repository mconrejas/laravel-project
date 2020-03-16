@extends('masters.admin')

@section('content')
    <style type="text/css">
        .croppie-container { overflow: hidden; }
        img { max-width: 100%;  }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Upload Icon for {{ $exchangeItem->name }}</div>
                    <div class="card-body">
                        <a href="{{ url('/admin/exchange-items') }}" title="Back"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
                        <br />
                        <br />
                        <div class="card-block">
                            <img id="image" src="{{asset('img/logo.png')}}">
                        </div>

                        <div class="card-block py-2 text-center">
                            <input type="file" class="d-none" id="iconfileupload" accept="image/*">
                            <button class="btn btn-dark" onclick="$('#iconfileupload').click()">
                                <i class="fa fa-upload"></i> Change image
                            </button>

                            <button class="btn btn-primary" id="crop-save-icon">
                                <i class="fa fa-save"></i> Crop And Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
    var iconWidth = parseInt("{{parameter('exchangeitem.icon_width', 120)}}");
    var iconHeight = parseInt("{{parameter('exchangeitem.icon_height', 120)}}");
    var uploadUrl  = '{{ route("exchangeitems.upload",["id" => $exchangeItem->item_id]) }}';

    $(document).ready(function(){
        var $uploadCrop;

        function readFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $uploadCrop.croppie('bind', { url: e.target.result })
                    .then(function(){
                        console.log('jQuery bind complete');
                    });
                }
                reader.readAsDataURL(input.files[0]);
            }
            else {
                alert("Sorry - you're browser doesn't support the FileReader API");
            }
        }

        $uploadCrop = $('#image').croppie({
            enableExif: true,
            viewport: {
                width: iconWidth,
                height: iconHeight,
                type: 'square'
            },
            boundary: {
                width:  $("#image").parents('.card-block').width(),
                height: $("#image").parents('.card-block').height()
            }
        });

        $('#iconfileupload').on('change', function () { 
            readFile(this); 
        });

        $(document).on("click","#crop-save-icon", function(){
            var button = $(this);
            button.btnProcessing('Cropping...Uploading...');

            $uploadCrop.croppie('result', {
                type: 'blob',
                size: 'viewport'
            }).then(function (response) {
                if (response) {
                    var formData = new FormData();
                    formData.append('icon', response);
                    $.ajax({
                        url: uploadUrl, 
                        type: "POST", 
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData
                    }).done(function(data){
                        toast({type:'success' , title : data.flash_message })
                        button.btnReset();
                    }).fail(function(){
                        toast({type:'error' , title : "Oops! Something went wrong" })
                        button.btnReset();
                    })
                }
            });
        })
    })
</script>
@endsection