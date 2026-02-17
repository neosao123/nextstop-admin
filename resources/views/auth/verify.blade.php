<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Reset | {{ config('app.name') }}</title>
     <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-icon.png') }}">
   <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-16x16.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/logo_16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
  <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/apple-icon-57x57.png') }}">
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
    <link href="{{ asset('assets/css/parsley.css') }}" rel="stylesheet" id="user-style-default">
    <script>
        var isFluid = JSON.parse(localStorage.getItem('isFluid'));
        if (isFluid) {
            var container = document.querySelector('[data-layout]');
            container.classList.remove('container');
            container.classList.add('container-fluid');
        }
    </script>
	<style>
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
        <div class="container" data-layout="container">

            <div class="row flex-center min-vh-100 py-6">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                    
                    <div class="card">
                        <div class="card-body p-4 p-sm-5">
						    <a class="d-flex flex-center mb-4" href="{{ url('/') }}">
							   <img class="d-block mx-auto mb-4 rounded-circle" src="../../../assets/img/carrier_logo_new.png" alt="carrier-logo" width="300">
								<span class="font-sans-serif fw-bolder fs-1 d-inline-block d-none">{{ config('app.name') }}</span>
							</a>
                            <div id="altbx" class="text-center text-danger"></div>
                            <div class="row flex-between-center mb-2">
                                <div class="col-auto">
                                    <h5>Change Password</h5>
                                </div>
                            </div>
                            <form method="POST" action="{{ url('/recovers-password') }}" data-parsley-validate="">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="password">New Password</label>
                                    <input type="hidden" name="code" value="{{ $result->code }}" readonly class="form-control">
                                    <input type="hidden" name="token" value="{{ $result->reset_token }}" readonly class="form-control">
                                    <input class="form-control" id="password" name="password" type="password" required="" data-parsley-required-message="Password is required" />
                                </div>
                                <div>
                                    <span class="text-danger">{{ $errors->first('password') }}</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                                    <input name="password_confirmation" id="password_confirmation" class="form-control" type="password" required="" data-parsley-required-message="Confirm Password is required">
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit" id="submit">Submit</button>
                                </div>
                                <div class="row flex-between-center">
                                    <div class="col-auto"><a class="fs--1" href="{{ url('/login') }}">Back to Login.</a></div>
                                </div>

                            </form>
                            @if (session('fail'))
                            <div class="my-alert alert alert-warning alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <strong>Opps!</strong> {{ session('fail') }}
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                            @endif
                            @if (session('error'))
                            <div class="my-alert alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <strong>Opps!</strong> {{ session('error') }}
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                            @endif
                            @if (session('message'))
                            <div class="my-alert alert alert-info alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    {{ session('message') }}
                                </div>
                            </div>
                            @endif
                            @if (session('success'))
                            <div class="my-alert alert alert-success alert-dismissible fade show" role="alert">
                                <div class="me-5">
                                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    {{ session('success') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript" src="https://parsleyjs.org/dist/parsley.min.js"></script>
    <script src="{{ asset('assets/vendors/popper/popper.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/anchorjs/anchor.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/is/is.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/fontawesome/all.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/lodash/lodash.min.js') }}"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
    <script src="{{ asset('assets/vendors/list.js/list.min.js') }}"></script>
    <script src="{{ asset('site-plugins/login/index.js') }}"></script> 

    <script>
        
    </script>

</body>

</html>