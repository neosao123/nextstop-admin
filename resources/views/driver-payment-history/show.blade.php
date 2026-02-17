@extends('layout.master', ['pageTitle' => __('index.view_transaction')])
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.view_transaction')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Driver Payment History.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/driver-payment-history') }}" class="text-decoration-none text-dark">{{__('index.driver_payment_history')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.view_transaction')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('Driver Payment History.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('driver-payment-history') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>
<!--ADD USER-->
 <div class="col-lg-12">
    <div class="card mb-3">
      <form >
       
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.view_transaction')}}</h5>
        </div>
        <div class="card-body"> 
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.driver_name')}}</label>
			  <input type="text" class="form-control mb-2" name="driver_name" id="driver_name" value="{{$walletTransaction->driver->driver_first_name }} {{$walletTransaction->driver->driver_last_name }}" readonly />
			</div>
			 <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.date')}}</label>
			  <input type="text" class="form-control mb-2" name="date" id="date" value="{{ date('d-m-Y', strtotime($walletTransaction->created_at)) }}" readonly />
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.type')}}</label>
			  <input type="text" class="form-control mb-2" name="type" id="type" value="{{$walletTransaction->type }}" readonly />
			</div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.message')}}</label>
			  <input type="text" class="form-control mb-2" name="message" id="message" value="{{$walletTransaction->message }}" readonly />
			</div>			
		    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.amount')}}</label>
			  <input type="text" class="form-control mb-2" name="amount" id="amount" value="{{$walletTransaction->amount }}" readonly />
			</div>
           
          </div>
      </form>
    </div>
  </div>
@endsection
@push('scripts')
 
@endpush