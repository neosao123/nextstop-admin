@extends('layout.master', ['pageTitle' => __('index.permissions')])
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.user_permissions_access_rights')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('User.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/users') }}" class="text-decoration-none text-dark">{{__('index.users')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.permissions')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('User.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('users') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>
<!--ADD USER-->
 <div class="row gx-3">
    <div class="col-md-3">
        <div class="sticky-sidebar">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0" id="form-title">{{__('index.user')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">{{__('index.name')}}</label>
                            <input readonly class="form-control" value="{{ $user->first_name }} {{$user->last_name }}" />
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">{{__('index.email')}}</label>
                            <input readonly class="form-control" value="{{ $user->email }}" />
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">{{__('index.phone_number')}}</label>
                            <input readonly class="form-control" value="{{ $user->phone_number }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- List Permissions -->
    <div class="col-md-9">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{__('index.permissions')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($groups as $group)
                    <div class="col-sm-6 col-md-6 col-lg-6 mb-3">
                        <div class="mb-2 border-bottom py-1">
                            {{ $group->group_name }}
                        </div>
                        @foreach ($permissions as $permission)
                        @php
                        $directPermissionArr = $directPermission->toArray();
                        $isChecked = in_array($permission->id, array_column($directPermissionArr, 'id'));
                        @endphp
                        @if ($permission->group_id === $group->id)
                        <div class="d-flex justify-content-between btn-reveal-trigger border-200 todo-list-item">
                            <div class="form-check mb-0 d-flex align-items-center">
                                <input type="checkbox" class="form-check-input rounded-circle p-1 form-check-input-primary" data-permission="{{ $permission->id . '|' . $permission->group_id . '|' . $permission->name . '|' . $permission->guard }}" name="{{ $permission->name }}" id="{{ 'chk-' . $permission->id }}" {{ $permission->name === 'Welcome.View' ? 'checked disabled' : ($isChecked ? 'checked' : '') }} />
                                <label class="form-check-label mb-0 mt-1 p-1" for="{{ 'chk-' . $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}";
	const userId = '{{ $user->id }}';
  </script>
      <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
 <script src="{{ asset('site-plugins/user-permission/index.js?v=' . time()) }}"></script>
@endpush