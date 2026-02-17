@extends('layout.master', ['pageTitle' => __('index.enquiry')])
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.enquiry')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.enquiry')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    
</div>
<div class="row gx-3">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive scrollbar">
                    <table id="dt-enquiry" class="table table-hover">
                        <thead>
                            <tr> 						
                                <th scope="col">{{__('index.name')}}</th>
                                <th scope="col">{{__('index.email')}}</th>
                                <th scope="col">{{__('index.phone_number')}}</th>
								<th scope="col">{{__('index.subject')}}</th>
								<th scope="col">{{__('index.message')}}</th>
								<th scope="col">{{__('index.created_at')}}</th>
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
  <script src="{{ asset('site-plugins/enquiry/index.js?v=' . time()) }}"></script>
@endpush