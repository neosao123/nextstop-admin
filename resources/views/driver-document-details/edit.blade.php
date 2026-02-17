@php
  $pageTitle = __('index.edit_driver');
@endphp
@extends('layout.master')
@push('styles')
  <style>
    label.error {
      color: #e63757;
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_driver') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.edit_driver') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('driver') }}">
        <span class="px-2">Back</span>
      </a>
    </div>
  </div>
  {{-- @if (Auth::guard('tenant')->user()->can('Cost-Type.Create-Update', 'tenant')) --}}
  <!-- Add Vehicle Type -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form class="" id="form-edit-driver" method="POST" action="{{ url('driver/' . $driver->id) }}">
        @csrf
        @method('PUT') <!-- This ensures a PUT request is made -->
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.edit_driver') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.porter_first_name') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-2" name="porter_first_name" id="porter_first_name" value="{{ $driver->porter_first_name }}" />
              @error('porter_first_name')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.porter_last_name') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-2" name="porter_last_name" id="porter_last_name" value="{{ $driver->porter_last_name }}" />
              @error('porter_last_name')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.porter_email') }} </label>
              <input type="text" class="form-control mb-2" name="porter_email" id="porter_email" value="{{ old('porter_email', $driver->porter_email) }}" />
              @error('porter_email')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.porter_phone') }} </label>
              <input type="text" class="form-control mb-2" name="porter_phone" id="porter_phone" value="{{ $driver->porter_phone }}" readonly />
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
          <button class="btn btn-primary" id="driver-update" type="submit">{{ __('index.update') }}</button>
        </div>
      </form>

    </div>
  </div>
  {{-- @endif --}}
@endsection
@push('scripts')
 <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('site-plugins/driver/edit.js?v=' . time()) }}"></script>
@endpush
