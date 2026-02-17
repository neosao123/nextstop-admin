@extends('layout.master', ['pageTitle' => __('index.role')])
@push('styles')
<link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<style>

.invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 75%;
    color: #e63757;
}

.was-invalid .invalid-feedback {
    display: block;
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
        <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.role')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{__('index.role')}}</li>
            </ol>
        </nav>
    </div>
</div>
<div class="row gx-3">

    <!-- Add|Edit Role -->
    @if(Auth::guard('admin')->user()->can('Role.Create-Update', 'admin'))
    <div class="col-lg-4">
        <div class="card mb-3">
            <form class="" id="form-role" data-parsley-validate="">
                @csrf
                <div class="card-header bg-light">
                    <h5 class="mb-0" id="form-title">{{__('index.new_role')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <input type="number" id="id" class="d-none form-control" name="id" value="" />
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">{{__('index.name_of_role')}}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" value="" required />
                        </div>
                    </div>
                </div>
                <div class="card-footer float-end">
                    <button class="btn btn-primary" id="btn-submit" type="button">{{__('index.submit')}}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    
	<!-- List Role -->
    @if(Auth::guard('admin')->user()->can('Role.List', 'admin'))

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{__('index.role_list')}}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive scrollbar">
                    <table id="dt-role" class="table table-hover table-bordered table-stripped">
                        <thead>
                            <tr>
                                <th scope="col">{{__('index.role')}}</th>
                               @if(Auth::guard('admin')->user()->canany(['Role.Create-Update', 'Role.Delete']))
                                <th class="text-end" scope="col">{{__('index.role_action')}}</th>
                               @endif
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

<script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('site-plugins/role/index.js?v=' . time()) }}"></script>

@endpush