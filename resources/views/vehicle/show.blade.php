@php
  $pageTitle = __('index.view_vehicle');
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
      width: 320px;
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_vehicle') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.view_vehicle') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('vehicle') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
  </div>
  <!-- Add Vehicle Type -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <div class="card-header bg-light">
        <h5 class="mb-0">{{ __('index.view_vehicle') }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Vehicle Type -->
          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_type') }} </label>
            @isset($vehicle_type)
              @foreach ($vehicle_type as $value)
                @if ($value->id === $vehicle->vehicle_type_id)
                  <input class="form-control" type="text" name="vehicle_type" id="vehicle_type" value="{{ $value->vehicle_type }}" readonly />
                @endif
              @endforeach
            @endisset
          </div>

          <!-- Vehicle Name -->
          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_name') }} </label>
            <input type="text" class="form-control" name="vehicle_name" id="vehicle_name" value="{{ $vehicle->vehicle_name }}" readonly />
          </div>

          <!-- Vehicle Dimensions -->
          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_dimensions') }} </label>
            <input type="text" class="form-control " name="vehicle_dimensions" id="vehicle_dimensions" value="{{ $vehicle->vehicle_dimensions }}" placeholder="LxBxH" readonly />
          </div>

          <!-- Max Load Capacity -->
          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_max_load_capacity') }} </label>
            <input type="number" class="form-control " name="vehicle_max_load_capacity" id="vehicle_max_load_capacity" value="{{ $vehicle->vehicle_max_load_capacity }}" step="0.01" readonly />
          </div>
		  
		  <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.vehicle_fixed_km') }} </label>
              <input type="text" class="form-control " name="vehicle_fixed_km" id="vehicle_fixed_km" value="{{ old('vehicle_fixed_km', $vehicle->vehicle_fixed_km) }}" readonly />
            </div>

          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_fixed_km_delivery_charge') }} </label>
            <input type="number" class="form-control " name="vehicle_fixed_km_delivery_charge" id="vehicle_fixed_km_delivery_charge" value="{{ $vehicle->vehicle_fixed_km_delivery_charge }}" step="0.01"
              readonly />
          </div>
          <!-- Per KM Delivery Charge -->
          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_per_km_delivery_charge') }} </label>
            <input type="number" class="form-control " name="vehicle_per_km_delivery_charge" id="vehicle_per_km_delivery_charge" value="{{ $vehicle->vehicle_per_km_delivery_charge }}" step="0.01"
              readonly />
          </div>

          <!-- Per KM Extra Delivery Charge -->
          <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_per_km_extra_delivery_charge') }} </label>
            <input type="number" class="form-control " name="vehicle_per_km_extra_delivery_charge" id="vehicle_per_km_extra_delivery_charge"
              value="{{ $vehicle->vehicle_per_km_extra_delivery_charge }}" step="0.01" readonly />
          </div>

          <!-- Vehicle Description -->
          <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_description') }}</label>
            <textarea class="tinymce" name="vehicle_description" id="vehicle_description">{{ $vehicle->vehicle_description }}</textarea>
          </div>

          <!-- Vehicle Rules -->
          <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
            <label class="form-label">{{ __('index.vehicle_rules') }}</label>
            <textarea class="tinymce" name="vehicle_rules" id="vehicle_rules">{{ $vehicle->vehicle_rules }}</textarea>
          </div>

          <!-- Vehicle Image -->
          <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
            <label class="form-label">{{ __('index.vehicle_icon') }}</label>
            <!-- Add d-none class if no image is available -->
            <div id="image_preview" class="mt-2 {{ isset($vehicle->vehicle_icon) ? '' : 'd-none' }}">
              <img id="preview_img" data-id="{{ $vehicle->id }}" src="{{ isset($vehicle->vehicle_icon) ? url('storage-bucket?path=' . $vehicle->vehicle_icon) : '#' }}" alt="Image Preview"
                class="img-fluid" style="max-width: 125px; height: 125px;" />
            </div>
          </div>


        </div>

        <!-- Active Status -->
        <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="form-check">
            <input class="form-check-input" name="is_active" id="is_active" onclick="return false;" type="checkbox" value="1" {{ $vehicle->is_active == 1 ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
              {{ __('index.active') }}
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}"
    var csrfToken = "{{ csrf_token() }}"
  </script>
    <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
  
  <script src="{{ asset('site-plugins/vehicle/show.js?v=' . time()) }}"></script>
@endpush
