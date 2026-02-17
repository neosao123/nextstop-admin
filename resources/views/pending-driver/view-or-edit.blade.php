@php
  $pageTitle = __('index.view_or_edit_pending_driver');
@endphp
@extends('layout.master')
@push('styles')
  <link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
  <style>
    label.error {
      color: #e63757;
    }

    tr.group,
    tr.group:hover {
      background-color: #ddd !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      background-color: transparent !important;
    }



    #adhar_card_drop_zone,
    #pan_copy_drop_zone,
    #gst_certificate_drop_zone,
    #cheque_copy_drop_zone,
    #vrf_drop_zone,
    #other_document_drop_zone {
      border: 2px dashed #A9A9A9;
      border-radius: 5px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      color: #A9A9A9;
      margin-bottom: 15px;
      background-color: #f5f5f5;
    }

    #adhar_card_drop_zone.hover,
    #pan_copy_drop_zone.hover,
    #gst_certificate_drop_zone.hover,
    #cheque_copy_drop_zone.hover,
    #vrf_drop_zone.hover,
    #other_document_drop_zone.hover {
      background-color: #f1f1f1;
    }

    #adhar_card_copy,
    #pan_copy,
    #gst_certificate,
    #cheque_copy,
    #vrf,
    #any_other_document {
      display: none;
    }

    .dropzone {
      min-height: auto !important;
    }

    #adhar-card-preview,
    #pan-copy-preview,
    #gst-certificate-preview,
    #cheque-copy-preview,
    #vrf-copy-preview,
    #other-document-preview {
      position: relative;
      display: inline-block;
    }

    .remove_btn_div {
      position: absolute;
      top: 5px;
      right: 5px;
      background-color: rgba(255, 0, 0, 0.8);
      border-radius: 50%;
      width: 25px;
      height: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1;
    }

    #remove_adhar_image_btn,
    #remove_pan_image_btn,
    #remove_gst_image_btn,
    #remove_cheque_image_btn,
    #remove_vrf_image_btn,
    #remove_other_document_image_btn {
      background: transparent;
      border: none;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }

    #remove_adhar_image_btn:focus,
    #remove_pan_image_btn:focus,
    #remove_gst_image_btn:focus,
    #remove_cheque_image_btn:focus,
    #remove_vrf_image_btn:focus,
    #remove_other_document_image_btn:focus {
      outline: none;
      box-shadow: none;
    }

    #vrf_file_name,
    #other_document_name {
      padding-right: 20px !important;
      padding-top: 30px !important;
    }

    #wizard-controller {
      height: 410px;
      overflow-y: auto;
      scrollbar-width: thin;
    }

    .line {
      height: .5px;
      background-color: #ccc;
      width: 100%;
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_or_edit_pending_driver') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/driver/pending') }}" class="text-decoration-none text-dark">{{ __('index.pending_driver') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.view_or_edit_pending_driver') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('driver/pending') }}">
        <span class="px-2">Back</span>
      </a>
    </div>
  </div>

  <!-- Edit driver -->
  <div class="row">
    <div class="col-lg-12 col-md-12 col-xl-12 h-100">
      <div class="card theme-wizard h-100 mb-5">
        <div class="card-header bg-light p-2">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="personal-verification-tab" data-bs-toggle="tab" href="#tab-personal-verification" role="tab" aria-controls="tab-home" aria-selected="true">
                {{ __('index.personal_details_document_verification') }}
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="vehicle-verification-tab" data-bs-toggle="tab" href="#tab-vehicle-verification" role="tab" aria-controls="tab-profile" aria-selected="false">
                {{ __('index.vehicle_details_verification') }}
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="training-video-verification-tab" data-bs-toggle="tab" href="#tab-video-verification" role="tab" aria-controls="tab-video" aria-selected="false">
                {{ __('index.training_video_verification') }}
              </a>
            </li>
			<li class="nav-item">
              <a class="nav-link" id="admin-verification-tab" data-bs-toggle="tab" href="#tab-admin-verification" role="tab" aria-controls="tab-video" aria-selected="false">
                {{ __('index.final_verification_status') }}
              </a>
            </li>
          </ul>
        </div>

        <div class="tab-content p-0" id="myTabContent">
          <div class="tab-pane fade show active" id="tab-personal-verification" role="tabpanel" aria-labelledby="personal-verification-tab">
            @include('pending-driver.verification-forms.personal-verification')
          </div>
          <div class="tab-pane fade" id="tab-vehicle-verification" role="tabpanel" aria-labelledby="vehicle-verification-tab">
            @include('pending-driver.verification-forms.vehicle-verification')
          </div>
          <div class="tab-pane fade" id="tab-video-verification" role="tabpanel" aria-labelledby="video-verification-tab">
            @include('pending-driver.verification-forms.training-video-verification')
          </div>
		   <div class="tab-pane fade" id="tab-admin-verification" role="tabpanel" aria-labelledby="admin-verification-tab">
            @include('pending-driver.verification-forms.admin-final-verification')
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}";
    var driverId = "{{ $driver->id }}";
    var csrfToken = "{{ csrf_token() }}";
  </script>
  <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('site-plugins/pending-driver/edit.js?v=' . time()) }}"></script>
@endpush
