@if (isset($driver))
  <form class="form-driver-admin-verification" id="form-driver-admin-verification" method="POST">
    @csrf
	@method('PUT') 
    <div class="card-body p-3">
      <!-- Verification Status Block -->
      <div class="row admin_verification_status">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.verification_status') }}<span class="text-danger">*</span></label>
          <select class="form-control select2 custom-select" name="admin_verification_status" id="admin_verification_status" style="width:100%">
            <option value="">Select Status</option>
            <option value="approve" {{ $driver->admin_verification_status == '1' ? 'selected' : '' }}>Approve</option>
            <option value="reject" {{ $driver->admin_verification_status == '2' ? 'selected' : '' }}>Reject</option>
          </select> 
        </div>
        <div class="col-lg-12 col-md-6 col-sm-12 mb-2 ">
          <label class="form-label ">{{ __('index.verification_reason') }} <span
              class="text-danger {{ $driver->admin_verification_status == '2' ? '' : 'd-none' }} admin_verification_reason_required">*</span> </label>
          <textarea class="form-control mb-2 " name="admin_verification_reason" id="admin_verification_reason">{{ old('admin_verification_reason', $driver->admin_verification_reason ?? '') }}</textarea>
        </div>
      </div>
    </div>

    <div class="card-footer p-2 text-end bg-light">
      <button class="btn btn-primary btn-sm" id="admin_verify_info_btn" type="button">Verify</button>
    </div>
  </form>
@else
  <div class="card-body p-3">
    <p>Driver is not found</p>
  </div>
@endif