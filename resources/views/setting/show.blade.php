@php
  $pageTitle = __('index.view_setting');
@endphp
@extends('layout.master')
@push('styles')
  <style>
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_setting') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
			@if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
			<li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
			@endif
			@if(Auth::guard('admin')->user()->can('Setting.List', 'admin'))
			<li class="breadcrumb-item"><a href="{{ url('/setting') }}" class="text-decoration-none text-dark">{{__('index.setting')}}</a></li>
			@endif
			<li class="breadcrumb-item active" aria-current="page">{{__('index.view')}}</li>
        </ol>
      </nav>
    </div>
	 @if(Auth::guard('admin')->user()->can('Setting.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('setting') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
	@endif
  </div>
  <!-- Add Goods Type -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form class="" id="form-edit-goods-type" method="POST" action="{{ url('setting/' . $setting->id) }}">
        @csrf
        @method('PUT') <!-- This ensures a PUT request is made -->
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.view_setting') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.setting_name') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-2" name="setting_name" id="setting_name" value="{{ $setting->setting_name}}" readonly />
               @error('setting_name')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.setting_value') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-2" name="setting_value" id="setting_value" value="{{ $setting->setting_value}}" />
               @error('setting_value')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
          </div>
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $setting->is_active == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  {{ __('index.active') }}
                </label>
              </div>
			   @if($setting->id == 1 || $setting->id == 2)
			  <div class="form-check">
                <input class="form-check-input" name="is_update_compulsory" id="is_update_compulsory" type="checkbox" value="1" {{ $setting->is_update_compulsory == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="is_update_compulsory">
                  {{ __('index.is_update_compulsory') }}
                </label>
              </div>
			  @endif
            </div>
          </div>
        </div>
      </form>

    </div>
  </div>
@endsection
@push('scripts')
 
@endpush
