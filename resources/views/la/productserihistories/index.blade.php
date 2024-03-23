@extends("la.layouts.app")

@section("contentheader_title", "Quản lý mã kích hoạt")
@section("contentheader_description", "")
@section("section", "Lịch sử mã seri")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Mã kích hoạt")

@section("headerElems")
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

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<table id="example1" class="table table-bordered">
		<thead>
		<tr class="success">
			<th>Seri</th>
			<th>Mã kích hoạt</th>
			<th>Sản phẩm</th>
			<th>Loại mã</th>
			<th>Khách hàng</th>
			<th>Mã đơn hàng</th>
			<th>Ngày thanh toán</th>
			<th>Trạng thái thanh toán</th>
			<th>Ngày kích hoạt</th>
			<th>Trạng thái kích hoạt</th>
		</tr>
		</thead>
		<tbody>
			
		</tbody>
		</table>
	</div>
</div>


@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
$(function () {
	$("#example1").DataTable({
		processing: true,
        serverSide: true,
        ajax: "{{ url(config('laraadmin.adminRoute') . '/productserihistory_dt_ajax') }}",
		language: {
			lengthMenu: "_MENU_",
			search: "_INPUT_",
			searchPlaceholder: "Search"
		}
	});
});
</script>
@endpush
