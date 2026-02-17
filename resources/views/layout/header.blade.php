<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $pageTitle . ' | ' ?? '' }} {{ config('app.name') }}</title>
<link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/img/favicons/apple-icon-57x57.png') }}">
<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/img/favicons/apple-icon-60x60.png') }}">
<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/img/favicons/apple-icon-72x72.png') }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/favicons/apple-icon-76x76.png') }}">
<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/img/favicons/apple-icon-114x114.png') }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/img/favicons/apple-icon-120x120.png') }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/img/favicons/apple-icon-144x144.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/img/favicons/apple-icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-icon-180x180.png') }}">
   <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/logo_16x16.png') }}">
<link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
<meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/mstile-150x150.png') }}" />
<meta name="theme-color" content="#ffffff">
<meta name="baseurl" content="{{ url('/') }}">
<meta name="csrf_token" content="{{ csrf_token() }}" />
<!-- conffig and plugin -->
<script src="{{ asset('assets/js/config.js') }}"></script>
<script src="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<!-- fonts-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css?family=Nunito:300,400,500,600,700&amp;display=swap" rel="stylesheet">
<!-- css -->
<link href="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet" id="style-default" />
<link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet" id="user-style-default" />
<link rel="stylesheet" href="{{ asset('assets/css/jquery-confirm.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/toastify.min.css') }}" />


@stack('styles')
<link rel="stylesheet" href="{{ asset('assets/css/common.css?v=' . time()) }}" />
<script>
  var isFluid = JSON.parse(localStorage.getItem("isFluid"));
  if (isFluid) {
    var container = document.querySelector("[data-layout]");
    container.classList.remove("container");
    container.classList.add("container-fluid");
  }
  var navbarStyle = localStorage.getItem("navbarStyle");
  if (navbarStyle && navbarStyle !== "transparent") {
    document.querySelector(".navbar-vertical").classList.add(
      `navbar-${navbarStyle}`);
  }
</script>
