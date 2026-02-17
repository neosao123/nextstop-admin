@extends('layout.master', ['pageTitle' => __('index.permissions')])
@push('styles')
@endpush
@section('content')
  <div class="d-flex mb-4 mt-1">
    <span class="fa-stack me-2 ms-n1">
      <i class="fas fa-circle fa-stack-2x text-300"></i>
      <i class="fa-inverse fa-stack-1x text-primary fas fa-film" data-fa-transform="shrink-2"></i>
    </span>
    <div class="col">
      <div class="">
        <h5 class="mb-0 text-primary position-relative">
          <span class="bg-200 dark__bg-1100 pe-3">{{ __('index.permissions') }}</span>
          <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
        </h5>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            @if (Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{ __('index.dashboard') }}</a></li>
            @endif
            <li class="breadcrumb-item">{{ __('index.configuration') }}</li>
            <li class="breadcrumb-item active">{{ __('index.permissions') }}</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <div class="row">
    @if (Auth::guard('admin')->user()->can('Permissions.Create', 'admin'))
      <div class="col-xl-4 col-lg-4 col-md-5">
        <div class="card mb-3">
          <form action="{{ url('configuration/permissions/store') }}" method="post">
            @csrf
            <div class="card-header bg-light">
              <h6 class="mb-0">{{ __('index.permission_create') }}</h6>
            </div>
            <div class="card-body row gx-3">
              <div class="col-12 mb-3">
                <label class="form-label" for="group">{{ __('index.group') }}<span class="text-danger">*</span></label>
                <select class="form-select" name="group" id="group">
                  <option value="">---</option>
                  @for ($i = 0; $i < $groups->count(); $i++)
                    <option value="{{ $groups[$i]->group_name }}" data-id="{{ $groups[$i]->id }}">{{ $groups[$i]->group_name }}</option>
                  @endfor
                </select>
                @error('group')
                  <div class="text-danger">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 mb-3">
                <label class="form-label" for="section">{{ __('index.section') }} <span class="text-danger">*</span></label>
                <select class="form-select" name="section" id="section">
                  <option value="">---</option>
                  <option value="List">{{ __('index.list') }}</option>
                  <option value="Create">{{ __('index.create') }}</option>
                  <option value="Edit">{{ __('index.edit') }}</option>
                  <option value="View-Edit">{{ __('index.view_edit') }}</option>
                  <option value="Create-Update">{{ __('index.create-update') }}</option>
                  <option value="Delete">{{ __('index.delete') }}</option>
                  <option value="View">{{ __('index.view') }}</option>
                  <option value="Export">{{ __('index.export') }}</option>
                  <option value="Import">{{ __('index.import') }}</option>
                  <option value="Permissions">{{ __('index.users-permissions') }}</option>
                  <option value="Block">{{ __('index.users_block') }}</option>
				  <option value="Change-Status">{{ __('index.change_status') }}</option>
				  <option value="Rating">{{ __('index.rating') }}</option>
				  <option value="Wallet-Transaction">{{ __('index.wallet_transaction') }}</option>
				  <option value="Wallet">{{ __('index.wallet') }}</option>
				  <option value="Refund-Amount">{{ __('index.refund_amount') }}</option>
                </select>
                @error('section')
                  <div class="text-danger">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 mb-3">
                <label class="form-label" for="permission">{{ __('index.permission') }}</label>
                <input type="hidden" class="form-control" name="group_id" id="group_id" readonly>
                <input type="text" class="form-control" name="permission" id="permission" readonly disabled>
              </div>
            </div>
            <div class="card-footer bg-light text-end">
              <button class="btn btn-primary" type="submit">{{ __('index.submit') }}</button>
            </div>
          </form>
        </div>
      </div>
    @endif
    @if (Auth::guard('admin')->user()->can('Permissions.List', 'admin'))
      <div class="col-xl-8 col-lg-8 col-md-7">
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0">{{ __('index.permissions_list') }}</h6>
          </div>
          <div class="card-body">
            <table class="table table-bordered table-stripped">
              <thead>
                <tr>
                  <td>{{ __('index.group') }}</td>
                  <td>{{ __('index.permission') }}</td>
                  <!--<th align="right">Acttions</th>-->
                </tr>
              </thead>
              <tbody>
                @forelse ($permissions as $item)
                  <tr>
                    <td>{{ $item->group_name }}</td>
                    <td>{{ $item->name }}</td>
                    <!-- <td><button class="btn btn-sm btn-outline-danger" data-id="{{ $item->id }}">Delete</button></td>-->
                  </tr>
                @empty
                  <tr>
                    <th colspan="3">
                      No Permissions Found Yet
                    </th>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            {{ $permissions->links() }}
          </div>
        </div>
      </div>
    @endif
  </div>
@endsection
@push('scripts')
  <script>
    $(document).on("change", "select.form-select", function() {
      const group = $("select#group").val();
      const section = $("select#section").val();
      if (group && section) {
        $("#group_id").val($("select#group").find("option:selected").data("id"));
        $("#permission").val(`${group}.${section}`);
      }
    });
  </script>
@endpush
