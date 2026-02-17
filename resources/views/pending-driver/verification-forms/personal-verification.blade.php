@php
  $driverStatus = json_decode($driver->driver_status, true) ?? null;
  $documentDetails = isset($driverStatus['document_details']) ? $driverStatus['document_details'] : null;
@endphp
<form class="form-driver-personal-information" id="form-driver-personal-information" method="POST">
  @csrf
  @method('PUT')
  <div class="card-body p-3">
    @if ($documentDetails === 1)
      <div class="row">
        <div class="col-12 mb-2 text-end">
          <button class="btn btn-outline-warning btn-sm " id="edit_btn" data-personal-info-id="{{ $driver->id }}" type="button">{{ __('index.edit') }}</button>
          <button class="btn btn-outline-info btn-sm d-none " id="view_btn" data-personal-info-id="{{ $driver->id }}" type="button">{{ __('index.view') }}</button>
        </div>
      </div>
    @endif
    {{-- Personal Information --}}
    <div class="row">

      <h5>{{ __('index.personal_information') }}</h5>
      <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
        <label class="form-label">{{ __('index.driver_first_name') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control mb-2 toggleElement" name="driver_first_name" id="driver_first_name" value="{{ old('driver_first_name', $driver->driver_first_name) ?? '' }}"
          readonly />
        @error('driver_first_name')
          <span class="text-danger backend-error">{{ $message }}</span>
        @enderror
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
        <label class="form-label">{{ __('index.driver_last_name') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control mb-2 toggleElement" name="driver_last_name" id="driver_last_name" value="{{ old('driver_last_name', $driver->driver_last_name) ?? '' }}" readonly />
        @error('driver_last_name')
          <span class="text-danger backend-error">{{ $message }}</span>
        @enderror
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
        <label class="form-label">{{ __('index.driver_phone') }} <span class="text-danger">*</span> </label>
        <input type="text" class="form-control mb-2 toggleElement" name="driver_phone" id="driver_phone" value="{{ old('driver_phone', $driver->driver_phone) ?? '' }}" readonly />
        @error('driver_phone')
          <span class="text-danger backend-error">{{ $message }}</span>
        @enderror
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
        <label class="form-label">{{ __('index.driver_email') }} </label>
        <input type="text" class="form-control mb-2 toggleElement" name="driver_email" id="driver_email" value="{{ old('driver_email', $driver->driver_email) ?? '' }}" readonly />
        @error('driver_email')
          <span class="text-danger backend-error">{{ $message }}</span>
        @enderror
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
        <label class="form-label">{{ __('index.driver_gender') }} <span class="text-danger">*</span> </label>
        <input type="text" class="form-control mb-2" id="driver_gender_input" value="{{ ucfirst($driver->driver_gender) ?? '' }}" readonly />
        <div class="d-none" id="driver_gender_div">
          <select class="form-control custom-select select2 " name="driver_gender" id="driver_gender" aria-label="" style="width: 100% important;">
            <option value="">Select Gender</option>
            <option value="male" {{ $driver->driver_gender == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ $driver->driver_gender == 'female' ? 'selected' : '' }}>Female</option>
            <option value="others" {{ $driver->driver_gender == 'others' ? 'selected' : '' }}>Others</option>
          </select>
        </div>
        @error('driver_gender')
          <span class="text-danger backend-error">{{ $message }}</span>
        @enderror
      </div>
      <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
        <label class="form-label">{{ __('index.driver_serviceable_location') }} <span class="text-danger">*</span> </label>
        <input type="text" class="form-control mb-2" id="driver_serviceable_location_input"
          value="{{ isset($driver->serviceableZones) ? ucfirst($driver->serviceableZones->serviceable_zone_name) ?? '' : '' }}" readonly />
        <div class="d-none" id="driver_serviceable_location_div">
          <select class="form-control custom-select select2 " name="driver_serviceable_location" id="driver_serviceable_location" aria-label="" style="width: 100% important;">
            @isset($driver->serviceableZones)
              <option value="{{ $driver->serviceableZones->id }}">{{ ucfirst($driver->serviceableZones->serviceable_zone_name) ?? '' }}</option>
            @endisset
          </select>
        </div>
        @error('driver_serviceable_location')
          <span class="text-danger backend-error">{{ $message }}</span>
        @enderror
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-2 uploadDocs d-none">
        <label class="form-label">{{ __('index.driver_photo') }}</label>
        <input type="file" class="form-control driver_photo" name="driver_photo" id="driver_photo" accept=".jpg, .jpeg, .png">
      </div>

      <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
        <label class="form-label photo-label mb-1">{{ __('index.driver_photo') }}</label> <!-- Label for vehicle photo -->

        @if (isset($driver->driver_photo) && !empty($driver->driver_photo))
          @php
            $extension = pathinfo($driver->driver_photo, PATHINFO_EXTENSION);
          @endphp
          <div class="d-flex align-items-center">
            <img class="img-radius me-2" id="showImage" src="{{ asset('storage/' . $driver->driver_photo) }}" onerror="this.onerror=null;this.src='{{ asset('/assets/img/docs-placeholder.png') }}';"
              alt="Photo" height="60" width="60" />
            <button class="btn btn-outline-info btn-sm view-document-btn" data-url="{{ url('storage-bucket?path=' . $driver->driver_photo) }}" type="button"
              data-document-type="{{ $extension }}">
              View
            </button>
            <a data-url="{{ url('driver/pending/driver-photo/delete/' . $driver->id) }}" class="text-danger deleteDriverPhoto d-none mx-2">
              <span class="fas fa-trash-alt"></span>
            </a>
          </div>
        @else
          <div class="d-flex align-items-center">
            <img class="img-radius me-2" id="showImage" src="{{ asset('/assets/img/user/default-user.png') }}" alt="Default Photo" height="60" width="60" />
          </div>
        @endif
      </div>

      <div class="col-lg-12 col-md-12 col-sm-12 mb-2">
        <input type="checkbox" class="form-check-input driver_is_active" name="driver_is_active" id="driver_is_active" onclick="return false" value="1"
          {{ $driver->is_active == 1 ? 'checked' : '' }}>
        <label class="form-label">{{ __('index.active') }}</label>
      </div>

      <div class="col-12 mb-2">
        <div class="line text-center"></div>
      </div>
    </div>
    @if (isset($driver_bank_details))
      {{-- Bank Information --}}
      <div class="row">
        <h5>{{ __('index.bank_information') }} </h5>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.driver_bank_name') }} <span class="text-danger">*</span></label>
          <input type="text" class="form-control mb-2 toggleElement" name="driver_bank_name" id="driver_bank_name"
            value="{{ old('driver_bank_name', $driver_bank_details->driver_bank_name) ?? '' }}" readonly />
          @error('driver_bank_name')
            <span class="text-danger backend-error">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.driver_bank_account_number') }} <span class="text-danger">*</span></label>
          <input type="text" class="form-control mb-2 toggleElement" name="driver_bank_account_number" id="driver_bank_account_number"
            value="{{ old('driver_bank_account_number', $driver_bank_details->driver_bank_account_number) ?? '' }}" readonly />
          @error('driver_bank_account_number')
            <span class="text-danger backend-error">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.driver_bank_ifsc_code') }} <span class="text-danger">*</span></label>
          <input type="text" class="form-control mb-2 toggleElement" name="driver_bank_ifsc_code" id="driver_bank_ifsc_code"
            value="{{ old('driver_bank_ifsc_code', $driver_bank_details->driver_bank_ifsc_code) ?? '' }}" readonly />
          @error('driver_bank_ifsc_code')
            <span class="text-danger backend-error">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.driver_bank_branch_name') }} <span class="text-danger">*</span> </label>
          <input type="text" class="form-control mb-2 toggleElement" name="driver_bank_branch_name" id="driver_bank_branch_name"
            value="{{ old('driver_bank_branch_name', $driver_bank_details->driver_bank_branch_name) ?? '' }}" readonly />
          @error('driver_bank_branch_name')
            <span class="text-danger backend-error">{{ $message }}</span>
          @enderror
        </div>


        <div class="col-12 mb-2">
          <div class="line text-center"></div>
        </div>
      </div>
    @endif

    @if ($documentDetails === 1)
      {{-- Personal Document Information --}}
      <div class="row">

        @if (isset($driver_personal_document) && !empty($driver_personal_document))
          <h5>{{ __('index.personal_documents') }}</h5>
          @foreach ($driver_personal_document as $key => $value)
            <div class="row align-items-start">
              <input type="hidden" class="form-control" name="document_id[]" id="document_id" value="{{ $value->id ?? '' }}" readonly>

              <div class="col-12 mb-2">
                <label class="form-label" style="font-size:1.1rem">{{ Str::title(str_replace('_', ' ', $value->document_type ?? '')) }}</label>
                <input type="hidden" name="document_type[]" value="{{ $value->document_type ?? '' }}[]" id="document_type" />
              </div>

              <div class="col-lg-4 col-md-12 col-sm-12 mb-2 {{ $value->document_type == 'bank_passbook_or_cancel_cheque' ? 'd-none' : '' }}">
                <label class="form-label">{{ Str::title(str_replace('_', ' ', $value->document_type ?? '')) }} {{ __('index.number') }}</label>
                <input data-key="{{ $key }}" type="{{ $value->document_type == 'bank_passbook_or_cancel_cheque' ? 'hidden' : 'text' }}" class="document_number form-control "
                  name="document_number[]" id="document_number" data-document-type="{{ $value->document_type }}" value="{{ $value->document_number ?? '' }}" readonly>
                <div class="col-12">
                  <div class="document-number-error-{{ $key }}"></div>
                </div>
              </div>

              <div class="col-lg-4 col-md-12 col-sm-12 mb-2 uploadDocs d-none">
                <label class="form-label">{{ __('index.upload') }} {{ Str::title(str_replace('_', ' ', $value->document_type ?? '')) }}
                  @if ($value->document_type == 'aadhar_card')
                    {{ __('index.front') }}
                  @endif
                </label>
                <input type="file" data-key="{{ $key }}" class="form-control file-upload" name="document_1_upload[]" accept=".jpg, .jpeg, .png, .pdf, .docx">
                <div class="col-12">
                  <div class="document-1-upload-error-{{ $key }}">
                    <!-- Error message will be injected dynamically here -->
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-12 col-sm-12 mb-2 uploadDocs d-none">

                <label class="form-label {{ $value->document_type != 'aadhar_card' ? 'd-none' : '' }}">{{ __('index.upload') }} {{ Str::title(str_replace('_', ' ', $value->document_type ?? '')) }}
                  {{ __('index.back') }}</label>
                <input type="file" data-key="{{ $key }}" class="form-control {{ $value->document_type != 'aadhar_card' ? 'd-none' : '' }} file-upload" name="document_2_upload[]"
                  accept=".jpg, .jpeg, .png, .pdf, .docx">
                <div class="col-12 {{ $value->document_type != 'aadhar_card' ? 'd-none' : '' }}">
                  <div class="document-2-upload-error-{{ $key }}">
                    <!-- Error message will be injected dynamically here -->
                  </div>
                </div>
              </div>
            </div>
            <div class="row align-items-start">
              <div class="col-lg-4 col-md-6 col-sm-12 mb-2 backend-image-file">
                @php
                  $defaultImage = asset('/assets/img/docs-placeholder.png');
                @endphp
                @if (isset($value->document_1) && $value->document_1 != '')
                  @php
                    $filePath = asset('storage/' . $value->document_1);

                    $viewButton =
                        '<button class="btn btn-outline-info mx-2 btn-sm view-document-btn" data-url="' .
                        url('storage-bucket?path=' . $value->document_1) .
                        '" data-document-type="' .
                        $value->document_1_file_type .
                        '" type="button">View</button>';
                  @endphp

                  @if (in_array($value->document_1_file_type, ['jpg', 'jpeg', 'png']))
                    <!-- Display the image for JPEG, JPG, or PNG -->
                    <img class="img-radius" id="showImage" src="{{ $filePath }}" onerror="this.onerror=null;this.src='{{ $defaultImage }}';" height="60" width="60" />
                    {!! $viewButton !!}
                  @elseif (in_array($value->document_1_file_type, ['pdf', 'docx']))
                    <!-- Display the basename for PDF and DOCX -->
                    <p>{{ basename($value->document_1) }}</p>
                    {!! $viewButton !!}
                  @else
                    <p>Unsupported file type.</p>
                  @endif

                  <a data-document-count="1" data-url="{{ url('driver/pending/personal-document/delete/' . $value->id) }}" class="text-danger deletePersonalDocument d-none mx-2">
                    <span class="fas fa-trash-alt"></span>
                  </a>
                @else
                  <!-- No document uploaded -->
                  <img src="{{ $defaultImage }}" alt="No document" height="60" width="60" class="img-radius" />
                  <small class="text-muted">No document uploaded</small>
                @endif
              </div>
              @if ($value->document_type == 'aadhar_card')
                <div class="col-lg-4 col-md-6 col-sm-12 mb-2 backend-image-file">
                  @php
                    $defaultImage = asset('/assets/img/docs-placeholder.png');
                  @endphp
                  @if (isset($value->document_2) && $value->document_2 != '')
                    @php
                      $filePath = asset('storage/' . $value->document_2);

                      $viewButton =
                          '<button class="btn btn-outline-info mx-2 btn-sm view-document-btn" data-url="' .
                          url('storage-bucket?path=' . $value->document_2) .
                          '" data-document-type="' .
                          $value->document_2_file_type .
                          '" type="button">View</button>';
                    @endphp

                    @if (in_array($value->document_2_file_type, ['jpg', 'jpeg', 'png']))
                      <!-- Display the image for JPEG, JPG, or PNG -->
                      <img class="img-radius" id="showImage" src="{{ $filePath }}" onerror="this.onerror=null;this.src='{{ $defaultImage }}';" height="60" width="60" />
                      {!! $viewButton !!}
                    @elseif (in_array($value->document_2_file_type, ['pdf', 'docx']))
                      <!-- Display the basename for PDF and DOCX -->
                      <p>{{ basename($value->document_2) }}</p>
                      {!! $viewButton !!}
                    @else
                      <p>Unsupported file type.</p>
                    @endif

                    <a data-document-count="2" data-url="{{ url('driver/pending/personal-document/delete/' . $value->id) }}" class="text-danger deletePersonalDocument d-none mx-2">
                      <span class="fas fa-trash-alt"></span>
                    </a>
                  @else
                    <!-- No document uploaded -->
                    <img src="{{ $defaultImage }}" alt="No document" height="60" width="60" class="img-radius" />
                    <small class="text-muted">No document uploaded</small>
                  @endif
                </div>
              @endif

            </div>
          @endforeach
        @endif

        <div class="col-12 mb-2">
          <div class="line text-center"></div>
        </div>
      </div>

      <!-- Vehicle Document Information -->

      @if (isset($driver_vehicle_document_deatils) && !empty($driver_vehicle_document_deatils))
        <h5>{{ __('index.vehicle_documents') }}</h5>
        @foreach ($driver_vehicle_document_deatils as $key => $value)
          <div class="row align-items-start">

            <input type="hidden" name="vehicle_document_id[]" value="{{ $value->id ?? '' }}" readonly>
            <div class="col-12 mb-2">
              <label class="form-label" style="font-size:1.1rem">{{ Str::title(str_replace('_', ' ', $value->document_type ?? '')) }}</label>
              <input type="hidden" name="vehicle_document_type[]" value="{{ $value->document_type ?? '' }}[]" id="vehicle_document_type" />
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
              <label class="form-label">{{ Str::title(str_replace('_', ' ', $value->document_type ?? '')) }} {{ __('index.number') }}</label>
              <input type="text" data-key="{{ $key }}" data-document-type="{{ $value->document_type }}" class="form-control vehicle_document_number" name="vehicle_document_number[]"
                value="{{ $value->document_number ?? '' }}" readonly>
              <div class="col-12">
                <div class="vehicle-document-number-error-{{ $key }}">
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-2 uploadDocs d-none">
              <label class="form-label">{{ __('index.upload') }} {{ Str::title(str_replace('_', ' ', $value->document_type ?? '')) }}</label>
              <input type="file" class="form-control file-upload" name="vehicle_document_upload[]" accept=".jpg, .jpeg, .png, .pdf, .docx">
              <div class="col-12">
                <div class="vehicle-document-upload-error-{{ $key }}">
                  <!-- Error message will be injected dynamically here -->
                </div>
              </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 mb-2 backend-image-file">
              @php
                $defaultImage = asset('/assets/img/docs-placeholder.png');
              @endphp
              @if (isset($value->document_file_path) && $value->document_file_path != '')
                @php
                  $filePath = asset('storage/' . $value->document_file_path);

                  $viewButton =
                      '<button class="btn btn-outline-info mx-2 btn-sm view-document-btn" data-url="' .
                      url('storage-bucket?path=' . $value->document_file_path) .
                      '" data-document-type="' .
                      $value->document_file_type .
                      '" type="button">View</button>';
                @endphp

                @if (in_array($value->document_file_type, ['jpg', 'jpeg', 'png']))
                  <!-- Display the image for JPEG, JPG, or PNG -->
                  <img class="img-radius" id="showImage" src="{{ $filePath }}" onerror="this.onerror=null;this.src='{{ $defaultImage }}';" height="60" width="60" />
                  {!! $viewButton !!}
                @elseif (in_array($value->document_file_type, ['pdf', 'docx']))
                  <!-- Display the basename for PDF and DOCX -->
                  <p>{{ basename($value->document_file_path) }}</p>
                  {!! $viewButton !!}
                @else
                  <p>Unsupported file type.</p>
                @endif

                <a data-url="{{ url('driver/pending/vehicle-document/delete/' . $value->id) }}" class="text-danger deleteVehicleDocument d-none mx-2">
                  <span class="fas fa-trash-alt"></span>
                </a>
              @else
                <!-- No document uploaded -->
                <img src="{{ $defaultImage }}" alt="No document" height="60" width="60" class="img-radius" />
                <small class="text-muted">No document uploaded</small>
              @endif
            </div>
          </div>
        @endforeach
      @endif


      {{-- Verification Status Block --}}
      <div class="row verification_status">
        <div class="col-12 mb-2">
          <div class="line text-center"></div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.verification_status') }} <span class="text-danger">*</span> </label>
          <select class="form-control custom-select select2" name="personal_verification_status" id="personal_verification_status" aria-label="verification status">
            <option value="">Select Status</option>
            <option value="approve" {{ $driver->driver_document_verification_status == 1 ? 'selected' : '' }}>Approve</option>
            <option value="reject" {{ $driver->driver_document_verification_status == 2 ? 'selected' : '' }}>Reject</option>
          </select>
        </div>
        <div class="col-lg-12 col-md-6 col-sm-12 mb-2">
          <label class="form-label">{{ __('index.verification_reason') }} <span
              class="text-danger {{ $driver->driver_document_verification_status == '2' ? '' : 'd-none' }} personal_verification_reason_required">*</span> </label>
          <textarea class="form-control mb-2" name="personal_verification_reason" id="personal_verification_reason">{{ old('personal_verification_reason', $driver_personal_document[0]->document_verification_reason ?? '') }}</textarea>
        </div>
      </div>
    @else
      <p>The user has not completed the document upload process.</p>
    @endif
  </div>
  @if ($documentDetails === 1)
    <div class="card-footer p-2 text-end bg-light">
      <button class="btn btn-primary btn-sm" id="verify_btn" type="button">Verify</button>
      <button class="btn btn-primary btn-sm d-none" id="update_btn" type="button">Update</button>
    </div>
  @endif
</form>



{{-- Modal For View Image Document --}}
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="documentModalLabel">Document Viewer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="imageContainer">
          <img id="documentImage" src="" style="width: 100%;" alt="Document Image">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal For View PDF, DOCX Document --}}
<div class="modal fade" id="documentModalPDF" tabindex="-1" aria-labelledby="documentModalLabelPDF" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="documentModalLabelPDF">Document Viewer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Iframe for displaying PDF -->
        <iframe id="documentIframe" src="" style="width: 100%; height:70vh;" frameborder="0"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
