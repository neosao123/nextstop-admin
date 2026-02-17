@extends('layout.master', ['pageTitle' => __('index.view_serviceable_zone')])
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
	 #map { height: 400px; width: 900px; }
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
            <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{__('index.view_serviceable_zone')}}</span><span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    @if(Auth::guard('admin')->user()->can('ServiceableZone.List', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/serviceable-zone') }}" class="text-decoration-none text-dark">{{__('index.serviceable_zone')}}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{__('index.view_serviceable_zone')}}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(Auth::guard('admin')->user()->can('ServiceableZone.List', 'admin'))
    <div class="col-auto ms-2 align-items-center">
        <a href="{{ url('serviceable-zone') }}" class="btn btn-falcon-primary btn-sm me-1 mb-1">{{__('index.back')}}</a>
    </div>
    @endif
</div>
<!--ADD USER-->
 <div class="col-lg-12">
    <div class="card mb-3">
      <form >
       
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.view_serviceable_zone')}}</h5>
        </div>
        <div class="card-body"> 
          <div class="row">
            
			<!-- Serviceable Zone Name -->
			<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			  <label class="form-label">{{__('index.serviceable_zone_name')}}</label>
			  <input type="text" class="form-control mb-2" name="serviceable_zone_name" id="serviceable_zone_name" value="{{$serviceable_zone->serviceable_zone_name }}" readonly />
			
			</div>
			@php
			   
				$polygon = $serviceable_zone->serviceable_area;
				$polygon = str_replace(['POLYGON((', '))'], '', $polygon);
				$coordinates = explode(',', $polygon);
				$coordinatesArray = array_map(function($coordinate) {
					return explode(' ', trim($coordinate));
				}, $coordinates);
				$coordinatesString = json_encode($coordinatesArray);
			@endphp
			
			<!-- Coordinates Input -->
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                <label class="form-label">{{__('index.co_ordinates')}}<span class="text-danger">*</span></label>
                <input type="text" class="form-control mb-2" name="co_ordinates" id="co_ordinates" value="{{ old('co_ordinates', $coordinatesString) }}" readonly/>
                @error('co_ordinates')
                  <div class="error py-2">{{ $message }}</div>
                @enderror
            </div>

            <!-- Map Display -->
            <div class="my-2 flex flex-col align-center justify-center">
                <div id="map"></div><br>
            </div>	 
          </div>
          <div>
            <div class="col-lg-4 col-md-6 col-sm-12">
              <div class="form-check">
                <input class="form-check-input" name="is_active" id="is_active" type="checkbox" value="1" {{ $serviceable_zone->is_active == 1 ? 'checked' : '' }} disabled>
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
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEDVgwojhHdvfyzg2alNbtcUJBqILWInA&libraries=drawing">
    </script>
<script>
function initMap() {
    var map = new google.maps.Map(document.getElementById('map'), {
        center: {
            lat: 16.691307, 
            lng: 74.244865
        },
        zoom: 10
    });

    // Initialize the drawing manager
    var drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: google.maps.drawing.OverlayType.POLYGON,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: ['polygon']
        },
        polygonOptions: {
            editable: true,  // Editable polygons when drawing
            draggable: true, // Draggable polygons when drawing
            strokeColor: '#808080',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#808080',
            fillOpacity: 0.35
        }
    });

    drawingManager.setMap(map);

    // Check if there are existing coordinates (from a hidden input field or server-side data)
    var existingCoordinates = document.getElementById("co_ordinates").value;

    // If there are existing coordinates, draw the polygon
    if (existingCoordinates) {
        var coordinates = JSON.parse(existingCoordinates);  // Convert JSON string back to array

        // Create the polygon on the map
        var polygonCoords = coordinates.map(function(coord) {
            return { lat: parseFloat(coord[1]), lng: parseFloat(coord[0]) };
        });

        var polygon = new google.maps.Polygon({
            paths: polygonCoords,
            editable: false,  // Set to false to prevent editing
            draggable: false, // Set to false to prevent dragging
            strokeColor: '#808080',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#808080',
            fillOpacity: 0.35
        });

        polygon.setMap(map);
    }

    // Disable drawing mode to prevent adding new polygons when in view mode
    drawingManager.setDrawingMode(null); 
}

// Load the map when the window loads
window.onload = initMap;

</script>
@endpush