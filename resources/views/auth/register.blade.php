<!DOCTYPE html>
<html lang="en-US" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register | {{ config('app.name') }}</title>
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/logo_32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/logo_16x16.png') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/logo_16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
  <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/logo_56X56.png') }}">
  <meta name="theme-color" content="#ffffff">
  <script src="{{ asset('assets/js/config.js') }}"></script>
  <script src="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.js') }}"></script>
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
  <link href="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/theme-rtl.min.css') }}" rel="stylesheet" id="style-rtl">
  <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet" id="style-default">
  <link href="{{ asset('assets/css/user-rtl.min.css') }}" rel="stylesheet" id="user-style-rtl">
  <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet" id="user-style-default">
  <style>
    #domain {
      text-transform: lowercase
    }

    .error-div {
      color: #800d0d;
      background: rgb(255, 202, 202);
      padding: 4px;
      font-size: small;
      border-radius: 6px;
    }
	a {
		color: #c20001;
		text-decoration: none;
	}
	a:hover {
		color: #c20001;
		text-decoration: underline;
	}
	.btn-primary{
		color: #fff;
		background-color: #c20001;
		border-color: #c20001;
	}
	.btn-primary:hover,.btn-primary:focus{
		color: #fff;
		background-color: #c20001;
		border-color: #c20001;
	}
  </style>
</head>

<body>
  <main class="main" id="top">
    <div class="container-fluid">
      <div class="row min-vh-100 flex-center g-0">
        <div class="col-lg-8 col-xxl-5 py-3 position-relative">
          <img class="bg-auth-circle-shape" src="{{ asset('assets/img/icons/spot-illustrations/bg-shape.png') }}" alt="" width="250" />
          <img class="bg-auth-circle-shape-2" src="{{ asset('assets/img/icons/spot-illustrations/shape-1.png') }}" alt="" width="150" />
          <div class="card overflow-hidden z-index-1">
            <div class="card-body p-0">
              <div class="row g-0 h-100">
                <div class="col-md-5 text-center bg-card-gradient d-flex align-items-center justify-content-center flex-column">
                  <div class="position-relative p-4 pt-md-5 pb-md-5 light">
                    <div class="">
                      <div class="bg-holder bg-auth-card-shape" style="background-image:url({{ asset('assets/img/icons/spot-illustrations/half-circle.png') }});">
                      </div>
                      <div class="z-index-1 position-relative">
                        <a class="link-light mb-4 font-sans-serif fs-4 d-inline-block fw-bolder" href="{{ url('/') }}">{{ config('app.name') }}</a>
                        <p class="opacity-75 text-white">
                          Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ea temporibus eum, sunt exercitationem impedit beatae illum dicta cumque, voluptatum,
                          maiores fugit praesentium culpa eligendi. Fugiat, reiciendis mollitia? Tempora, cumque rem.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-7 d-flex flex-center">
                  <div class="p-4 p-md-5 flex-grow-1">
                    <h3>Register</h3>
                    <form action="{{ url('account/sign-up') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                        <label class="form-label" for="card-name">Name</label>
                        <input class="form-control" type="text" autocomplete="on" id="card-name" name="name" />
                        @error('name')
                          <div class="error-div">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="mb-3">
                        <label class="form-label" for="card-email">Email address</label>
                        <input class="form-control" type="email" autocomplete="on" id="card-email" name="email" />
                        @error('email')
                          <div class="error-div">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="row gx-2">
                        <div class="mb-3 col-sm-6">
                          <label class="form-label" for="card-password">Password</label>
                          <input class="form-control" type="password" autocomplete="on" id="card-password" name="password" />
                          @error('password')
                            <div class="error-div">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="mb-3 col-sm-6">
                          <label class="form-label" for="card-confirm-password">Confirm Password</label>
                          <input class="form-control" type="password" autocomplete="on" name="conf_password" id="card-confirm-password" />
                          @error('conf_password')
                            <div class="error-div">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" name="terms" type="checkbox" id="card-register-checkbox" />
                        <label class="form-label" for="card-register-checkbox">I accept the <a href="{{ url('/terms-conditions') }}">terms </a>and <a href="{{ url('privacy-policy') }}">privacy
                            policy</a></label>
                      </div>
                      @error('terms')
                        <div class="mb-3">
                          <div class="error-div">{{ $message }}</div>
                        </div>
                      @enderror
                      <div class="mb-3">
                        <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Register</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script src="{{ asset('assets/vendors/popper/popper.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/bootstrap/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/anchorjs/anchor.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/is/is.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/fontawesome/all.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/lodash/lodash.min.js') }}"></script>
  <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
  <script src="{{ asset('assets/vendors/list.js/list.min.js') }}"></script>
  <script src="{{ asset('assets/assets/js/theme.js') }}"></script>
</body>

</html>
