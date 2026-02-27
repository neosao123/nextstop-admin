@php
    $pageTitle = 'Dashboard';
@endphp
@extends('layout.master')
@push('styles')
    <style>
        td {
            white-space: nowrap;
        }

        .dash-board a {
            color: #1f6386;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 12px;
            transition: all 0.3s ease-in-out;
            text-decoration: none !important;
        }

        .dash-board a:hover {
            color: #ffffff;
            background: #0f2550;
        }
    </style>
    <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
@endpush
@section('content')
    <h5 class="mb-3">Dashboard</h5>

    <div class="row g-3 mb-3 dash-board">
        <div class="col-sm-6 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>{{ __('index.admin_users') }}<span class="badge badge-subtle-warning rounded-pill ms-2"></span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary"
                        data-countup='{"endValue":58.386,"decimalPlaces":2,"suffix":"k"}'>{{ $user }}</div>
                    <a class="fw-semi-bold fs-10 text-nowrap" href="{{ url('users') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>{{ __('index.pending_driver') }}<span class="badge badge-subtle-info rounded-pill ms-2"></span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary"
                        data-countup='{"endValue":23.434,"decimalPlaces":2,"suffix":"k"}'>{{ $pendingCount }}</div>
                    <a class="fw-semi-bold fs-10 text-nowrap" href="{{ url('driver/pending') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>{{ __('index.verified_driver') }}<span
                            class="badge badge-subtle-success rounded-pill ms-2">9.54%</span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary"
                        data-countup='{"endValue":43594,"prefix":"$"}'>{{ $verifiedCount }}</div>
                    <a class="fw-semi-bold fs-10 text-nowrap" href="{{ url('driver/verified') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>{{ __('index.customers') }}<span class="badge badge-subtle-warning rounded-pill ms-2"></span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary"
                        data-countup='{"endValue":58.386,"decimalPlaces":2,"suffix":"k"}'>{{ $totalCustomer }}</div>
                    <a class="fw-semi-bold fs-10 text-nowrap" href="{{ url('customers') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>{{ __('index.total_trips') }}<span class="badge badge-subtle-info rounded-pill ms-2"></span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary"
                        data-countup='{"endValue":23.434,"decimalPlaces":2,"suffix":"k"}'>{{ $trips }}</div><a
                        class="fw-semi-bold fs-10 text-nowrap" href="{{ url('trips') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>{{ __('index.cancel_trips') }}<span
                            class="badge badge-subtle-success rounded-pill ms-2">9.54%</span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary"
                        data-countup='{"endValue":43594,"prefix":"$"}'>{{ $totalCancelTrip }}</div><a
                        class="fw-semi-bold fs-10 text-nowrap" href="{{ url('cancel-trips') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>Total Commission<span class="badge badge-subtle-primary rounded-pill ms-2"></span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary">₹
                        {{ number_format($totalCommission ?? 0, 2) }}</div>
                    <a class="fw-semi-bold fs-10 text-nowrap" href="{{ url('reports/commission') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>This Month's Commission<span class="badge badge-subtle-success rounded-pill ms-2"></span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary">₹
                        {{ number_format($thisMonthCommission ?? 0, 2) }}</div>
                    <a class="fw-semi-bold fs-10 text-nowrap" href="{{ url('reports/commission?filter=month') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="card overflow-hidden" style="min-width: 12rem">
                <div class="bg-holder bg-card"
                    style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);"></div>
                <!--/.bg-holder-->
                <div class="card-body position-relative">
                    <h6>Today's Commission<span class="badge badge-subtle-info rounded-pill ms-2"></span></h6>
                    <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary">₹
                        {{ number_format($todayCommission ?? 0, 2) }}</div>
                    <a class="fw-semi-bold fs-10 text-nowrap" href="{{ url('reports/commission?filter=today') }}">See all<span
                            class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ __('index.trips') }}</h5>
                        <div>
                            <div class="btn-group" role="group" aria-label="Frequency Options">
                                <input type="radio" class="btn-check" name="frequency" id="daily" value="daily"
                                    onclick="updateChart(this)">
                                <label class="btn btn-sm btn-outline-dark" for="daily">Daily</label>

                                <input type="radio" class="btn-check" name="frequency" id="weekly" value="weekly"
                                    onclick="updateChart(this)">
                                <label class="btn btn-sm btn-outline-dark" for="weekly">Weekly</label>

                                <input type="radio" class="btn-check" name="frequency" id="monthly" value="monthly"
                                    checked onclick="updateChart(this)">
                                <label class="btn btn-sm btn-outline-dark" for="monthly">Monthly</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chart_2" width="400" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ __('index.cancelled_trips') }}</h5>
                        <a class="fw-semi-bold fs-10 text-nowrap float-left" href="{{ url('cancel-trips') }}">See
                            all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table id="dt-cancel-trips" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('index.trip_no') }}</th>
                                    <th scope="col">{{ __('index.date') }}</th>
                                    <th scope="col">{{ __('index.vehicle_name') }}</th>
                                    <th scope="col">{{ __('index.customer_name') }}</th>
                                    <th scope="col">{{ __('index.driver_name') }}</th>
                                    <th scope="col">{{ __('index.cancel_by') }}</th>
                                    <th scope="col">{{ __('index.reason') }}</th>
                                    <th scope="col">{{ __('index.status') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-sm-8 col-md-8 col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ __('index.verified_driver') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table id="dt-verified-driver" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('index.driver_first_name') }}</th>
                                    <th scope="col">{{ __('index.driver_last_name') }}</th>
                                    <th scope="col">{{ __('index.driver_vehicle') }}</th>
                                    <th scope="col">{{ __('index.driver_serviceable_location') }}</th>
                                    <th scope="col">{{ __('index.driver_phone') }}</th>
                                    <th scope="col">{{ __('index.driver_email') }}</th>
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
        <div class="col-sm-4 col-md-4 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="p-2">{{ __('index.driver') }}</h5>
                    <canvas id="chart_1"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
    <script src="{{ asset('site-plugins/dashboard/index.js?v=' . time()) }}"></script>
@endpush
