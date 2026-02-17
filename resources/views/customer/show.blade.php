@extends('layout.master', ['pageTitle' => __('index.view_customer')])
@push('styles')
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
 <link rel="stylesheet" href="{{ asset('assets/css/flatpickr.min.css') }}">
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.view_customer')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Customer.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/customers') }}" class="text-decoration-none text-dark">{{__('index.customers')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.view')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('Customer.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('customers') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>
<!--ADD USER-->
 <div class="col-lg-12">
    <div class="card mb-3">
      <form >
       
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.view_customer')}}</h5>
        </div>
        <div class="card-body"> 
          <div class="row">
            
			<!-- First Name -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.first_name')}}</label>
			  <input type="text" class="form-control mb-2" name="customer_first_name" id="customer_first_name" value="{{$customer->customer_first_name }}" readonly />
			
			</div>
			<!-- Last Name -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.last_name')}}</label>
			  <input type="text" class="form-control mb-2" name="customer_last_name" id="customer_last_name" value="{{ $customer->customer_last_name }}" readonly />
			  
			</div>
			<!-- Email -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.email')}}</label>
			  <input type="email" class="form-control mb-2" name="customer_email" id="customer_email" value="{{ $customer->customer_email }}" readonly />
			 
			</div>
			<!-- Phone Number -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.phone_number')}}</label>
			  <input type="number" class="form-control mb-2" name="customer_phone_number" id="customer_phone_number" value="{{ $customer->customer_phone}}" readonly />
			  
			</div>
		    <!-- Customer Wallet -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.wallet')}} </label>
			  <input type="text" class="form-control mb-2" name="customer_wallet" id="customer_wallet" value="{{ $customer->customer_wallet_balance }}" readonly />
			  
			</div>
			<!-- Customer Referral Wallet -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.referral_wallet')}} </label>
			  <input type="text" class="form-control mb-2" name="customer_referral_wallet" id="customer_referral_wallet" value="{{ $customer->customer_referral_wallet }}" readonly />
			  
			</div>
			<!-- Customer Referral Code -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.referral_code')}} </label>
			  <input type="text" class="form-control mb-2" name="customer_referral_code" id="customer_referral_code" value="{{ $customer->customer_referral_code }}" readonly />
			  
			</div>
			<!-- Customer Refer By -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.refer_by')}} </label>
			  <input type="text" class="form-control mb-2" name="customer_refer_by" id="customer_refer_by" value="{{$customer->referred_customer_first_name}} {{$customer->referred_customer_last_name}}" readonly />
			  
			</div>
			<!-- Avatar -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
				<div class="mb-3">
					<label class="form-label">{{__('index.avatar')}}</label>
					<img class="img-radius" id="showImage" src="{{  $customer->customer_avatar ?  asset('storage/'.$customer->customer_avatar) : asset('/assets/img/user/default-user.png') }}" height="80" width="80" />
					
				</div>
			</div>
							 
          </div>
          <div>
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $customer->is_active == 1 ? 'checked' : '' }} disabled>
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