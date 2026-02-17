@php
    $pageTitle = __('index.verified_driver');
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
    <link href="{{ asset('assets/vendors/select2/select2.min.css') }}" rel="stylesheet" />
@endpush
@section('content')
    <div class="d-flex mb-4 mt-1">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <h5 class="mb-0 text-primary position-relative"><span
                    class="bg-200 dark__bg-1100 pe-3">{{ __('index.verified_driver') }}</span><span
                    class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                            class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('index.verified_driver') }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row gx-3">
        <!-- List Driver -->
        @if (Auth::guard('admin')->user()->can('Verified-Driver.List'))
            <div class="col-lg-12">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ __('index.filter') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                <label class="form-label">{{ __('index.name') }}</label>
                                <input type="text" class="form-control" name="search_name" id="search_name" />
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                <label class="form-label">{{ __('index.driver_vehicle') }}</label>
                                <select class="cutsom-select select-2 form-control" name="search_driver_vehicle"
                                    id="search_driver_vehicle">
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                <label class="form-label">{{ __('index.driver_serviceable_location') }}</label>
                                <select class="cutsom-select select-2 form-control"
                                    name="search_driver_serviceable_location" id="search_driver_serviceable_location">
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                <label class="form-label">{{ __('index.account_status') }}</label>
                                <select class="cutsom-select select-2 form-select" name="search_account_status"
                                    id="search_account_status">
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="delete">Delete</option>
                                </select>
                            </div>
                            <div class="col-12 text-end  mt-3">
                                <button class="btn btn-sm btn-danger" id="reset_filter">{{ __('index.reset') }}</button>
                                <button class="btn btn-sm btn-primary" id="search_filter">{{ __('index.search') }}</button>
                                <button id="exportExcelBtn"
                                    class="btn btn-sm  btn-success">{{ __('index.export_to_csv') }}</button>
                                <button id="exportPdfBtn"
                                    class="btn btn-sm  btn-info">{{ __('index.export_to_pdf') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ __('index.list') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive scrollbar">
                            <table id="dt-verified-driver" class="table table-hover">
                                <thead>
                                    <tr>
                                        @if (Auth::guard('admin')->user()->canany(['Verified-Driver.View-Edit', 'Verified-Driver.Delete', 'Verified-Driver.Block']))
                                            <th class="text-start" scope="col">{{ __('index.action') }}</th>
                                        @endif
                                        <th scope="col">{{ __('index.driver_name') }}</th> 
                                        <th scope="col">{{ __('index.driver_vehicle') }}</th>
                                        <th scope="col">{{ __('index.driver_serviceable_location') }}</th>
                                        <th scope="col">{{ __('index.driver_phone') }}</th>
                                        <th scope="col">{{ __('index.driver_email') }}</th>
                                        <th scope="col">{{ __('index.wallet') }}</th>
                                        <th scope="col">{{ __('index.driver_document_verification_status') }}</th>
                                        <th scope="col">{{ __('index.driver_vehicle_verification_status') }}</th>
                                        <th scope="col">{{ __('index.driver_training_video_verification_status') }}</th>
                                        <th scope="col">{{ __('index.admin_verification_status') }}</th>
                                        <th scope="col">{{ __('index.account_status') }}</th>
                                        <th scope="col">{{ __('index.created_at') }}</th>
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
    <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>

    <script src="{{ asset('site-plugins/verified-driver/index.js?v=' . time()) }}"></script>
@endpush
