@extends('layout.master', ['pageTitle' => __('index.refund')])
@push('styles')
<link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    tr.group,
    tr.group:hover {
        background-color: #ddd !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: transparent !important;
    }
	.error {
      color: red;
    }
</style>
@endpush
@section('content')
<div class="d-flex mb-4 mt-1" id="sel-projectId">
    <span class="fa-stack me-2 ms-n1">
        <i class="fas fa-circle fa-stack-2x text-300"></i>
        <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
        <div class="">
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.refund')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.refund')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    
</div>
<div class="row gx-3">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body row">
				<!-- Customer Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.name')}}</label>
					<select class="form-control select2 custom-select" id="customer_name" name="customer_name">
					</select>
				</div>
				
				<!-- Driver Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.driver_name')}}</label>
					<select class="form-control select2 custom-select" id="driver_name" name="driver_name">
					</select>
				</div>

				<!-- Vehicle Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.vehicle')}}</label>
					<select class="form-control select2 custom-select" id="vehicle" name="vehicle">
					</select>
				</div>

                <!-- coupon Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2 d-none">
					<label class="form-label">{{__('index.coupon_code')}}</label>
					<select class="form-control select2 custom-select" id="coupon_code" name="coupon_code">
					</select>
				</div>
				
				 <!-- goods Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2 d-none">
					<label class="form-label">{{__('index.goods_type')}}</label>
					<select class="form-control select2 custom-select" id="goods_type" name="goods_type">
					</select>
				</div>
				
				 <!-- unique id -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.unique_id')}}</label>
					<select class="form-control select2 custom-select" id="unique_id" name="unique_id">
					</select>
				</div>				
				
				 <div class="col-12 text-end  mt-3">
					<!-- Buttons for Search and Clear -->
					<button class="btn btn-sm btn-danger" id="reset_filter">{{__('index.reset')}}</button>
					<button class="btn btn-sm btn-primary" id="search_filter">{{__('index.search')}}</button>
					<!-- Excel and PDF Buttons -->
					@if(Auth::guard('admin')->user()->can('Cancel Trip.Export', 'admin'))
					  <button type="button" id="btnExcelDownload" class="btn btn-sm btn-success me-1">{{__('index.export_to_csv')}}</button>
					  <button type="button" id="btnPdfDownload" class="btn btn-sm btn-info">{{__('index.export_to_pdf')}}</button>
				    @endif
				  </div>
			</div>

        </div>
    </div>
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive scrollbar">
                    <table id="dt-refund-trip" class="table table-hover">
                        <thead>
                            <tr>                               
                                <th scope="col">{{__('index.unique_id')}}</th>
								<th scope="col">{{__('index.date')}}</th>
                                <th scope="col">{{__('index.vehicle_name')}}</th>
                                <th scope="col">{{__('index.customer_name')}}</th>
								<th scope="col">{{__('index.driver_name')}}</th>
                                <th scope="col">{{__('index.total_amount')}}</th>
                                <th scope="col">{{__('index.refund_amount')}}</th>								
								<th scope="col">{{__('index.status')}}</th>					
                                
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="{{ asset('site-plugins/refund/index.js?v=' . time()) }}"></script>
@endpush