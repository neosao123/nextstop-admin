@php
  $pageTitle = __('index.import_goods_type');
@endphp
@extends('layout.master')
@push('styles')
  <style>
    .table-responsive {
      scrollbar-width: thin
    }
  </style>
  <link rel="stylesheet" href="{{ url('theme/css/parsely.css') }}">
  <link href="{{ url('theme/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ url('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
  <div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
      <i class="fas fa-circle fa-stack-2x text-300"></i>
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <h5 class="mb-0 text-primary position-relative"><span class="bg-200 dark__bg-1100 pe-3">{{ __('index.import_goods_type') }}</span><span
          class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span></h5>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          @if (Auth::guard('admin')->user()->can('Dashboard.View'))
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
          @endif
          @if (Auth::guard('admin')->user()->can('Goods-Type.List'))
            <li class="breadcrumb-item"><a href="{{ url('/goods-type') }}" class="text-decoration-none text-dark">{{ __('index.goods_type') }}</a></li>
          @endif
          <li class="breadcrumb-item active" aria-current="page">{{ __('index.import_goods_type') }}</li>
        </ol>
      </nav>
    </div>
    @if (Auth::guard('admin')->user()->can('Goods-Type.List'))
      <div class="col-auto ms-2 align-items-center">
        <a class="btn btn-falcon-default btn-sm me-1 mb-1" href="{{ url('goods-type') }}">
          <span class="px-2">{{ __('index.back') }}</span>
        </a>
      </div>
    @endif

  </div>

  <div class="row gx-3">
    <div class="col-lg-12">
      <div class="card mb-3">
        <div class="card-header bg-light d-flex">
          <div class="col">
            <h5 class="mb-0">{{ __('index.import') }}</h5>
          </div>
          <div class="col-auto ms-2 align-items-center">
            <a class="btn btn-falcon-danger  btn-sm me-1 mb-1" href="{{ asset('assets/templates/Template - Goods Type.xlsx') }}" download="Template - Goods Type.xlsx">
              <span class="px-2">{{ __('index.get_excel_template') }}</span>
            </a>
          </div>
        </div>

        <div class="card-body">
          <div class="text-danger">
            <p class="text-danger"><strong>{{ __('index.note') }}</strong></p>
            <ul class="" type="">
              <li>
                <p class="mb-2">{{ __('index.make_sure_you_only_select_excel_template_and_please_make_sure_you_have_prepared_as_per_the_template_provided') }}</p>
              </li>
              <li>
                <p class="mb-2">{{ __('index.the_status_field_always_contains') }} <strong>active</strong> {{ __('index.or') }} <strong>inactive</strong>
                  {{ __('index.keyword') }}</p>
              </li>
            </ul>
          </div>
          <form action="{{ url('/goods-type/upload/excel') }}" method="post" enctype="multipart/form-data" data-parsley-validate="" id="matform">
            @csrf
            <div class="row">
              <div class="col-md-6 form-group">
                <label>{{ __('index.select_excel_file') }}</label>
                <div class="form-group">
                  <input type="file" class="form-control" name="uploadFile" id="uploadFile" required />
                </div>
              </div>
              <div class="col-md-6 form-group mt-2">
                <label></label>
                <div class="form-group">
                  <button class="btn btn-primary" type="button" id="upload" onclick="Upload()">{{ __('index.upload_excel_file') }}</button>
                  <button class="btn btn-danger" type="button" onclick="window.location.reload();">{{ __('index.reset') }}</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="card mb-3 d-none" id="goodstypeList">
        <div class="card-header bg-light">
          <h5 class="mb-0">{{ __('index.goods_type') . ' ' . __('index.list') }}</h5>
        </div>
        <div id="successMsg"></div>
        <form id="submitForm" method="post" name="s" enctype="multipart/form-data">
          @csrf
          <div class="card-body bg-white">
            <div id="message1" class="mb-3" style="display:none ">
              <p class="text-danger"><strong>{{ __('index.the_following_rows_having_issues_please_check') }}</strong></p>
              <p id="validMsgs"></p>
            </div>
            <div id="dvExcel" class="table-responsive"></div>
            <div class="progress mb-3" style="height:16px;">
              <div id="file-progress-bar" class="progress-bar progress-bar-striped active"></div>
            </div>
          </div>
          <div class="card-footer bg-light m-0 p-2 text-end">
            <button type="submit" name="submit" id="dataSubmit" class="btn btn-success">{{ __('index.save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    var baseUrl = "{{ url('/') }}";
    var csrfToken = "{{ csrf_token() }}"
  </script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/xlsx.full.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/jszip.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script type="text/javascript" src="{{ url('theme/js/moment.js?v=' . time()) }}"></script>
  <script type="text/javascript" src="{{ url('theme/js/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ url('theme/js/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ url('theme/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>
  <script src="{{ asset('site-plugins/goods-type/import.js?v=' . time()) }}"></script>
@endpush
