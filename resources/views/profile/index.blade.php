@extends('layout.master', ['pageTitle' =>__('index.profile_update')])
@push('styles')
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.profile')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
					<li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
					<li class="breadcrumb-item active" aria-current="page">{{__('index.profile_update')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('dashboard') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.dashboard')}}</a>
    </div>
    @endif
</div>
<div class="row g-3 mb-3 justify-content-center">
    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-8 col-sm-12 pe-lg-2">
        <div class="card">
            <form method="post" action="{{ url('profile/update' )}}" enctype="multipart/form-data" id="form-add-user">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{__('index.profile')}}</h5>
                </div>
                <div class="card-body pt-3 pb-2">
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="mb-1">
                                <label class="form-label">{{__('index.first_name')}} : <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="first_name" value="{{ $details->first_name}}" maxlength="100" />
                                @error('first_name')
                                <div class="error py-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-1">
                                <label class="form-label">{{__('index.last_name')}}: <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="last_name" value="{{ $details->last_name}}" maxlength="100" />
                                @error('last_name')
                                <div class="error py-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-1">
                                <label class="form-label">{{__('index.email')}} : <span class="text-danger">*</span></label>
                                <input class="form-control" type="email" name="email" value="{{ $details->email}}" maxlength="100" />
                                @error('email')
                                <div class="error py-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-1">
                                <label class="form-label">{{__('index.phone_number')}} : <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="phone_number" value="{{ $details->phone_number }}" maxlength="12" />
                                @error('phone_number')
                                <div class="error py-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row gx-3">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('index.avatar')}}</label>
                                <input type="file" id="file" class="form-control " name="avatar" accept=".jpg, .jpeg, .png">
                            </div>
                        </div>
                    </div>
                   <div class="row gx-3">
						<div class="col-12">
							<img class="img-radius" id="showImage" 
								 src="{{ $details->avatar ? asset('storage/'.$details->avatar . '?t=' . time()) : asset('/assets/img/user/default-user.png') }}" 
								 height="80" width="80" />
							@if($details->avatar)
								<a href="{{ url('profile/delete/avatar') }}" class="mx-3 text-danger">
									<span class="fas fa-trash-alt"></span>
								</a>
							@endif
						</div> 
					</div>

                </div>
                <div class="card-footer float-end">
                    <button type="submit" class="btn btn-primary">{{__('index.profile_update')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@push('scripts')
 <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
 <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('site-plugins/profile/index.js?v=' . time()) }}"></script>

@endpush