@haspermission('Dashboard.View', 'admin')
    <li class="nav-item">
        <a class="nav-link" href="{{ url('dashboard') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-chart-pie"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.dashboard') }}</span>
            </div>
        </a>
    </li>
@endhaspermission
@canany(['Role.List', 'PermissionGroup.List', 'Permissions.List', 'User.List', 'Goods-Type.List', 'Vehicle-Type.List',
    'ServiceableZone.List', 'Training-Video.List'])
    <li class="nav-item">
        <a class="nav-link dropdown-indicator" href="#dashboard" role="button" data-bs-toggle="collapse"
            aria-expanded="false" aria-controls="dashboard">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon"><span class="fas fa-cog"></span></span><span
                    class="nav-link-text ps-1">{{ __('index.configuration') }}</span>
            </div>
        </a>
        <ul class="nav collapse" id="dashboard">
            @haspermission('Role.List', 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/configuration/role') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-user-cog"></span><span class="nav-link-text ps-1">{{ __('index.roles') }}</span>
                        </div>
                    </a>
                </li>
            @endhaspermission
            {{-- @haspermission('PermissionGroup.List', 'admin')
        <li class="nav-item">
          <a class="nav-link" href="{{ url('/configuration/permission-groups') }}" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center">
              <span class="fas fa-user-check"></span><span class="nav-link-text ps-1">{{ __('index.permissions_groups') }}</span>
            </div>
          </a>
        </li>
      @endhaspermission
      @haspermission('Permissions.List', 'admin')
        <li class="nav-item">
          <a class="nav-link" href="{{ url('/configuration/permissions') }}" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center">
              <span class="fas fa-user-check"></span><span class="nav-link-text ps-1">{{ __('index.permissions') }}</span>
            </div>
          </a>
        </li>
	@endhaspermission --}}
            @haspermission('User.List', 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/users') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-users"></span><span class="nav-link-text ps-1">{{ __('index.users') }}</span>
                        </div>
                    </a>
                </li>
            @endhaspermission
            @if (Auth::guard('admin')->user()->can('Goods-Type.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('goods-type') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-list-alt"></span><span
                                class="nav-link-text ps-1">{{ __('index.goods_type') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Vehicle-Type.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('vehicle-type') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-shipping-fast"></span><span
                                class="nav-link-text ps-1">{{ __('index.vehicle_type') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Vehicle.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('vehicle') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-shipping-fast"></span><span
                                class="nav-link-text ps-1">{{ __('index.vehicle') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Coupon.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('coupon') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-shipping-fast"></span><span
                                class="nav-link-text ps-1">{{ __('index.coupon') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('ServiceableZone.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('serviceable-zone') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-map-marker-alt"></span><span
                                class="nav-link-text ps-1">{{ __('index.serviceable_zone') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Training-Video.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('training-video') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="far fa-play-circle"></span><span class="nav-link-text ps-1">
                                {{ __('index.training_video') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Setting.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('setting') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-cogs"></span><span class="nav-link-text ps-1">
                                {{ __('index.setting') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Customer-Reason.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('customer-rejection-reason') }}" data-bs-toggle=""
                        aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-user"></span><span class="nav-link-text ps-1">
                                {{ __('index.customer_rejection_reason') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Driver-Reason.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('driver-rejection-reason') }}" data-bs-toggle=""
                        aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-user"></span><span class="nav-link-text ps-1">
                                {{ __('index.driver_rejection_reason') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Service.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('service') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="far fa-list-alt"></span><span class="nav-link-text ps-1">
                                {{ __('index.service') }}</span>
                        </div>
                    </a>
                </li>
            @endif
            @if (Auth::guard('admin')->user()->can('Enquiry.List'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/enquiry') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-users"></span><span
                                class="nav-link-text ps-1">{{ __('index.enquiry') }}</span>
                        </div>
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endcanany

@if (Auth::guard('admin')->user()->can('Pending-Driver.List'))
    <li class="nav-item">
        <a class="nav-link" href="{{ url('driver/pending') }}" role="button" data-bs-toggle=""
            aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-shipping-fast"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.pending_driver') }}</span>
            </div>
        </a>
    </li>
@endif

@if (Auth::guard('admin')->user()->can('Verified-Driver.List'))
    <li class="nav-item">
        <a class="nav-link" href="{{ url('driver/verified') }}" role="button" data-bs-toggle=""
            aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-shipping-fast"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.verified_driver') }}</span>
            </div>
        </a>
    </li>
@endif

@if (Auth::guard('admin')->user()->can('Customer.List'))
    <li class="nav-item">
        <a class="nav-link" href="{{ url('customers') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-users"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.customers') }}</span>
            </div>
        </a>
    </li>
@endif


@canany(['Trip.List', 'Cancel Trip.List'])
    <li class="nav-item">
        <a class="nav-link dropdown-indicator" href="#trips" role="button" data-bs-toggle="collapse"
            aria-expanded="false" aria-controls="trips">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon"><span class="fas fa-user-check"></span></span><span
                    class="nav-link-text ps-1">{{ __('index.trips') }}</span>
            </div>
        </a>
        <ul class="nav collapse" id="trips">
            @haspermission('Trip.List', 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('trips') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-users"></span><span
                                class="nav-link-text ps-1">{{ __('index.live_trips') }}</span>
                        </div>
                    </a>
                </li>
            @endhaspermission
            @haspermission('Cancel Trip.List', 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/cancel-trips') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-user-check"></span><span class="nav-link-text ps-1">
                                {{ __('index.cancel_trips') }}</span>
                        </div>
                    </a>
                </li>
            @endhaspermission
            @haspermission('Refund.List', 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/refund-trips') }}" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-user"></span><span class="nav-link-text ps-1">
                                {{ __('index.refund') }}</span>
                        </div>
                    </a>
                </li>
            @endhaspermission
        </ul>
    </li>
@endcanany
@if (Auth::guard('admin')->user()->can('Incentives.List'))
    <li class="nav-item">
        <a class="nav-link" href="{{ url('incentives') }}" role="button" data-bs-toggle="" aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-users"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.incentives') }}</span>
            </div>
        </a>
    </li>
@endif

@if (Auth::guard('admin')->user()->can('Driver Earning.List'))
    <li class="nav-item">
        <a class="nav-link" href="{{ url('driver-earning') }}" role="button" data-bs-toggle=""
            aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-users"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.driver_payment_requests') }}</span>
            </div>
        </a>
    </li>
@endif

@if (Auth::guard('admin')->user()->can('Driver Payment History.List'))
    <li class="nav-item">
        <a class="nav-link" href="{{ url('driver-payment-history') }}" role="button" data-bs-toggle=""
            aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-users"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.driver_payment_history') }}</span>
            </div>
        </a>
    </li>
@endif

@if (Auth::guard('admin')->user()->can('Notification.List'))
    <li class="nav-item">
        <a class="nav-link" href="{{ url('notification') }}" role="button" data-bs-toggle=""
            aria-expanded="false">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon">
                    <span class="fas fa-users"></span>
                </span>
                <span class="nav-link-text ps-1"> {{ __('index.notification') }}</span>
            </div>
        </a>
    </li>
@endif

<li class="nav-item">
    <a class="nav-link dropdown-indicator" href="#reports" role="button" data-bs-toggle="collapse"
        aria-expanded="false" aria-controls="reports">
        <div class="d-flex align-items-center">
            <span class="nav-link-icon"><span class="fas fa-file-alt"></span></span><span
                class="nav-link-text ps-1">Reports</span>
        </div>
    </a>
    <ul class="nav collapse" id="reports">
        <li class="nav-item">
            <a class="nav-link" href="{{ url('reports/commission') }}" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <span class="fas fa-money-bill-wave"></span><span class="nav-link-text ps-1">Commission</span>
                </div>
            </a>
        </li>
    </ul>
</li>
