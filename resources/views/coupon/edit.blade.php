@php
  $pageTitle = __('index.edit_coupon');
@endphp
@extends('layout.master')
@push('styles')
  <link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/flatpickr.min.css') }}">
  <style>
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_coupon') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.edit_coupon') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('coupon') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
  </div>
  <!-- Edit Coupon Type -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form id="form-edit-coupon" method="POST" enctype="multipart/form-data">
        @csrf
        @method('put')
        <div class="card-header bg-light">
          <h5 class="mb-0">{{ __('index.edit_coupon') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Coupon Code -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_code') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control " name="coupon_code" id="coupon_code" value="{{ old('coupon_code', $coupon->coupon_code) }}" />
            </div>

            <!-- Coupon Type -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_type') }} <span class="text-danger">*</span></label>
              <select class="custom-select form-control select2 " name="coupon_type" id="coupon_type">
                <option></option>
                <option value="flat" {{ $coupon->coupon_type === 'flat' ? 'selected' : '' }}>{{ __('index.coupon_type_flat') }}</option>
                <option value="percent" {{ $coupon->coupon_type === 'percent' ? 'selected' : '' }}>{{ __('index.coupon_type_percent') }}</option>
              </select>
            </div>

            <!-- Coupon Amount/Percent -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_amount_or_percentage') }} <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control " name="coupon_amount_or_percentage" id="coupon_amount_or_percentage"
                value="{{ old('coupon_amount_or_percentage', $coupon->coupon_amount_or_percentage) }}" />
            </div>

            <!-- Coupon Cap Limit -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_cap_limit') }} <span class="text-danger">*</span></label>
              <input type="number" class="form-control " name="coupon_cap_limit" id="coupon_cap_limit" value="{{ old('coupon_cap_limit', $coupon->coupon_cap_limit) }}" step="0.01" />
            </div>

            <!-- Coupon Min Order Amount -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_min_order_amount') }} <span class="text-danger">*</span></label>
              <input type="number" class="form-control " name="coupon_min_order_amount" id="coupon_min_order_amount" value="{{ old('coupon_min_order_amount', $coupon->coupon_min_order_amount) }}"
                step="0.01" />
            </div>
            @php 
				$startDate='';
				$endDate='';
				if($coupon->coupon_start_date!='' &&  $coupon->coupon_start_date!=NULL){
					$startDate = date('d-m-Y',strtotime($coupon->coupon_start_date));
				}
				if($coupon->coupon_end_date!='' &&  $coupon->coupon_end_date!=NULL){
					$endDate = date('d-m-Y',strtotime($coupon->coupon_end_date));
				}
			@endphp 
			<!-- Coupon Start Date -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_start_date') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control " name="coupon_start_date" id="coupon_start_date" placeholder="dd/mm/yyyy"  value="{{ $startDate }}" />
            </div>
			
			<!-- Coupon End Date -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_end_date') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control " name="coupon_end_date" id="coupon_end_date" placeholder="dd/mm/yyyy"  value="{{ $endDate }}" />
            </div>
			
            <!-- Coupon Description -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_description') }}</label>
              <textarea class="tinymce" name="coupon_description" id="coupon_description">{{ old('coupon_description', $coupon->coupon_description) }}</textarea>
            </div>

            <!-- Coupon Icon -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
              <label class="mb-0 form-label">{{ __('index.coupon_image') }}</label>
              <p id="accept_format" class="mb-0 {{ isset($coupon->coupon_image) ? 'd-none' : '' }}">
                <small class="form-label">
                  {{ __('index.accept_format') }}
                  {{ implode(', ', [__('index.jpg'), __('index.jpeg'), __('index.png')]) }}
                </small>
              </p>
              <input type="file" class="form-control {{ isset($coupon->coupon_image) ? 'd-none' : '' }}" name="coupon_image" id="coupon_image" accept=".jpg, .jpeg, .png" />
              <div id="image_preview" class="mt-2 {{ isset($coupon->coupon_image) ? '' : 'd-none' }}">
                <img id="preview_img" data-id="{{ $coupon->id }}" src="{{ isset($coupon->coupon_image) ? url('storage-bucket?path=' . $coupon->coupon_image) : '' }}" alt="Image Preview"
                  class="img-fluid" style="max-width: 125px; height: 125px;" />
                <button type="button" id="remove_image" class="btn btn-danger"
                  style="position: absolute; top: 5px; right: 5px; border-radius: 50%; width: 30px; height: 30px; pediting: 0; display: flex; align-items: center; justify-content: center;">
                  &times; <!-- This will display an 'X' -->
                </button>
              </div>
              <div id="error_message" class="text-danger mt-2" style="display: none;"></div>
            </div>

          </div>

          <!-- Active Status -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="form-check">
              <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $coupon->is_active == '1' ? 'checked' : '' }}>
              <label class="form-check-label" for="is_active">
                {{ __('index.active') }}
              </label>
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
          <button class="btn btn-primary" id="coupon-update" type="button">{{ __('index.update') }}</button>
        </div>
      </form>


    </div>
  </div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}"
    var id = "{{ $coupon->id }}"
  </script>
  <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
   <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="{{ asset('site-plugins/coupon/edit.js?v=' . time()) }}"></script>
@endpush
