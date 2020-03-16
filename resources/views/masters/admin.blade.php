<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="og:title" content="Buzzex">
    <meta name="og:description" content="The trading of future">
    <meta name="og:image" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="nodeport" content="{{parameter('node.port', 8443)}}">
    <link rel="icon" type="image/png" href="{{asset('img/58x58.ico')}}">
    <title>{{ config('app.name', 'Buzzex') }}</title>

    <link rel="stylesheet" href="{{mix('css/admin-global.css')}}">
    @yield('styles')

    @stack('styles')
</head>

<body class="admin">
    <div class="page-wrapper chiller-theme sidebar-bg {{auth()->user()->settings('admin_theme','bg1')}} toggled">
        <a id="show-sidebar" class="btn btn-md btn-dark" href="#">
            <i class="fa fa-indent"></i>
        </a>
        
        @include('admin.components.sidebar')
        <!-- sidebar-wrapper  -->

        <main class="page-content">
            @if (Session::has('flash_message'))
                <div class="container-fluid">
                    <div class="alert alert-info">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ Session::get('flash_message') }}
                    </div>
                </div>
            @endif

            @yield('content')

        </main>
        <!-- page-content" -->

    </div>
    <!-- page-wrapper -->

    <script src="{{ mix('js/admin-global.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.8.1/tinymce.min.js" type="text/javascript"></script>

    @include('sweetalert::alert')

    <script type="text/javascript">
        $(function () {
            
            tinymce.init({
                selector: '.crud-richtext'
            });

            if ($('.table.tabulator').length > 0) {
                var tabulator = new Tabulator('.table.tabulator',{ 
                    layout: "fitColumns",
                    responsiveLayout:"collapse",
                    placeholder: window.Templates.noDataAvailable(),
                    data: [],
                    layoutColumnsOnNewData: false
                });
            }

            $('.static-select2').select2({
                placeholder : 'Click to select'
            });

            $("[rel='tooltip']").tooltip({
                placement : 'auto'
            });
        });


    </script>

    @yield('scripts')

    @stack('scripts')
</body>

</html>