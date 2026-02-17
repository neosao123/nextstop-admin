@php
  $pageTitle = __('index.vehicle');
@endphp
@extends('layout.master')
@push('styles')
 <link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
  <style>
    td {
      white-space: nowrap;
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.vehicle') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.vehicle') }}</li>
        </ol>
      </nav>
    </div>
    @if (Auth::guard('admin')->user()->can('Vehicle.Create'))
      <div class="col-auto ms-2 align-items-center">
        <a class="btn btn-falcon-primary btn-sm me-1 mb-1" href="{{ url('vehicle/create') }}">
          <span class="px-2">{{ __('index.add_vehicle') }}</span>
        </a>
      </div>
    @endif
  </div>
  <div class="row gx-3">
    <!-- List Vehicle -->
    @if (Auth::guard('admin')->user()->can('Vehicle.List'))
      <div class="col-lg-12">
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h5 class="mb-0">{{ __('index.filter') }}</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                <label class="form-label">{{ __('index.vehicle_type') }}</label>
                <select class="cutsom-select select-2 form-control" name="search_vehicle_type" id="search_vehicle_type">
                </select>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                <label class="form-label">{{ __('index.vehicle_max_load_capacity') }}</label>
                <input type="number" class="form-control" name="search_vehicle_max_load_capacity" id="search_vehicle_max_load_capacity" step="0.01" />
              </div>
              <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                <label class="form-label">{{ __('index.vehicle_per_km_delivery_charge') }}</label>
                <input type="number" class="form-control" name="search_vehicle_per_km_delivery_charge" id="search_vehicle_per_km_delivery_charge" step="0.01" />
              </div>
              <div class="col-12 text-md-end text-start  mt-3">
                <button class="btn btn-sm btn-danger mb-2" id="reset_filter">{{ __('index.reset') }}</button>
                <button class="btn btn-sm btn-primary mb-2" id="search_filter">{{ __('index.search') }}</button>
                @if (Auth::guard('admin')->user()->can('Vehicle.Export'))
                  <button id="exportExcelBtn" class="btn btn-sm  btn-success mb-2">{{ __('index.export_to_csv') }}</button>
                  <button id="exportPdfBtn" class="btn btn-sm  btn-info mb-2">{{ __('index.export_to_pdf') }}</button>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="card mb-3">
          <div class="card-header bg-light d-flex">
            <div class="col">
              <h5 class="mb-0">{{ __('index.list') }}</h5>
            </div>
            @if (Auth::guard('admin')->user()->can('Vehicle.Import'))
              <div class="col-auto ms-2 align-items-center">
                <a class="btn btn-falcon-danger  btn-sm me-1 mb-1" href="{{ asset('assets/templates/Template - Vehicle.xlsx') }}" download="Template - Vehicle.xlsx">
                  <span class="px-2">{{ __('index.get_excel_template') }}</span>
                </a>
                <a class="btn btn-falcon-success btn-sm me-1 mb-1" href="{{ url('vehicle/import/excel') }}">
                  <span class="px-2">{{ __('index.import_excel') }}</span>
                </a>
              </div>
            @endif
          </div>
          <div class="card-body">
            <div class="table-responsive scrollbar">
              <table id="dt-vehicle" class="table table-hover">
                <thead>
                  <tr>
				    @if (Auth::guard('admin')->user()->canany(['Vehicle.Edit', 'Vehicle.Delete', 'Vehicle.View']))
                      <th class="text-start" scope="col">{{ __('index.action') }}</th>
                    @endif
                    <th scope="col">{{ __('index.vehicle_name') }}</th>
                    <th scope="col">{{ __('index.vehicle_type') }}</th>
                    <th scope="col">{{ __('index.vehicle_dimensions') }}</th>
                    <th scope="col">{{ __('index.vehicle_max_load_capacity') }}</th>
					<th scope="col">{{ __('index.vehicle_fixed_km') }}</th>
					<th scope="col">{{ __('index.vehicle_fixed_km_delivery_charge') }}</th>
                    <th scope="col">{{ __('index.vehicle_per_km_delivery_charge') }}</th>
                    <th scope="col">{{ __('index.vehicle_per_km_extra_delivery_charge') }}</th>
                    <th scope="col">{{ __('index.status') }}</th>
                    <th scope="col">{{ __('index.created_at') }}</th>
                    
                  </tr>
                </thead>
                <tbody>
                  <!-- Table rows will be dynamically inserted here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    @endif
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
  <script src="{{ asset('site-plugins/vehicle/index.js?v=' . time()) }}"></script>
@endpush
