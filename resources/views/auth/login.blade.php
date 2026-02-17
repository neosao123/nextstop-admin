<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | {{ config('app.name') }}</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/logo_16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
  <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/apple-icon-57x57.png') }}">
  <meta name="theme-color" content="#ffffff">
  <meta name="baseurl" content="{{ url('/') }}">
  <script src="{{ asset('assets/js/config.js') }}"></script>
  <script src="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.js') }}"></script>
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
  <link href="{{ asset('assets/vendors/overlayscrollbars/OverlayScrollbars.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/theme-rtl.min.css') }}" rel="stylesheet" id="style-rtl">
  <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet" id="style-default">
  <link href="{{ asset('assets/css/user-rtl.min.css') }}" rel="stylesheet" id="user-style-rtl">
  <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet" id="user-style-default">
    <link href="{{ asset('assets/css/parsley.css') }}" rel="stylesheet" id="user-style-default">
  <style>
	.bg-card-gradient {
		background: white !important;
	}
	.form-check-input:checked {
		background-color: #3AC9E0;
		border-color: #3AC9E0;
	}
	a {
		color: #0B1743;
		text-decoration: none;
	}
	a:hover {
		color: #0B1743;
		text-decoration: underline;
	}
	
	.btn-primary{
		  background: linear-gradient(to right, #0B1743, #3AC9E0) !important; 
          border: none;
	}
	.btn-primary:hover,.btn-primary:focus{
		  background: linear-gradient(to right, #0B1743, #3AC9E0) !important; 
          border: none;
	}
  </style>
</head>

<body>
  <main class="main" id="top">
    <div class="container-fluid">
      <div class="row min-vh-100 flex-center g-0">
        <div class="col-lg-8 col-xxl-5 py-3 position-relative"><img class="bg-auth-circle-shape" src="../../../assets/img/icons/spot-illustrations/bg-shape.png" alt="" width="250"><img
            class="bg-auth-circle-shape-2" src="../../../assets/img/icons/spot-illustrations/shape-1.png" alt="" width="150">
          <div class="card overflow-hidden z-index-1">
            <div class="card-body p-0">
              <div class="row g-0 h-100">
                <div class="col-md-5 text-center bg-card-gradient">
                  <div class="position-relative p-4 pt-md-5 pb-md-7 light">
                    <div class="bg-holder bg-auth-card-shape" style="background-image:url(../../../assets/img/icons/spot-illustrations/half-circle.png);">
                    </div>
                    <!--/.bg-holder-->

                    <div class="z-index-1 position-relative"><a class="link-light mb-4 font-sans-serif fs-4 d-inline-block fw-bolder" href="{{ url('/') }}"><img class="d-block mx-auto mb-4 rounded-circle" src="../../../assets/img/carrier_logo_new.png" alt="carrier-logo" width="300"></a>
                      <p class="opacity-75">Logistics Simplified: Your Trusted Delivery Companion</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-7 d-flex flex-center">
                  <div class="p-4 p-md-5 flex-grow-1">
                    <div class="row flex-between-center">
                      <div class="col-auto">
                        <h3>Account Login</h3>
                      </div>
                    </div>
                    <form method="POST" action="{{ url('/login') }}" >
                      @csrf
                      <div class="mb-3">
                        <label class="form-label" for="email">Email address</label>
                        <input class="form-control" id="email" type="email" name="email" required  data-parsley-required-message="Email is required." value="{{ Cookie::get('email') }}" />
                      </div>
                      <div>
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                      </div>
                      <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" required data-parsley-required-message="Password is required." value="{{ Cookie::get('password') }}" />
                      </div>
                      <div>
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                      </div>
                      <div class="row flex-between-center">
                        <div class="col-auto">
                          <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="rememberme" name="rememberme" @if (Cookie::get('email')) checked @endif />
                            <label class="form-check-label mb-0" for="card-checkbox">Remember me</label>
                          </div>
                        </div>
                        <div class="col-auto"><a class="fs--1" href="{{ url('forgot-password') }}">Forgot Password?</a></div>
                      </div>
                      <div class="mb-3">
                        <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Log in</button>
                      </div>
                    </form>
                    @if (session('fail'))
					  <div class="alert alert-warning alert-dismissible fade show" role="alert">
						<div class="me-5">
							{{ session('fail') }}
							<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
					    </div>
					  </div>
					@endif

					@if (session('error'))
					  <div class="alert alert-danger alert-dismissible fade show" role="alert">
						<div class="me-5">
							{{ session('error') }}
							<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
					    </div>
					  </div>
					@endif

					@if (session('message'))
					  <div class="alert alert-info alert-dismissible fade show" role="alert">
						<div class="me-5">
							{{ session('message') }}
							<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
					    </div>
					  </div>
					@endif

					@if (session('success'))
					  <div class="alert alert-success alert-dismissible fade show" role="alert">
						<div class="me-5">
							{{ session('success') }}
							<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
					    </div>
					  </div>
					@endif

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
   <script type="text/javascript" src="https://parsleyjs.org/dist/parsley.min.js"></script>
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
