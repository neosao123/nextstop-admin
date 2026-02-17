@php
  $driverStatus = json_decode($driver->driver_status, true) ?? null;
  $vehicleDetails = isset($driverStatus['vehicle_details']) ? $driverStatus['vehicle_details'] : null;
@endphp
@if (isset($driver_vehicle_details) && $vehicleDetails === 1)
  <form class="form-driver-vehicle-information" id="form-driver-vehicle-information" method="POST">
    @csrf
    <div class="card-body p-3">
      <div class="row">
        <div class="col-12 mb-2 text-end">
          <button class="btn btn-outline-warning btn-sm" id="edit_vehicle_info_btn" type="button">Edit</button>
          <button class="btn btn-outline-info btn-sm d-none" id="view_vehicle_info_btn" type="button">View</button>
        </div>
      </div>

      <!-- Vehicle Information -->
      <div class="row">

        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.vehicle') }}<span class="text-danger">*</span></label>
          <input type="text" class="form-control mb-2" id="driver_vehicle_input" value="{{ $driver_vehicle_details->vehicle->vehicle_name ?? '' }}" readonly />
          <div class="d-none" id="driver_vehicle_div">
            <select class="custom-select form-control select2" name="driver_vehicle" id="driver_vehicle" aria-label="vehicle" style="width:100%">
              <option value="{{ $driver_vehicle_details->vehicle_id }}">{{ $driver_vehicle_details->vehicle->vehicle_name ?? '' }}</option>
            </select>
          </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.vehicle_number') }}<span class="text-danger">*</span></label>
          <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ $driver_vehicle_details->id ?? '' }}" />
          <input type="text" class="form-control mb-2 toggleVehicleElement" name="driver_vehicle_number" id="driver_vehicle_number"
            value="{{ old('driver_vehicle_number', $driver_vehicle_details->vehicle_number) ?? '' }}" readonly />
          @error('driver_vehicle_number')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-2 uploadVehicleDocs d-none">
          <label class="form-label">{{ __('index.vehicle_photo') }}</label>
          <input type="file" class="form-control" name="vehicle_photo" id="vehicle_photo" accept=".jpg, .jpeg, .png">
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label photo-label mb-1">{{ __('index.vehicle_photo') }}</label> <!-- Label for vehicle photo -->

          @if (isset($driver_vehicle_details->vehicle_photo) && !empty($driver_vehicle_details->vehicle_photo))
            @php
              $extension = pathinfo($driver_vehicle_details->vehicle_photo, PATHINFO_EXTENSION);
            @endphp
            <div class="d-flex align-items-center">
              <img class="img-radius border me-2" id="showImage" src="{{ asset('storage/' . $driver_vehicle_details->vehicle_photo) }}"
                onerror="this.onerror=null;this.src='{{ asset('/assets/img/user/default-user.png') }}';" alt="Vehicle Photo" height="60" width="60" />
              <button class="btn btn-outline-info btn-sm view-vehicle-document-btn" data-url="{{ url('storage-bucket?path=' . $driver_vehicle_details->vehicle_photo) }}" type="button"
                data-document-type="{{ $extension }}">View</button>

              <a data-url="{{ url('driver/verified/vehicle-photo/delete/' . $driver_vehicle_details->id) }}" class="text-danger deleteVehiclePhoto d-none mx-2">
                <span class="fas fa-trash-alt"></span>
              </a>
            </div>
          @else
            <p class="text-muted">No document available</p>
          @endif
        </div>
      </div>

      <!-- Verification Status Block -->
      <div class="row vehicle_verification_status">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.verification_status') }}<span class="text-danger">*</span></label>
          <select class="form-control select2 custom-select" name="vehicle_verification_status" id="vehicle_verification_status" style="width:100%">
            <option value="">Select Status</option>
            <option value="approve" {{ $driver->driver_vehicle_verification_status == '1' ? 'selected' : '' }}>Approve</option>
            <option value="reject" {{ $driver->driver_vehicle_verification_status == '2' ? 'selected' : '' }}>Reject</option>
          </select>
        </div>
        <div class="col-lg-12 col-md-6 col-sm-12 mb-2 ">
          <label class="form-label ">{{ __('index.verification_reason') }} <span
              class="text-danger {{ $driver->driver_vehicle_verification_status == '2' ? '' : 'd-none' }} vehicle_verification_reason_required">*</span> </label>
          <textarea class="form-control mb-2 " name="vehicle_verification_reason" id="vehicle_verification_reason">{{ old('vehicle_verification_reason', $driver_vehicle_details->vehicle_verification_reason ?? '') }}</textarea>
        </div>
      </div>
    </div>

    <div class="card-footer p-2 text-end bg-light">
      <button class="btn btn-primary btn-sm" id="verify_vehicle_info_btn" type="button">Verify</button>
      <button class="btn btn-primary btn-sm d-none" id="update_vehicle_info_btn" type="button">Update</button>
    </div>
  </form>
@else
  <div class="card-body p-3">
    <p>The user has not completed the vehicle details process.</p>
  </div>
@endif
{{-- Modal For View Image Document --}}
<div class="modal fade" id="documentVehicleModal" tabindex="-1" aria-labelledby="documentVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="documentVehicleModalLabel">Document Viewer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="imageContainer">
          <img id="documentVehicleImage" src="" style="width: 100%;" alt="Document Image">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal For View PDF, DOCX Document --}}
<div class="modal fade" id="documentVehicleModalPDF" tabindex="-1" aria-labelledby="documentVehicleModalLabelPDF" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="documentVehicleModalLabelPDF">Document Viewer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Iframe for displaying PDF -->
        <iframe id="documentVehicleIframe" src="" style="width: 100%; height:70vh;" frameborder="0"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
