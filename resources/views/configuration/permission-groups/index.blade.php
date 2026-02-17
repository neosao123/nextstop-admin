@extends('layout.master', ['pageTitle' => __('index.permissions_groups')])
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
                <span class="bg-200 dark__bg-1100 pe-3">{{__('index.permissions_groups')}}</span>
                <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-index--1"></span>
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if(Auth::guard('admin')->user()->can('Dashboard.View', 'admin'))
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-dark">{{__('index.dashboard')}}</a></li>
                    @endif
                    <li class="breadcrumb-item">{{__('index.configuration')}}</li>
                    <li class="breadcrumb-item active">{{__('index.permissions_groups')}}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
   @if(Auth::guard('admin')->user()->can('PermissionGroup.Create', 'admin'))
    <div class="col-xl-4 col-lg-4 col-md-5">
        <form action="{{ url('configuration/permission-groups/store') }}" method="post">
            @csrf
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">{{__('index.create_permissions_groups')}}</h6>
                </div>
                <div class="card-body row gx-3">
                    <div class="col-12 mb-3">
                        <label class="form-label" for="name">{{__('index.group_name')}} <span class="text-danger">*</span></label>
                        <input class="form-control" name="group_name" id="group_name" />
                        @error('group_name')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label" for="name">{{__('index.slug')}} <span class="text-danger">*</span></label>
                        <input class="form-control" name="slug" id="slug" readonly />
                        @error('slug')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button class="btn btn-primary" type="submit">{{__('index.submit')}} </button>
                </div>
            </div>
        </form>
    </div>
   @endif
   @if(Auth::guard('admin')->user()->can('PermissionGroup.List', 'admin'))
    <div class="col-xl-8 col-lg-8 col-md-7">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">{{__('index.permissions_groups_list')}}</h6>
            </div>
            <div class="card-body">
                <table class="table table-condensed table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{__('index.permissions_group_name')}}</th>
                            <th>{{__('index.slug')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissionGroups as $item)
                        <tr class="mb-3">
                            <td>
                                {{ ucwords($item->group_name) }}
                            </td>
                            <td>
                                {{ $item->slug }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                {{ $permissionGroups->links() }}
            </div>
        </div>
    </div>
   @endif
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        $('#group_name').on('input', function() {
            const groupName = $(this).val().trim();
            const slug = groupName.toLowerCase().replace(/\s+/g, '-');
            $('#slug').val(slug);
        });
    });
</script>
@endpush