@php
  $pageTitle = __('index.view_coupon');
@endphp
@extends('layout.master')
@push('styles')
  <link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_coupon') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.view_coupon') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('coupon') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
  </div>
  <!-- View Coupon -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form id="form-view-coupon">
        <div class="card-header bg-light">
          <h5 class="mb-0">{{ __('index.view_coupon') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Coupon Code -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_code') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control " name="coupon_code" id="coupon_code" value="{{ $coupon->coupon_code }}" readonly />
            </div>

            <!-- Coupon Type -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_type') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control " name="coupon_type" id="coupon_type"
                value="{{ $coupon->coupon_type === 'flat' ? __('index.coupon_type_flat') : __('index.coupon_type_percent') }}" readonly />
            </div>

            <!-- Coupon Amount/Percent -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_amount_or_percentage') }} <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control " name="coupon_amount_or_percentage" id="coupon_amount_or_percentage" value="{{ $coupon->coupon_amount_or_percentage }}"
                readonly />
            </div>

            <!-- Coupon Cap Limit -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_cap_limit') }} <span class="text-danger">*</span></label>
              <input type="number" class="form-control " name="coupon_cap_limit" id="coupon_cap_limit" value="{{ $coupon->coupon_cap_limit }}" step="0.01" readonly />
            </div>

            <!-- Coupon Min Order Amount -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_min_order_amount') }} <span class="text-danger">*</span></label>
              <input type="number" class="form-control " name="coupon_min_order_amount" id="coupon_min_order_amount" value="{{ $coupon->coupon_min_order_amount }}" readonly step="0.01" />
            </div>

            <!-- Coupon Description -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.coupon_description') }}</label>
              <textarea class="tinymce" name="coupon_description" id="coupon_description">{{ $coupon->coupon_description }}</textarea>
            </div>

            <!-- Coupon Icon -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
              <label class="mb-0 form-label">{{ __('index.coupon_image') }}</label>
              @if (isset($coupon->coupon_image))
                <div id="image_preview" class="mt-2">
                  <img id="preview_img" src="{{ url('storage-bucket?path=' . $coupon->coupon_image) }}" alt="Image Preview" class="img-fluid" style="max-width: 125px; height: 125px;" />
                </div>
              @else
                <p><span>{{ __('index.image_not_available') }}</span></p>
              @endif
            </div>
          </div>

          <!-- Active Status -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="form-check">
              <input class="form-check-input" name="is_active" id="is_active" type="checkbox" onclick="return false" value="1" {{ $coupon->is_active == '1' ? 'checked' : '' }}>
              <label class="form-check-label" for="is_active">
                {{ __('index.active') }}
              </label>
            </div>
          </div>
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
  <script src="{{ asset('site-plugins/coupon/show.js?v=' . time()) }}"></script>
@endpush
