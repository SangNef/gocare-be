<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@hasSection('htmlheader_title')@yield('htmlheader_title') - @endif{{ LAConfigs::getByKey('sitename') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.4 -->
    <link href="{{ asset('la-assets/css/bootstrap.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('la-assets/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <!--<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />-->
    <link href="{{ asset('la-assets/css/ionicons.min.css') }}" rel="stylesheet" type="text/css" />
    <!--<link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />-->
    <link href="{{ asset('la-assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <!-- jQuery 2.1.4 -->
    <script src="{{ asset('la-assets/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="{{ asset('la-assets/js/bootstrap.min.js') }}" type="text/javascript"></script>
</head>
<script>
    $(function(){
        window.print();
    });
</script>
<body>
<div class="wrapper" style="padding: 10px 25px">
    @yield('content')
</div>
</body>
</html>
