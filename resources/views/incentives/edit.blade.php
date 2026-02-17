@php
  $pageTitle = __('index.edit_incentives');
@endphp
@extends('layout.master')
@push('styles')
  <link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
  <style>
    .error {
      color: red;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      background-color: transparent !important;
    }

    .card-body {
      height: 450px;
      overflow: auto;
      scrollbar-width: thin;
    }

    #remove_image {
      position: absolute;
      top: 5px;
      right: 5px;
      border: none;
      background: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      z-index: 10;
    }

    #image_preview {
      width: 125px;
      position: relative;
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_incentives') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.edit_incentives') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('vehicle') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
  </div>
  <!-- Edit incentives -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form id="form-edit-incentives" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-header bg-light">
          <h5 class="mb-0">{{ __('index.edit_incentives') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <input type="hidden" name="id" id="id" value="{{ $wallet->id }}" />
            
			 <!-- Driver  -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.driver') }} <span class="text-danger">*</span></label>
              <select class="form-control select2 custom-select" id="driver" name="driver" disabled>
			      <option value="{{$wallet->driver_id}}">{{ $wallet->driver->driver_first_name . ' ' . $wallet->driver->driver_last_name . ' (' . $wallet->driver->driver_phone . ')' }}</option>
			  </select>
            </div>

            <!-- Incentives Amount -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.amount') }} <span class="text-danger">*</span></label>
              <input type="number" class="form-control " name="amount" id="amount" value="{{ $wallet->amount }}" step="0.01" />
            </div>

           <!-- Operation  -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{ __('index.operation') }} <span class="text-danger">*</span></label>
			  <select class="custom-select form-control" name="operation" id="operation">
				<option value="" disabled selected>Select</option>
				<option value="add" {{ $wallet->type == 'incentives deposit' ? 'selected' : '' }}> {{ __('Add') }}</option>
        <option value="sub" {{ $wallet->type == 'incentives deduction' ? 'selected' : '' }}>{{ __('Subtract') }}</option>
			  </select>
			</div>
            <!-- Incentives Reason -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.reason') }} <span class="text-danger">*</span></label>
              <textarea class="form-control" name="reason" id="reason">{{ $wallet->message }}</textarea> 
            </div>
			
          </div>         
        </div>
        <div class="card-footer bg-light text-end">
          <button class="btn btn-primary" id="incentives-edit" type="button">{{ __('index.update') }}</button>
        </div>
      </form>
    </div>
  </div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}"
    var csrfToken = "{{ csrf_token() }}"
    var walletId = "{{ $wallet->id }}"
  </script>
  <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('site-plugins/incentives/edit.js?v=' . time()) }}"></script>
@endpush
