@php
  $pageTitle = __('index.setting');
@endphp
@extends('layout.master')
@push('styles')
 <link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
@endpush
@section('content')
  <div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
      <i class="fas fa-circle fa-stack-2x text-300"></i>
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.setting') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          @if (Auth::guard('admin')->user()->can('Dashboard.View'))         
		   <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
	      @endif
		  
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.setting') }}</li>
        </ol>
      </nav>
    </div>
  
  </div>
  <div class="row gx-3">
    <!-- List Vehicle-Type -->
    @if (Auth::guard('admin')->user()->can('Setting.List'))
      <div class="col-lg-12">
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h5 class="mb-0">{{ __('index.list') }}</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive scrollbar">
              <table id="dt-setting" class="table table-hover">
                <thead>
                  <tr>
				    @if (Auth::guard('admin')->user()->canany(['Setting.Edit', 'Setting.Delete', 'Setting.View']))
                      <th class="text-start" scope="col">{{ __('index.action') }}</th>
                    @endif
                    <th scope="col">{{ __('index.setting_name') }}</th>
					<th scope="col">{{ __('index.setting_value') }}</th>
                    <th scope="col">{{ __('index.created_at') }}</th>
                    <th scope="col">{{ __('index.status') }}</th>
                    
                  </tr>
                </thead>
                <tbody></tbody>
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
 <script src="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('site-plugins/setting/index.js?v=' . time()) }}"></script>
@endpush
