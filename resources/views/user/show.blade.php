@extends('layout.master', ['pageTitle' => __('index.view_user')])
@push('styles')
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    tr.group,
    tr.group:hover {
        background-color: #ddd !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: transparent !important;
    }
	 .error {
      color: red;
    }
</style>

@endpush
@section('content')
<div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
        <i class="fas fa-circle fa-stack-2x text-300"></i>
        <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <div class="">
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.view_user')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('User.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/users') }}" class="text-decoration-none text-dark">{{__('index.users')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.view')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('User.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('users') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>
<!--ADD USER-->
 <div class="col-lg-12">
    <div class="card mb-3">
      <form >
       
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.view_user')}}</h5>
        </div>
        <div class="card-body"> 
          <div class="row">
            <!-- Role -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.role') }}</label>
              <input type="text" class="form-control mb-2" name="role" id="role" value="{{$user->role->name??"" }}" readonly />
			
			 
            </div>
			<!-- First Name -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.first_name')}}</label>
			  <input type="text" class="form-control mb-2" name="first_name" id="first_name" value="{{$user->first_name }}" readonly />
			
			</div>
			<!-- Last Name -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.last_name')}}</label>
			  <input type="text" class="form-control mb-2" name="last_name" id="last_name" value="{{ $user->last_name }}" readonly />
			  
			</div>
			<!-- Email -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.email')}}</label>
			  <input type="email" class="form-control mb-2" name="email" id="email" value="{{ $user->email }}" readonly />
			 
			</div>
			<!-- Phone Number -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.phone_number')}}</label>
			  <input type="number" class="form-control mb-2" name="phone_number" id="phone_number" value="{{ $user->phone_number }}" readonly />
			  
			</div>
		
			<!-- Avatar -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
				<div class="mb-3">
					<label class="form-label">{{__('index.avatar')}}</label>
					<img class="img-radius" id="showImage" src="{{  $user->avatar ?  asset('storage/'.$user->avatar) : asset('/assets/img/user/default-user.png') }}" height="80" width="80" />
					
				</div>
			</div>
							 
          </div>
          <div>
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $user->is_active == 1 ? 'checked' : '' }} disabled>
                <label class="form-check-label" for="is_active">
                  {{ __('index.active') }}
                </label>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
@push('scripts')
 
@endpush