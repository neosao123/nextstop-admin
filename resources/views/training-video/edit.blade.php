@php
  $pageTitle = __('index.edit_training_video');
@endphp
@extends('layout.master')
@push('styles')
  <style>
    .error {
      color: red;
    }
		.progress {
		position: relative;
		width: 100%;
	}

	.bar {
		background-color: #b5076f;
		width: 0%;
		height: 20px;
	}

	.percent {
		position: absolute;
		display: inline-block;
		left: 50%;
		color: #040608;
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
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.edit_training_video') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
		  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          @endif
		   @if(Auth::guard('admin')->user()->can('Training-Video.List', 'admin'))
				<li class="breadcrumb-item"><a href="{{ url('/training-video') }}" class="text-decoration-none text-dark">{{__('index.training_video')}}</a></li>
		   @endif
		  <li class="breadcrumb-item active" aria-current="page">{{ __('index.add') }}</li>
        </ol>
      </nav>
    </div>
	@if(Auth::guard('admin')->user()->can('Training-Video.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('training-video') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
	@endif
  </div>
  <!-- Add Training Video -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form class="" id="form-add-training-video" method="POST" action="{{ url('training-video/' . $training_video->id) }}" enctype="multipart/form-data">
        @csrf
		@method('PUT') <!-- This ensures a PUT request is made -->
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.edit_training_video') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.video_title') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-2" name="video_title" id="video_title" value="{{ $training_video->video_title }}" />
              @error('video_title')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.total_video_time_length') }}</label>
              <input type="number"  class="form-control mb-2" name="total_video_time_length" id="total_video_time_length" value="{{ $training_video->total_video_time_length }}" />
              @error('total_video_time_length')
                <span class="text-danger backend-error">{{ $message }}</span>
              @enderror
			</div>
		  </div>
		  <div class="row">
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
				<div class="mb-3">
					<label class="form-label">{{__('index.video')}} </label>
					<input type="file" id="video" class="form-control " name="video" accept=".mp4">
	                @error('video')
					<span class="text-danger backend-error">{{ $message }}</span>
				  @enderror
				</div>
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
				@if($training_video->video_path)
					<div class="mb-3">
						<label class="form-label">{{ __('index.current_video') }}</label>
						<video width="100%" height="300Px"controls>
							<source src="{{ asset('storage/videos/' . $training_video->video_path) }}" type="video/mp4">
							
						</video>
					</div>					
				
				@endif
			
			</div>
			@if($training_video->video_path)
			<div class="col-lg-4 col-md-6 col-sm-12 mt-5">
			       <!-- Delete Video Link -->
				<a href="{{ url('training-video/delete/video/' . $training_video->id) }}" class="mx-3 text-danger">
					<span class="fas fa-trash-alt"></span>
				</a>
			</div>
			@endif
          </div>
		  <!-- Add this right after the video section -->
			<div class="row">
				<!-- Thumbnail Upload -->
				<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
					<div class="mb-3">
						<label class="form-label">{{__('index.thumbnail')}}</label>
						<input type="file" id="thumbnail" class="form-control" name="thumbnail" accept=".jpg,.png,.jpeg">
						@error('thumbnail')
							<span class="text-danger backend-error">{{ $message }}</span>
						@enderror
					</div>
				</div>
				
				<!-- Current Thumbnail -->
				@if($training_video->thumbnail)
				<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
					<div class="mb-3">
						<label class="form-label">{{ __('index.thumbnail') }}</label>
						<img src="{{ asset('storage/thumbnails/' . $training_video->thumbnail) }}" 
							 alt="Thumbnail" 
							 class="img-thumbnail" 
							 width="100%" height="300Px">
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-12 mt-5">
					<!-- Delete Thumbnail Link -->
					<a href="{{ url('training-video/delete/thumbnail/' . $training_video->id) }}" class="mx-3 text-danger">
						<span class="fas fa-trash-alt"></span>
					</a>
				</div>
				@endif
			</div>
          <div>
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $training_video->is_active == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  {{ __('index.active') }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
          <button class="btn btn-primary" type="submit" id="submit">{{ __('index.update') }}</button>
        </div>
      </form>
    </div>
  </div>
@endsection
@push('scripts')
   <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('site-plugins/training-video/edit.js?v=' . time()) }}"></script>
@endpush
