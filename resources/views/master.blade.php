<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }}</title>
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
  <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
  <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/mstile-150x150.png') }}">
  <meta name="theme-color" content="#ffffff">
  <script src="{{ asset('assets/js/config.js') }}"></script>
  <script src="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.js') }}"></script>
  <link href="{{ asset('assets/vendors/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
  <link href="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/theme-rtl.min.css') }}" rel="stylesheet" id="style-rtl">
  <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet" id="style-default">
  <link href="{{ asset('assets/css/user-rtl.min.css') }}" rel="stylesheet" id="user-style-rtl">
  <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet" id="user-style-default">
</head>

<body>

  <main class="main" id="top">
    <nav class="navbar navbar-standard navbar-expand-lg fixed-top navbar-dark" data-navbar-darken-on-scroll="data-navbar-darken-on-scroll">
      <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}"><span class="text-white dark__text-white">{{ config('app.name') }}</span></a>
        <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarStandard" aria-controls="navbarStandard" aria-expanded="false"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse scrollbar" id="navbarStandard">
          <ul class="navbar-nav" data-top-nav-dropdowns="data-top-nav-dropdowns">
            <li class="nav-item"><a class="nav-link" href="{{ url('about') }}">About</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('terms-conditions') }}">Terms</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('terms-conditions') }}">Privacy</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('terms-conditions') }}">Refund</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('faq') }}">FAQ</a></li>
          </ul>
        </div>
      </div>
    </nav>

    @yield('content')

  </main>

  <script src="{{ asset('assets/vendors/popper/popper.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/bootstrap/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/anchorjs/anchor.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/is/is.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/typed.js/typed.js') }}"></script>
  <script src="{{ asset('assets/vendors/fontawesome/all.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/lodash/lodash.min.js') }}"></script>
  <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
  <script src="{{ asset('assets/vendors/list.js/list.min.js') }}"></script>
  <script src="{{ asset('assets/js/theme.js') }}"></script>
</body>

</html>
