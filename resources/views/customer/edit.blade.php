@extends('layout.master', ['pageTitle' => __('index.edit_customer')])
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.edit_customer')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Customer.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/customers') }}" class="text-decoration-none text-dark">{{__('index.customer')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.edit')}}</li>
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
<!--edit customer-->
 <div class="col-lg-12">
    <div class="card mb-3">
      <form class="" id="form-edit-customer" method="POST" action="{{ url('customers/' . $customer->id) }}" enctype="multipart/form-data">
        @csrf
		@method('PUT')
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.edit_customer')}}</h5>
        </div>
        <div class="card-body"> 
          <div class="row">
            
			<!-- First Name -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.first_name')}}<span class="text-danger">*</span></label>
			  <input type="text" class="form-control mb-2" name="customer_first_name" id="customer_first_name" value="{{$customer->customer_first_name }}" />
			  @error('customer_first_name')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
			<!-- Last Name -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.last_name')}}<span class="text-danger">*</span></label>
			  <input type="text" class="form-control mb-2" name="customer_last_name" id="customer_last_name" value="{{ $customer->customer_last_name }}" />
			    @error('customer_last_name')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
			<!-- Email -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.email')}}<span class="text-danger">*</span></label>
			  <input type="email" class="form-control mb-2" name="customer_email" id="customer_email" value="{{ $customer->customer_email }}" />
			   @error('customer_email')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
			<!-- Phone Number -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.phone_number')}}<span class="text-danger">*</span></label>
			  <input type="number" class="form-control mb-2" name="customer_phone" id="customer_phone" value="{{ $customer->customer_phone }}" />
			     @error('phone_number')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
			<!-- Customer Wallet -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.wallet')}}</label>
			  <input type="text" class="form-control mb-2" name="customer_wallet" id="customer_wallet" value="{{ $customer->customer_wallet_balance }}" readonly />
			     @error('wallet')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
			<!-- Avatar -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
				<div class="mb-3">
					<label class="form-label">{{__('index.avatar')}}</label>
					<input type="file" id="file" class="form-control " name="avatar" accept=".jpg, .jpeg, .png">
				  
				</div>
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
				<img class="img-radius" id="showImage" src="{{  $customer->customer_avatar ?  asset('storage/'.$customer->customer_avatar) : asset('/assets/img/user/default-user.png') }}" height="80" width="80" />
				@if( $customer->customer_avatar!="" )
				<a href="{{ url('customers/delete/avatar/'.$customer->id) }}" class="mx-3 text-danger"><span class="fas fa-trash-alt"></span></a>
				@endif
            </div>					 
          </div>
          <div>
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $customer->is_active == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  {{ __('index.active') }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
          <button class="btn btn-primary" id="customer-save" type="submit">{{ __('index.update') }}</button>
           
		</div>
      </form>
    </div>
  </div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}"
  </script>
   <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  
  <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
 <script src="{{ asset('site-plugins/customer/edit.js?v=' . time()) }}"></script>
@endpush