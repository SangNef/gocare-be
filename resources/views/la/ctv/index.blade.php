@extends("la.layouts.app")

@section("contentheader_title", trans('messages.customers'))
@section("section", trans('messages.customers'))
@section("sub_section", trans('messages.customers'))
@section("htmlheader_title", trans('messages.customers'))

@section("headerElems")
@la_access("Customers", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">{{ trans('messages.add-customer')  }}</button>
    <a href="{{ route('customer.export') }}" class="btn btn-warning btn-sm pull-right mr-1" id="export-customer">Export</a>
@endla_access
@endsection

@section("main-content")

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('la.datatable.index', ['id' => 'users', 'path' => 'ctv_dt_ajax', 'cols' => [
    [
        'title' => 'ID',
        'field' => 'id',
    ],[
        'title' => 'Kho',
        'field' => 'store_id',
    ],
    [
        'title' => 'Tên',
        'field' => 'name',
    ],
    [
        'title' => 'Email',
        'field' => 'email',
    ],
    [
        'title' => 'Số điện thoại',
        'field' => 'phone',
    ],
    [
        'title' => 'Địa chỉ',
        'field' => 'address',
    ],
    [
        'title' => 'Số dư khoá',
        'field' => 'lock_commission',
    ],
    [
        'title' => 'Số dư',
        'field' => 'commission',
    ],
],
    'totals' => [
    'lock_commission' => 'Tổng số dư khoá',
    'commission' => 'Tổng số dư',
]])


@endsection

@push('styles')

@endpush

@push('scripts')
<script>
var url = "{{ url(config('laraadmin.adminRoute') . '/transaction_dt_ajax') }}";
$(function () {
    $('#province').change(function() {
        let id = $(this).find('option:selected').val();
        $('#district,#ward').find('option').not('.selected').remove();
        getAddress(id, 'province', '#district', '{{ route('customer.get-address') }}');
    });

    $('#district').change(function() {
        let id = $(this).find('option:selected').val();
        $('#ward').find('option').not('.selected').remove();
        getAddress(id, 'district', '#ward', '{{ route('customer.get-address') }}');
    });
    $('#store_id').change(function () {
        $('#group_id').val('').change();
    })
});
</script>
@endpush
