@php
  $pageTitle = __('index.edit_goods_type');
@endphp
@extends('layout.master')
@push('styles')
  <style>

  </style>
@endpush
@section('content')
  <div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
      <i class="fas fa-circle fa-stack-2x text-300"></i>
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_goods_type') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          @endif
          @if (Auth::guard('admin')->user()->can('Goods-Type.List', 'admin'))
            <li class="breadcrumb-item"><a href="{{ url('/goods-type') }}" class="text-decoration-none text-dark">{{ __('index.goods_type') }}</a></li>
          @endif
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.edit') }}</li>
        </ol>
      </nav>
    </div>
    @if (Auth::guard('admin')->user()->can('Goods-Type.List', 'admin'))
      <div class="col-auto ms-2 align-items-center">
        <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('goods-type') }}">
          <span class="px-2">{{ __('index.back') }}</span>
        </a>
      </div>
    @endif
  </div>
  <!-- Add Goods Type -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form class="" id="form-edit-goods-type" method="POST" action="{{ url('goods-type/' . $goods_type->id) }}">
        @csrf
        @method('PUT') <!-- This ensures a PUT request is made -->
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.edit_goods_type') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.goods_name') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-2" name="goods_name" id="goods_name" value="{{ $goods_type->goods_name }}" />
              @error('goods_name')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $goods_type->is_active == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  {{ __('index.active') }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
          <button class="btn btn-primary" id="cost-type-submit" type="submit">{{ __('index.update') }}</button>
        </div>
      </form>

    </div>
  </div>
@endsection
@push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"
    integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="{{ asset('site-plugins/goods-type/edit.js?v=' . time()) }}"></script>
@endpush
