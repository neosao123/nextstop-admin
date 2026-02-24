@extends('layout.master', ['pageTitle' => 'Admin Commission Report'])
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

        .dataTables_wrapper .dataTables_length select {
            padding: .25rem 2rem .25rem .5rem !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right .75rem center;
            background-size: 16px 12px;
            appearance: none;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            display: inline-block;
            width: auto;
        }
    </style>
@endpush
@section('content')
    <div class="d-flex mb-4 mt-1" id="sel-projectId">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fas fa-file-alt" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <div class="">
                <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">Admin Commission
                        Report</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                class="text-decoration-none text-dark">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Admin Commission Report</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="row gx-3">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body row">
                    <!-- Driver Name Field -->
                    <div class="col-lg-3 col-md-6 col-sm-12 pb-2">
                        <label class="form-label">Driver Name</label>
                        <select class="form-control select2 custom-select" id="driver" name="driver">
                        </select>
                    </div>

                    <!-- From Date -->
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <label class="form-label">From Date</label>
                        <input type="text" class="form-control" name="from_date" id="from_date"
                            placeholder="dd-mm-yyyy" />
                    </div>

                    <!-- To Date -->
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <label class="form-label">To Date</label>
                        <input type="text" class="form-control" name="to_date" id="to_date" placeholder="dd-mm-yyyy" />
                    </div>

                    <div class="col-12 text-end  mt-3">
                        <!-- Buttons for Search and Clear -->
                        <button class="btn btn-sm btn-danger" id="reset_filter">Reset</button>
                        <button class="btn btn-sm btn-primary" id="search_filter">Search</button>
                        <!-- Excel and PDF Buttons -->
                        <button type="button" id="btnExcelDownload" class="btn btn-sm btn-success me-1">Export CSV</button>
                        <button type="button" id="btnPdfDownload" class="btn btn-sm btn-info">Export PDF</button>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table id="dt-commission" class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th scope="col">Trip ID</th>
                                    <th scope="col">Driver Name</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Commission %</th>
                                    <th scope="col">Commission Amount</th>
                                    <th scope="col">Grand Total</th>
                                    <th scope="col">Date</th>
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
        var csrfToken = "{{ csrf_token() }}"
    </script>

    <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('site-plugins/reports/commission/index.js?v=' . time()) }}"></script>
@endpush
