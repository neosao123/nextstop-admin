@php
  $pageTitle = __('index.view_driver_document_details');
@endphp
@push('styles')
  <style>
    .modal-body {
      padding: 5px;
      height: 70vh;
      overflow-y: auto;
      scrollbar-width: thin;
    }


    #imageContainer img {
      display: block;
      max-width: 100%;
      height: 100%;
    }
  </style>
   <link href="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />

@endpush
@extends('layout.master')
@section('content')
  <div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
      <i class="fas fa-circle fa-stack-2x text-300"></i>
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.view_driver_document_details') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.view_driver_document_details') }}</li>
        </ol>
      </nav>
    </div>
    <div class="col-auto ms-2 align-items-center">
      <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('driver-document-details') }}">
        <span class="px-2">{{ __('index.back') }}</span>
      </a>
    </div>
  </div>
  {{-- @if (Auth::guard('admin')->user()->can('driver.View')) --}}
  <!-- driver View -->
  <div class="col-lg-12">
    <div class="card mb-3">
      <form>
        <div class="card-header bg-light">
          <h5 class="mb-0" id="form-title">{{ __('index.view_driver_document_details') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.porter_first_name') }} </label>
              <input type="hidden" class="form-control mb-2" name="porter_id" id="porter_id" value="{{ $driver_document_details->id ?? '' }}" readonly />
              <input type="text" class="form-control mb-2" name="porter_first_name" id="porter_first_name" value="{{ $driver_document_details->Driver->porter_first_name ?? '' }}" readonly />
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.porter_last_name') }} </label>
              <input type="text" class="form-control mb-2" name="porter_last_name" id="porter_last_name" value="{{ $driver_document_details->Driver->porter_last_name ?? '' }}" readonly />
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.document_type') }} </label>
              <input type="text" class="form-control mb-2" name="document_type" id="document_type" value="{{ $driver_document_details->document_type ?? '' }}" readonly />
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.document_number') }}</label>
              <input type="text" class="form-control mb-2" name="document_number" id="document_number" value="{{ $driver_document_details->document_number ?? '' }}" readonly />
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.document_file') }}</label>
              <div class="mb-2">
                @php
                  $filePath = $driver_document_details->document_file_path;
                  $fileUrl = $filePath ? asset('storage/document/' . $filePath) : asset('/assets/img/docs-placeholder.png');
                  $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                @endphp

                @if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']) && $filePath)
                  <img class="img-radius" id="documents" src="{{ $fileUrl }}" height="75" width="75" />
                  <div class="mb-3">
                    <button class="btn btn-outline-info me-1 mb-1 view-document-btn" data-url="{{ $fileUrl }}" type="button">View</button>
                  </div>
                @elseif($fileExtension === 'pdf' && $fileUrl)
                  <iframe class="img-radius" id="documents" src="{{ $fileUrl }}" height="75" width="75" style="border: none;"></iframe>
                  <div class="mb-3">
                    <button class="btn btn-outline-info me-1 mb-1 view-document-pdf-btn" data-url="{{ $fileUrl }}" type="button">View</button>
                  </div>
                @elseif(in_array($fileExtension, ['doc', 'docx']) && $fileUrl)
                  <iframe class="img-radius" id="documents" src="https://docs.google.com/gview?url={{ $fileUrl }}&embedded=true" height="75" width="75" style="border: none;"></iframe>
                  <div class="mb-3">
                    <button class="btn btn-outline-info me-1 mb-1 view-document-pdf-btn" data-url="{{ $fileUrl }}" type="button">View</button>
                  </div>
                @else
                  <img class="img-radius" id="documents" src="{{ asset('/assets/img/docs-placeholder.png') }}" height="75" width="75" />
                @endif
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
              <label class="form-label">{{ __('index.document_verification_status') }}</label>
              <select class="form-select mb-2" name="document_verification_status" id="document_verification_status">
                <option value="0" {{ $driver_document_details->document_verification_status == 0 ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ $driver_document_details->document_verification_status == 1 ? 'selected' : '' }}>Approved</option>
                <option value="2" {{ $driver_document_details->document_verification_status == 2 ? 'selected' : '' }}>Rejected</option>
              </select>
            </div>

          </div>
        </div>
    </div>
  </div>
  </div>
  {{-- @endif --}}
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
@endsection
@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}";
  </script>
 <script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatable1.13.8/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/vendors/datatable1.13.8/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('site-plugins/driver-document-details/view.js?v=' . time()) }}"></script>
@endpush
