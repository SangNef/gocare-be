@extends("la.layouts.app")

@section("contentheader_title", "Ngân hàng")
@section("contentheader_description", "Danh sách ngân hàng")
@section("section", "Ngân hàng")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Danh sách ngân hàng")

@section("headerElems")
@la_access("Banks", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm ngân hàng</button>
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

@include('la.datatable.index', ['id' => 'banks', 'path' => 'bank_dt_ajax', 'cols' => [
    [
        'title' => 'ID',
        'field' => 'id',
    ],
    [
        'title' => 'Kho',
        'field' => 'store_id',
    ],
    [
        'title' => 'Tên',
        'field' => 'name',
    ],
    [
        'title' => 'Chi nhánh',
        'field' => 'branch',
    ],
    [
        'title' => 'Chủ tài khoản',
        'field' => 'acc_name',
    ],
    [
        'title' => 'Số tài khoản',
        'field' => 'acc_id',
    ],
    [
        'title' => 'Hiển thị khi in ĐH',
        'field' => 'printing'
    ],
    [
        'title' => 'Số dư đầu',
        'field' => 'first_balance',
    ],
    [
        'title' => 'Số dư cuối',
        'field' => 'last_balance',
    ],
    [
    	'title' => 'Tồn đọng',
    	'field' => 'backlogs'
    ],
],
'totals' => [
    'last_balance' => 'Tổng số dư',
]])

@la_access("Banks", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm ngân hàng</h4>
			</div>
			{!! Form::open(['action' => 'LA\BanksController@store', 'id' => 'bank-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">					
                    @la_input($module, 'name')
                    <div class="form-group">
                        <label for="currency_type" style="margin-right: 20px">Loại tiền tệ :</label>
                        <label style="margin-right: 10px"><input type="radio" value="1" name="currency_type" checked>VNĐ</label>
                        <label><input type="radio" value="2" name="currency_type">NDT</label>
                    </div>
					@la_input($module, 'branch')
					@la_input($module, 'acc_name')
					@la_input($module, 'acc_id')
					@if (!auth()->user()->store_id)
						<div class="form-group">
							<label for="status" style="margin-right: 20px">Kho :</label>
							<select name="store_id" class="form-control ajax-select" model="stores">

							</select>
						</div>
					@endif
					<div class="form-group">
                        <label for="first_balance">Số dư đầu*:</label>
                        <input type="text" value="0" name="first_balance" id="first_balance" class="form-control currency valid" aria-invalid="false"/>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
				{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endla_access

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
$(function () {
	$("#bank-add-form").validate({
		
	});
});
</script>
@endpush
