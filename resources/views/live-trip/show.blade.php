@extends('layout.master', ['pageTitle' => __('index.view_live_trip')])
@push('styles')
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
    <div class="d-flex mb-4 mt-1">
        <span class="fa-stack me-2 ms-n1">
            <i class="fas fa-circle fa-stack-2x text-300"></i>
            <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
        </span>
        <div class="col">
            <div class="">
                <h5 class="mb-0 text-primary position-relative"><span
                        class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_live_trip') }}</span><span
                        class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"
                                    class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
                        @endif
                        @if (Auth::guard('admin')->user()->can('Trip.List', 'admin'))
                            <li class="breadcrumb-item"><a href="{{ url('/trips') }}"
                                    class="text-decoration-none text-dark">{{ __('index.live_trips') }}</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ __('index.view') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if (Auth::guard('admin')->user()->can('Trip.List', 'admin'))
            <div class="col-auto ms-2 align-items-center">
                <a href="{{ url('trips') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{ __('index.back') }}</a>
            </div>
        @endif
    </div>
    <!--View Trip-->
    <div class="col-lg-12">
        <div class="card mb-3">
            <form>

                <div class="card-header bg-light">
                    <h5 class="mb-0" id="form-title">{{ __('index.trip_no_detiails') }} <b>{{ $trip->id }}</b></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.trip_no') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_id" id="trip_id"
                                value="{{ $trip->id ?? '' }}" readonly />
                        </div>
                        <!-- unique_id -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.txn_no') }}</label>
                            <input type="text" class="form-control mb-2" name="unique_id" id="unique_id"
                                value="{{ $trip->trip_unique_id ?? '' }}" readonly />
                        </div>
                        <!-- customer name -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.customer_name') }}</label>
                            <input type="text" class="form-control mb-2" name="customer_name" id="customer_name"
                                value="{{ $trip->customer->customer_first_name }} {{ $trip->customer->customer_last_name }}"
                                readonly />

                        </div>
                        <!-- customer phone -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.customer_phone') }}</label>
                            <input type="text" class="form-control mb-2" name="customer_phone" id="customer_phone"
                                value="{{ $trip->customer->customer_phone }}" readonly />

                        </div>
                        <!-- goods name -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.goods_name') }}</label>
                            <input type="text" class="form-control mb-2" name="goods_name" id="goods_name"
                                value="{{ $trip->goodtype->goods_name ?? '' }}" readonly />

                        </div>
                        <!-- Fair Amount -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.trip_fair_amount') }}</label>
                            <input type="text" class="form-control mb-2" name="fair_amount" id="fair_amount"
                                value="{{ $trip->trip_fair_amount }}" readonly />

                        </div>
                        <!-- Net Fair Amount -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.trip_netfair_amount') }}</label>
                            <input type="text" class="form-control mb-2" name="fair_amount" id="fair_amount"
                                value="{{ $trip->trip_netfair_amount }}" readonly />

                        </div>
                        <!-- Trip Discount -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.trip_discount') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_discount" id="trip_discount"
                                value="{{ $trip->trip_discount }}" readonly />

                        </div>

                        <!-- Trip CGST Rate -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 d-none">
                            <label class="form-label">{{ __('index.trip_cgst_rate') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_cgst_rate" id="trip_cgst_rate"
                                value="{{ $trip->trip_cgst_rate }}" readonly />

                        </div>
                        <!-- Trip SGST Rate -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 d-none">
                            <label class="form-label">{{ __('index.trip_sgst_rate') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_sgst_rate" id="trip_sgst_rate"
                                value="{{ $trip->trip_cgst_rate }}" readonly />

                        </div>
                        <!-- Trip TaX Amount-->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 d-none">
                            <label class="form-label">{{ __('index.trip_tax_amount') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_tax_amount" id="trip_sgst_rate"
                                value="{{ $trip->trip_tax_amount }}" readonly />

                        </div>
                        <!-- Trip Total -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.trip_total_amount') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_total_amount"
                                id="trip_total_amount" value="{{ $trip->trip_total_amount }}" readonly />

                        </div>

                        <!-- Trip Status -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.trip_status') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_status" id="trip_status"
                                value="{{ $trip->trip_status }}" readonly />

                        </div>
                        @php
                            if ($trip->trip_payment_mode == 'cod') {
                                $paymentmode = 'Cash on delivery';
                            } elseif ($trip->trip_payment_mode == 'online') {
                                $paymentmode = 'Online';
                            } else {
                                $paymentmode = '';
                            }
                        @endphp
                        <!-- Payment Mode -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.trip_payment_mode') }}</label>
                            <input type="text" class="form-control mb-2" name="trip_payment_mode"
                                id="trip_payment_mode" value="{{ $paymentmode }}" readonly />

                        </div>
                        @if ($adminCommission)
                            <hr>
                            <h5>Admin Commission</h5>
                            <!-- Driver name -->
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{ __('index.commission_percentage') }}</label>
                                <input type="text" class="form-control mb-2" name="commission_percentage"
                                    id="commission_percentage" value="{{ $adminCommission->commission_percentage }}"
                                    readonly />
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{ __('index.commission_amount') }}</label>
                                <input type="text" class="form-control mb-2" name="commission_amount"
                                    id="commission_amount" value="{{ $adminCommission->commission_amount }}" readonly />
                            </div>
                        @endif
                        @if ($vehicleDetails)
                            <hr>
                            <h5>Driver Details</h5>
                            <!-- Driver name -->
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{ __('index.driver_name') }}</label>
                                <input type="text" class="form-control mb-2" name="driver_name" id="driver_name"
                                    value="{{ $vehicleDetails->driver_first_name . ' ' . $vehicleDetails->driver_last_name }}"
                                    readonly />
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{ __('index.driver_phone') }}</label>
                                <input type="text" class="form-control mb-2" name="driver_phone" id="driver_phone"
                                    value="{{ $vehicleDetails->driver_phone }}" readonly />
                            </div>

                            @if ($DriverEarning)
                                <hr>
                                <h5>Driver Earning</h5>
                                @foreach ($DriverEarning as $item)
                                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                        <label class="form-label">{{ __('index.amount') }}</label>
                                        <input type="text" class="form-control mb-2" name="driver_amount"
                                            id="driver_amount" value="{{ $item->amount }}" readonly />
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                        <label class="form-label">{{ __('index.type') }}</label>
                                        <input type="text" class="form-control mb-2" name="type" id="type"
                                            value="{{ $item->type }}" readonly />
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                        <label class="form-label">{{ __('index.status') }}</label>
                                        <input type="text" class="form-control mb-2" name="status" id="status"
                                            value="{{ $item->status }}" readonly />
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                        <label class="form-label">{{ __('index.message') }}</label>
                                        <input type="text" class="form-control mb-2" name="message" id="message"
                                            value="{{ $item->message }}" readonly />
                                    </div>
                                @endforeach
                            @endif

                        @endif

                        <hr>
                        <h5>Vehicle Details</h5>
                        <!-- vehicle name -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.vehicle_name') }}</label>
                            <input type="text" class="form-control mb-2" name="vehicle_name" id="vehicle_name"
                                value="{{ $trip->vehicle->vehicle_name }}" readonly />
                        </div>

                        @if ($vehicleDetails)
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <label class="form-label">{{ __('index.vehicle_number') }}</label>
                                <input type="text" class="form-control mb-2" name="vehicle_number"
                                    id="vehicle_number" value="{{ $vehicleDetails->vehicle_number }}" readonly />
                            </div>
                        @endif

                        <!-- Max Load Capacity -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.vehicle_max_load_capacity') }} </label>
                            <input type="number" class="form-control " name="vehicle_max_load_capacity"
                                id="vehicle_max_load_capacity" value="{{ $trip->vehicle->vehicle_max_load_capacity }}"
                                step="0.01" readonly />
                        </div>

                        <!-- Per KM Delivery Charge -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.vehicle_per_km_delivery_charge') }} </label>
                            <input type="number" class="form-control " name="vehicle_per_km_delivery_charge"
                                id="vehicle_per_km_delivery_charge"
                                value="{{ $trip->vehicle->vehicle_per_km_delivery_charge }}" step="0.01" readonly />
                        </div>

                        <!-- Per KM Extra Delivery Charge -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.vehicle_per_km_extra_delivery_charge') }} </label>
                            <input type="number" class="form-control " name="vehicle_per_km_extra_delivery_charge"
                                id="vehicle_per_km_extra_delivery_charge"
                                value="{{ $trip->vehicle->vehicle_per_km_extra_delivery_charge }}" step="0.01"
                                readonly />
                        </div>


                        <hr>
                        <h5>Coupon Details</h5>
                        <!-- Coupon code -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.coupon_code') }}</label>
                            <input type="text" class="form-control mb-2" name="coupon_code" id="coupon_code"
                                value="{{ $trip->coupon->coupon_code ?? '' }}" readonly />
                        </div>
                        <!-- Coupon Type -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.coupon_type') }}</label>
                            <input type="text" class="form-control mb-2" name="coupon_type" id="coupon_type"
                                value="{{ $trip->coupon->coupon_type ?? '' }}" readonly />
                        </div>
                        <!-- Coupon Type -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.coupon_amount_or_percentage') }}</label>
                            <input type="text" class="form-control mb-2" name="coupon_amount_or_percentage"
                                id="coupon_amount_or_percentage"
                                value="{{ $trip->coupon->coupon_amount_or_percentage ?? '' }}" readonly />
                        </div>
                        <!-- Coupon Cap Limit -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.coupon_cap_limit') }}</label>
                            <input type="text" class="form-control mb-2" name="coupon_cap_limit"
                                id="coupon_cap_limit" value="{{ $trip->coupon->coupon_cap_limit ?? '' }}" readonly />
                        </div>
                        <!-- Coupon Min Amount -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label class="form-label">{{ __('index.coupon_min_order_amount') }}</label>
                            <input type="text" class="form-control mb-2" name="coupon_min_order_amount"
                                id="coupon_min_order_amount" value="{{ $trip->coupon->coupon_min_order_amount ?? '' }}"
                                readonly />
                        </div>
                        <hr>
                        @if ($trip->sourceAddress)
                            <h5>Pickup Address Details</h5>

                            <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                <label class="form-label">{{ __('index.customer_name') }}</label>
                                <input type="text" class="form-control mb-2" name="customer_name"
                                    value="{{ $trip->customer->customer_first_name }} {{ $trip->customer->customer_last_name }}"
                                    readonly />
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                <label class="form-label">{{ __('index.customer_address') }}</label>
                                <input type="text" class="form-control mb-2" name="customer_address"
                                    value="{{ $trip->sourceAddress->customeraddresses_address }},{{ $trip->sourceAddress->customeraddresses_mobile }}"
                                    readonly />
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                <label class="form-label">{{ __('index.customer_address_type') }}</label>
                                <input type="text" class="form-control mb-2" name="customer_address_type"
                                    value="{{ $trip->sourceAddress->customeraddresses_location_type ?? 'home' }}"
                                    readonly />
                            </div>
                        @endif
                        @if ($customerStopAddress && count($customerStopAddress) > 0)
                            <h5>Stop Address Details</h5>
                            @foreach ($customerStopAddress as $item)
                                <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                    <label class="form-label">{{ __('index.customer_name') }}</label>
                                    <input type="text" class="form-control mb-2" name="customer_name"
                                        value="{{ $item->customeraddresses_name }}" readonly />
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                    <label class="form-label">{{ __('index.customer_address') }}</label>
                                    <input type="text" class="form-control mb-2" name="customer_address"
                                        value="{{ $item->customeraddresses_address }},{{ $item->customeraddresses_mobile }}"
                                        readonly />
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                    <label class="form-label">{{ __('index.customer_address_type') }}</label>
                                    <input type="text" class="form-control mb-2" name="customer_address_type"
                                        value="{{ $item->customeraddresses_location_type }}" readonly />
                                </div>
                            @endforeach
                        @endif
                        @if ($trip->destinationAddress)
                            <h5>Drop-off Address Details</h5>

                            <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                <label class="form-label">{{ __('index.customer_name') }}</label>
                                <input type="text" class="form-control mb-2" name="customer_name"
                                    value="{{ $trip->destinationAddress->customeraddresses_name }}" readonly />
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                <label class="form-label">{{ __('index.customer_address') }}</label>
                                <input type="text" class="form-control mb-2" name="customer_address"
                                    value="{{ $trip->destinationAddress->customeraddresses_address }},{{ $trip->destinationAddress->customeraddresses_mobile }}"
                                    readonly />
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                <label class="form-label">{{ __('index.customer_address_type') }}</label>
                                <input type="text" class="form-control mb-2" name="customer_address_type"
                                    value="{{ $trip->destinationAddress->customeraddresses_location_type ?? 'home' }}"
                                    readonly />
                            </div>
                        @endif
                        @foreach ($trip->rating as $rating)
                            @if ($rating->rating_given_by == 'customer')
                                <hr>
                                <h5>Customer Rating</h5>
                                <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                    <label class="form-label">{{ __('index.rating') }}</label>
                                    <input type="text" class="form-control mb-2" name="customer_rating"
                                        value="{{ $rating->rating_value ?? '' }}" readonly />
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    <label class="form-label">{{ __('index.description') }}</label>
                                    <input type="text" class="form-control mb-2" name="customer_rating_description"
                                        value="{{ $rating->rating_description ?? '' }}" readonly />
                                </div>
                            @endif
                            @if ($rating->rating_given_by == 'driver')
                                <hr>
                                <h5>Driver Rating</h5>
                                <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                    <label class="form-label">{{ __('index.rating') }}</label>
                                    <input type="text" class="form-control mb-2" name="driver_rating"
                                        value="{{ $rating->rating_value ?? '' }}" readonly />
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    <label class="form-label">{{ __('index.description') }}</label>
                                    <input type="text" class="form-control mb-2" name="driver_rating_description"
                                        value="{{ $rating->rating_description ?? '' }}" readonly />
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div>
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="form-check">
                                <input class="form-check-input" name="is_active" id="is_active" type="checkbox"
                                    value="1" {{ $trip->is_active == 1 ? 'checked' : '' }} disabled>
                                <label class="form-check-label" for="is_active">
                                    {{ __('index.active') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div>
                        @if ($tripStatus->count())
                            <hr>
                            <h5>Trip Status History</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('index.trip_status_title') }}</th>
                                        <th>{{ __('index.trip_status_reason') }}</th>
                                        <th>{{ __('index.driver') }}</th>
                                        <th>{{ __('index.trip_action_type') }}</th>

                                        <th>{{ __('index.created_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tripStatus as $status)
                                        <tr>

                                            <td>{{ $status->trip_status_short }}</td>
                                            <td>{{ $status->trip_status_reason }}</td>
                                            <td>{{ $status->driver_first_name }}-{{ $status->driver_last_name }}</td>
                                            <td>{{ $status->trip_action_type }}</td>
                                            <td>{{ \Carbon\Carbon::parse($status->created_at)->format('d-m-Y h:i A') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
@endpush
