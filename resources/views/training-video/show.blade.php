@php
  $pageTitle = __('index.view_training_video');
@endphp
@extends('layout.master')
@push('styles')

@endpush
@section('content')
  <div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
      <i class="fas fa-circle fa-stack-2x text-300"></i>
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_training_video') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
		  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          @endif
		   @if(Auth::guard('admin')->user()->can('Training-Video.List', 'admin'))
				<li class="breadcrumb-item"><a href="{{ url('/training-video') }}" class="text-decoration-none text-dark">{{__('index.training_video')}}</a></li>
		   @endif
		  <li class="breadcrumb-item active" aria-current="page">{{ __('index.view') }}</li>
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
      <form class="" id="form-add-training-video" method="POST">
       
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.view_training_video') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.video_title') }} </label>
              <input type="text" class="form-control mb-2" name="video_title" id="video_title" value="{{ $training_video->video_title }}" />
             
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.total_video_time_length') }}</label>
              <input type="number"  class="form-control mb-2" name="total_video_time_length" id="total_video_time_length" value="{{ $training_video->total_video_time_length }}" />
              
			</div>
		  </div>
		  <div class="row">
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
				@if($training_video->video_path)
					<div class="mb-3">
						<label class="form-label">{{ __('index.current_video') }}</label>
						<video width="100%" height="300Px"controls>
							<source src="{{ asset('storage/videos/' . $training_video->video_path) }}" type="video/mp4">
							Your browser does not support the video tag.
						</video>
					</div>
				@else
					<span>No video available</span>
				@endif
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			@if($training_video->thumbnail)
				<div class="mb-3">
					<label class="form-label">{{ __('index.thumbnail') }}</label>
					<div class="thumbnail-container">
						<img src="{{ asset('storage/thumbnails/' . $training_video->thumbnail) }}" 
							 alt="Video Thumbnail" 
							 class="img-thumbnail img-fluid"
							 width="100%" height="300Px">
					</div>
				</div>
			@else
				<span class="text-muted">{{ __('index.no_thumbnail_available') }}</span>
			@endif
		</div>
          </div>
          <div>
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" checked disabled>
                <label class="form-check-label" for="is_active">
                  {{ __('index.active') }}
                </label>
              </div>
            </div>
          </div>
        </div>
       
      </form>
    </div>
  </div>
@endsection
@push('scripts')
  
@endpush
