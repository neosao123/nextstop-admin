@php
  $pageTitle = __('index.notification');
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.notification') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.notification') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('notification') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
  </div>
  <!-- Add notification -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form id="form-add-notification" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-header bg-light">
          <h5 class="mb-0">{{ __('index.notification') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            
			 <!-- Title -->
            <div class="col-lg-6 col-md-6 col-sm-6 mb-3">
              <label class="form-label">{{ __('index.notification_title') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control " name="notification_title" id="notification_title" value="{{ old('notification_title') }}" />
            </div>
			
			<!-- Type -->
            <div class="col-lg-6 col-md-6 col-sm-6 mb-3">
              <label class="form-label">{{ __('index.type') }} <span class="text-danger">*</span></label>
              <select class="form-select custom-select" id="type" name="type">
					<option value="">Choose...</option>
					<option value="customer">Customer</option>
					<option value="driver">Partner</option>
			  </select>
            </div>
			
			
			<!-- Customer Name -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.customer_name') }} </label>
              <select class="custom-select form-control select2 " name="customer_name[]" id="customer_name" multiple>
              
              </select>
            </div> 

            <!-- Driver Name -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.driver_name') }}</label>
              <select class="custom-select form-control select2 " name="driver_name[]" id="driver_name" multiple>
              
              </select>
            </div> 			
            

            <!-- Message -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.message') }} <span class="text-danger">*</span></label>
              <textarea class="form-control" name="message" id="message">{{ old('message') }}</textarea>
            </div>

            <!-- Image -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3 position-relative">
              <label class="mb-0 form-label">{{ __('index.image') }}</label>
              <p class="mb-0">
                <small class="form-label">
                  {{ __('index.accept_format') }}
                  {{ implode(', ', [__('index.jpg'), __('index.jpeg'), __('index.png')]) }}
                </small>
              </p>
              
              <input type="file" class="form-control" name="image" id="image" accept=".jpg, .jpeg, .png" />
              <div id="image_preview" class="mt-2" style="display: none;">
                <img id="preview_img" src="#" alt="Image Preview" class="img-fluid" style="width: 125px; height: 125px;" />
                <button type="button" id="remove_image" class="btn btn-danger"
                  style="position: absolute; top: 5px; right: 5px; border-radius: 50%; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center;">
                  &times; <!-- This will display an 'X' -->
                </button>
              </div>
              <div id="error_message" class="text-danger mt-2" style="display: none;"></div>
            </div>

          </div>

          <!-- Active Status -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="form-check">
              <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" checked>
              <label class="form-check-label" for="is_active">
                {{ __('index.active') }}
              </label>
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
          <button class="btn btn-primary" id="notification-save" type="button">{{ __('index.save') }}</button>
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
  <script src="{{ asset('site-plugins/notification/index.js?v=' . time()) }}"></script>
@endpush
