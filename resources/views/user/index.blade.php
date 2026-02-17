@extends('layout.master', ['pageTitle' => __('index.users')])
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.users')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.users')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('User.Create', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('users/create' ) }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.add_user')}}</a>
    </div>
    @endif
</div>
<div class="row gx-3">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body row">
				<!-- User Name Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.name')}}</label>
					<select class="form-control select2 custom-select" id="user_name" name="user_name">
					</select>
				</div>

				<!-- Role Field -->
				<div class="col-lg-3 col-md-6 col-sm-12 pb-2">
					<label class="form-label">{{__('index.role')}}</label>
					<select class="form-control select2 custom-select" id="role_id" name="role_id">
					</select>
				</div>

				
				
				 <div class="col-12 text-end  mt-3">
					<!-- Buttons for Search and Clear -->
					<button class="btn btn-sm btn-danger" id="reset_filter">{{__('index.reset')}}</button>
					<button class="btn btn-sm btn-primary" id="search_filter">{{__('index.search')}}</button>
					<!-- Excel and PDF Buttons -->
					@if(Auth::guard('admin')->user()->can('User.Export', 'admin'))
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
                    <table id="dt-users" class="table table-hover">
                        <thead>
                            <tr>  
                                @if(Auth::guard('admin')->user()->canany(['User.Edit','User.Delete','User.View','User.Permissions','User.Export']))
                                  <th scope="col">{{__('index.action')}}</th>
                                @endif							
                                <th scope="col">{{__('index.name')}}</th>
                                <th scope="col">{{__('index.role')}}</th>
                                <th scope="col">{{__('index.email')}}</th>
                                <th scope="col">{{__('index.phone_number')}}</th>
                                <th scope="col">{{__('index.status')}}</th>
								<th scope="col">{{__('index.block')}}</th>
                               
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
  <script src="{{ asset('site-plugins/user/index.js?v=' . time()) }}"></script>
@endpush