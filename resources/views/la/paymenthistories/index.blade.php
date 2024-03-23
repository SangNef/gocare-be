@extends("la.layouts.app")

@section("contentheader_title", "Lịch sử thanh toán online")
@section("contentheader_description", "")
@section("section", "Lịch sử thanh toán online")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Lịch sử thanh toán online")

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

@include('la.partials.created_filter', [
    'useExtraFilter' => true,
	'filterCreatedDate' => true,
	'filterDate' => true,
	'restoreState' => true,
'totals' => ['total_amount' => 'Tổng tiền giao dịch'],
'filterColumns' => [],
'filterOptions' => [
'status' => [
1 => 'Đang xử lý',
3 => 'Thành công',
4 => 'Thất bại',
],
],
])

<div class="box box-success">
    <!-- <div class="box-header"></div> -->
    <div class="box-body">
        <table id="example1" class="table table-bordered">
            <thead>
                <tr class="success">
                    <th>id</th>
                    <th>Mã đơn hàng</th>
                    <th>Số tiền</th>
                    <th>Thời gian thanh toán</th>
                    <th>Trạng thái thanh toán</th>
                    <th>Phương thức thanh toán</th>
                    <th>Seri - Mã kích hoạt</th>
                </tr>
            </thead>
            <thead id="filter_bar">
                <tr class="success">
                    <th colname="{!! isset($module->fields['id']) ? $module->fields['id']['colname'] : 'id' !!}">
                        {{ $module->fields['id']['label'] or ucfirst('id') }}
                    </th>
                    <th colname="{!! isset($module->fields['order_id']) ? $module->fields['order_id']['colname'] : 'order_id' !!}">
                        {{ $module->fields['order_id']['label'] or ucfirst('order_id') }}
                    </th>
                    <th colname="{!! isset($module->fields['request']) ? $module->fields['request']['colname'] : 'request' !!}">
                        {{ $module->fields['request']['label'] or ucfirst('request') }}
                    </th>
                    <th colname="{!! isset($module->fields['created_at']) ? $module->fields['created_at']['colname'] : 'created_at' !!}">
                        {{ $module->fields['created_at']['label'] or ucfirst('created_at') }}
                    </th>
                    <th colname="{!! isset($module->fields['status']) ? $module->fields['status']['colname'] : 'status' !!}">
                        {{ $module->fields['status']['label'] or ucfirst('status') }}
                    </th>
                    <th colname="{!! isset($module->fields['provider']) ? $module->fields['provider']['colname'] : 'provider' !!}">
                        {{ $module->fields['provider']['label'] or ucfirst('provider') }}
                    </th>
                    <th colname="{!! isset($module->fields['request']) ? $module->fields['request']['colname'] : 'request' !!}">
                        {{ $module->fields['request']['label'] or ucfirst('request') }}
                    </th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
    $(function() {
        var table = $("#example1").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url(config('laraadmin.adminRoute') . '/paymenthistory_dt_ajax') }}",
            language: {
                lengthMenu: "_MENU_",
                search: "_INPUT_",
                searchPlaceholder: "Search"
            },
        });
    });
</script>
@endpush


@push('scripts')
<script>
    var url = "{{ url(config('laraadmin.adminRoute') . '/paymenthistory_dt_ajax') }}";
</script>
@endpush