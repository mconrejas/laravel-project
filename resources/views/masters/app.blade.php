<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="og:title" content="Buzzex.io">
        <meta name="og:description" content="Exchange The Future.">
        <meta name="og:image" content="{{ asset('img/logo.png') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="nodeport" content="{{parameter('node.port', 8443)}}">
        <link rel="icon" type="image/png" href="{{ asset('img/58x58.ico') }}">
        <title>Buzzex</title>
        <link rel="stylesheet" type="text/css" href="{{ mix('css/'.$user_theme.'-global.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ mix('css/'.$user_theme.'-concatenated.css') }}">
        <script type="text/javascript" src="{{ mix('js/'.$user_theme.'-global.js')}}"></script>
        @yield('styles')
    
        @stack('styles')
    </head>

    <body class="theme-{{$user_theme}}">
        <div class="wrapper w-100 d-flex flex-column">

            @include('partials.header-top')
            
            @include('partials.header-bottom')

            <div class="w-100 flex-fill content-wrapper">

                @yield('content')

            </div>

            @include('partials.footer')

        </div>
        <script type="text/javascript" src="{{ mix('js/'.$user_theme.'-concatenated.js')}}" type="text/javascript"></script>
        
        @include('sweetalert::alert')

        @yield('scripts')
        
        @stack('scripts')

        @if(parameter('zendesk.widget_enable',1) == 1)
            <!-- Start of buzzex Zendesk Widget script -->
            <script defer id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=a59b3648-c39b-4f8d-99c0-a636bca173a7" type="text/javascript"> </script>
            <!-- End of buzzex Zendesk Widget script -->
        @endif
        <script type="text/javascript">
        $(document).ready(function() {
            /**
             * bind numeric to element
             */
            $(".numeric").numeric({
                allowMinus: false,
                allowThouSep: false,
                maxDecimalPlaces: 8,
                min: 0,
                max: 999999999
            });

            /**
             * display bootstrap tooltip
             */
            $('body').tooltip({
                selector: '[data-toggle=tooltip], [rel=tooltip]'
            });

            /**
             * check the checkbox that correspond this switcher
             */
            $(document).on('click', 'input[data-toggle="switch"]', function() {
                // check if switch button is checked
                if ($(this).attr("checked") == 'checked') {
                    $(this).removeAttr('checked');
                } else {
                    $(this).attr("checked", "checked");
                }
            });
        });
        </script>
        @auth
        <script type="text/javascript">
            window.count = 0;
            
            var markAsRead = function(id) {
                $.post("{{route('notifications.markasread')}}",{ id : id });
            };

            $(document).ready(function(){
                window.Echo.join('PublicPresenceChannel')
                    .here((users) => {
                        window.count = users.length;
                        $('.online-count').text(window.count)
                        $.get("{{route('notifications.unread')}}",{})
                        .done(function(data){
                            $.each(data, function(index, value){
                                if ($.trim(value.data.message) != '') {
                                    notifications({message : value.data.message })
                                }
                            })
                        });
                    })
                    .joining((user) => {
                        window.count++;
                        $('.online-count').text(window.count)
                    })
                    .leaving((user) => {
                        window.count--;
                        $('.online-count').text(window.count)
                    });

                window.Echo.private('Buzzex.Models.User.{{auth()->user()->id}}')
                    .notification((notification) => {
                        if ($.trim(notification.message) != '') {
                            notifications({message : notification.message , class: notification.id, onClosed : function(instance, toast, closedBy) {
                                    markAsRead(instance.class);
                                } 
                            })
                        }
                    });
            })
        </script>
        @endauth
    </body>

</html>
