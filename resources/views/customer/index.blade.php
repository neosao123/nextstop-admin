@extends('layout.master', ['pageTitle' => __('index.customers')])
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
                <h5 class="mb-0 text-primary position-relative"><span
                        class="bg-200 dark__bg-1100 pe-3">{{ __('index.customers') }}</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                    class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ __('index.customers') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

    </div>
    <div class="row gx-3">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body row">
                    <!-- User Name Field -->
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                        <label class="form-label">{{ __('index.name') }}</label>
                        <select class="form-control select2 custom-select" id="customer_name" name="customer_name">
                        </select>
                    </div>

                    <!-- Email Field -->
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                        <label class="form-label">{{ __('index.email') }}</label>
                        <select class="form-control select2 custom-select" id="email" name="email">
                        </select>
                    </div>

                    <!-- Phone Field -->
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                        <label class="form-label">{{ __('index.phone_number') }}</label>
                        <select class="form-control select2 custom-select" id="phone_number" name="phone_number">
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                        <label class="form-label">{{ __('index.account_status') }}</label>
                        <select class="cutsom-select form-select" name="account_status" id="account_status">
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <div class="col-lg-12 text-end mt-3">
                        <!-- Buttons for Search and Clear -->
                        <button class="btn btn-sm btn-danger" id="reset_filter">{{ __('index.reset') }}</button>
                        <button class="btn btn-sm btn-primary" id="search_filter">{{ __('index.search') }}</button>
                        <!-- Excel and PDF Buttons -->
                        @if (Auth::guard('admin')->user()->can('Customer.Export', 'admin'))
                            <button type="button" id="btnExcelDownload"
                                class="btn btn-sm btn-success me-1">{{ __('index.export_to_csv') }}</button>
                            <button type="button" id="btnPdfDownload"
                                class="btn btn-sm btn-info">{{ __('index.export_to_pdf') }}</button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table id="dt-customer" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    @if (Auth::guard('admin')->user()->canany(['Customer.Edit', 'Customer.Delete', 'Customer.View', 'Customer.Export']))
                                        <th class="text-start" scope="col">{{ __('index.action') }}</th>
                                    @endif
                                    <th scope="col">{{ __('index.name') }}</th>
                                    <th scope="col">{{ __('index.email') }}</th>
                                    <th scope="col">{{ __('index.phone_number') }}</th>
                                    <th scope="col">{{ __('index.wallet') }}</th>
                                    <th scope="col">{{ __('index.referral_wallet') }}</th>
                                    <th scope="col">{{ __('index.referral_code') }}</th>
                                    <th scope="col">{{ __('index.refer_by') }}</th>
                                    <th scope="col">{{ __('index.status') }}</th>
                                    <th scope="col">{{ __('index.block') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 pb-2">
                            <label class="form-label">{{ __('index.wallet_balance') }}</label>
                            <input type="hidden" class="form-control mb-2" name="customer_id" id="customer_id" />
                            <input type="text" class="form-control mb-2" name="wallet_balance" id="wallet_balance"
                                readonly />
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 pb-2">
                            <label class="form-label">{{ __('index.amount') }}</label>
                            <input type="number" class="form-control mb-2" name="amount" id="amount" />
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 pb-2">
                            <label class="form-label">{{ __('index.wallet_operation') }}</label>
                            <select class="form-select" id="operation" name="operation">
                                <option selected disabled>Select Action</option>
                                <option value="deposit">Deposit</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 pb-2">
                            <label class="form-label">{{ __('index.reason') }}</label>
                            <textarea class="form-control" name="reason" id="reason"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="wallet_submit">Submit</button>
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
    <script src="{{ asset('site-plugins/customer/index.js?v=' . time()) }}"></script>
@endpush
