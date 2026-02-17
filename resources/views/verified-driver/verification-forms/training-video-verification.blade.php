@php
  $driverStatus = json_decode($driver->driver_status, true) ?? null;
  $trainingVideoDetails = isset($driverStatus['training_video_details']) ? $driverStatus['training_video_details'] : null;
@endphp
@if (isset($driver_training_video_deatils))

  <form class="form-driver-training-video-information" id="form-driver-training-video-information" method="POST">
    @csrf
    <div class="card-body p-3">
      @if (isset($driver_training_video_deatils) && count($driver_training_video_deatils) > 0)
        <!-- Video List Table -->
        <div class="row">
          <div class="col-12">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Video Title</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($driver_training_video_deatils as $detail)
                  <tr>
                    <!-- Video Title Column -->
                    <td>{{ $detail->video_title }}</td>

                    <!-- Checkbox Status Column -->
                    <td>
                     <input type="checkbox" class="form-check-input" name="videos[]" disabled value="{{ $detail->video_id }}" 
                    {{ $detail->checked_status == 1 ? 'checked' : '' }} >
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
      @if (isset($driver_training_video) && $trainingVideoDetails === 1)
        <!-- Verification Status Block -->
        <div class="row traning_video_verification_status">
          <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
            <label class="form-label">{{ __('index.verification_status') }}<span class="text-danger">*</span></label>
            <input type="hidden" name="id" value="{{ $driver_training_video->id }}">
            <select class="form-control select2 custom-select" name="training_video_verification_status" id="training_video_verification_status" style="width:100%">
              <option value="">Select Status</option>
              <option value="approve" {{ $driver->driver_training_video_verification_status == '1' ? 'selected' : '' }}>Approve</option>
              <option value="reject" {{ $driver->driver_training_video_verification_status == '2' ? 'selected' : '' }}>Reject</option>
            </select>
          </div>
          <div class="col-lg-12 col-md-6 col-sm-12 mb-2 ">
            <label class="form-label">{{ __('index.verification_reason') }} <span
                class="text-danger {{ $driver->driver_training_video_verification_status == '2' ? '' : 'd-none' }} training_video_verification_reason_required">*</span> </label>
            <textarea class="form-control mb-2" name="training_video_verification_reason" id="training_video_verification_reason">{{ old('training_video_verification_reason', $driver_training_video->training_video_verification_reason ?? '') }}</textarea>
          </div>
        </div>
      @else
        <p>The user has not completed the training video process.</p>
      @endif
    </div>
    @if ($trainingVideoDetails === 1)
      <div class="card-footer p-2 text-end bg-light">
        <button class="btn btn-primary btn-sm" id="verify_training_video_info_btn" type="button">Verify</button>
        <button class="btn btn-primary btn-sm d-none" id="update_training_video_info_btn" type="button">Update</button>
      </div>
    @endif
  </form>
@endif
