@extends('layout.master', ['pageTitle' => __('index.rating')])
@push('styles')
<link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.rating')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Customer.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/customers') }}" class="text-decoration-none text-dark">{{__('index.customer')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.rating')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('Customer.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('customers') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>
<div class="row gx-3">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body row">
				<input type="hidden" id="customer" value="{{$customerId}}">
				<!-- Driver Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.driver')}}</label>
					<select class="form-control select2 custom-select" id="driver" name="driver">
					</select>
				</div>

                <!-- Trip Field-->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.trip')}}</label>
					<select class="form-control select2 custom-select" id="trip" name="trip">
					</select>
				</div>
				
				
				 <div class="col-12 text-end  mt-3">
					<!-- Buttons for Search and Clear -->
					<button class="btn btn-sm btn-danger" id="reset_filter">{{__('index.reset')}}</button>
					<button class="btn btn-sm btn-primary" id="search_filter">{{__('index.search')}}</button>
					
				  </div>
			</div>

        </div>
    </div>
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive scrollbar">
                    <table id="dt-rating" class="table table-hover">
                        <thead>
                            <tr>                               
                                <th scope="col">{{__('index.name')}}</th>
								<th scope="col">{{__('index.driver')}}</th>
                                <th scope="col">{{__('index.trip')}}</th>                                
                                <th scope="col">{{__('index.rating_value')}}</th>
								<th scope="col">{{__('index.rating_description')}}</th>
                                <th scope="col">{{__('index.date')}}</th>
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
  <script src="{{ asset('site-plugins/customer/rating.js?v=' . time()) }}"></script>
@endpush