@php
  $pageTitle = __('index.driver_document_details');
@endphp
@extends('layout.master')
@push('styles')
  <style>
    td {
      white-space: nowrap;
    }
  </style>
 <link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />

@endpush
@section('content')
  <div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
      <i class="fas fa-circle fa-stack-2x text-300"></i>
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.driver_document_details') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.driver_document_details') }}</li>
        </ol>
      </nav>
    </div>
  </div>
  <div class="row gx-3">
    <!-- List driver -->
    {{-- @if (Auth::guard('admin')->user()->can('driver.List')) --}}
    <div class="col-lg-12">
      <div class="card mb-3">
        <div class="card-header bg-light">
          <h5 class="mb-0">{{ __('index.list') }}</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive scrollbar">
            <table id="dt-driver-document-details" class="table table-hover">
              <thead>
                <tr>
                  <th scope="col">{{ __('index.porter_first_name') }}</th>
                  <th scope="col">{{ __('index.porter_last_name') }}</th>
                  <th scope="col">{{ __('index.document_type') }}</th>
                  <th scope="col">{{ __('index.document_number') }}</th>
                  <th scope="col">{{ __('index.document_verification_status') }}</th>
                  <th scope="col">{{ __('index.status') }}</th>
                  <th scope="col">{{ __('index.created_at') }}</th>
                  {{-- @if (Auth::guard('admin')->user()->canany(['driver.Edit', 'driver.Delete', 'driver.View'])) --}}
                  <th class="text-end" scope="col">{{ __('index.action') }}</th>
                  {{-- @endif --}}
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    {{-- @endif --}}
  </div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}";
  </script>
 <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('site-plugins/driver-document-details/index.js?v=' . time()) }}"></script>
@endpush
