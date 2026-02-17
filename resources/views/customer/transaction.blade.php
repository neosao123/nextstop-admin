@extends('layout.master', ['pageTitle' => __('index.wallet_transaction')])
@push('styles')
<link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
 <link rel="stylesheet" href="{{ asset('assets/css/flatpickr.min.css') }}">
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.wallet_transaction')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('Customer.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/customers') }}" class="text-decoration-none text-dark">{{__('index.customer')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.wallet_transaction')}}</li>
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
				
                <!-- Trip Field-->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.trip')}}</label>
					<select class="form-control select2 custom-select" id="trip" name="trip">
					</select>
				</div>
				
                <div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label for="type" class="form-label">{{__('index.type')}}</label>
					<select class="form-select" id="type" name="type">
						<option selected disabled>Select Type</option>
						<option value="deposit">Deposit</option>					
						<option value="deduction">Deduction</option>
						<option value="refund">Refund</option>
					</select>
				</div>

				<!-- Start Date -->
				<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
				  <label class="form-label">{{ __('index.from_date') }} </label>
				  <input type="text" class="form-control " name="from_date" id="from_date" placeholder="dd/mm/yyyy" />
				</div>
				
				<!-- End Date -->
				<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
				  <label class="form-label">{{ __('index.to_date') }} </label>
				  <input type="text" class="form-control " name="to_date" id="to_date" placeholder="dd/mm/yyyy" />
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
                    <table id="dt-transaction" class="table table-hover">
                        <thead>
                            <tr>   
                                
                                <th class="text-end" scope="col">{{__('index.action')}}</th>
                                                          
                                <th scope="col">{{__('index.name')}}</th>								
                                <th scope="col">{{__('index.trip')}}</th>                                
                                <th scope="col">{{__('index.payment_order_id')}}</th>
								<th scope="col">{{__('index.amount')}}</th>
								<th scope="col">{{__('index.type')}}</th>
								<th scope="col">{{__('index.message')}}</th>
								<th scope="col">{{__('index.status')}}</th>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="{{ asset('site-plugins/customer/transaction.js?v=' . time()) }}"></script>
@endpush