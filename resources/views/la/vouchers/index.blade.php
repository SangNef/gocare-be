@extends("la.layouts.app")

@section("contentheader_title", "Voucher giảm giá")
@section("contentheader_description", "Danh sách voucher giảm giá")
@section("section", "Voucher giảm giá")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Danh sách voucher giảm giá")

@section("headerElems")
@la_access("Vouchers", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm Voucher</button>
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

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<table id="example1" class="table table-bordered">
		<thead>
		<tr class="success">
			@foreach( $listing_cols as $col )
			<th>{{ $module->fields[$col]['label'] or ucfirst($col) }}</th>
			@endforeach
			@if($show_actions)
			<th>Actions</th>
			@endif
		</tr>
		</thead>
		<tbody>
			
		</tbody>
		</table>
	</div>
</div>

@la_access("Vouchers", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-90" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm Voucher</h4>
			</div>
			{!! Form::open(['action' => 'LA\VouchersController@store', 'id' => 'voucher-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="row">
						<div class="col-md-6 col-sm-12">
							@la_input($module, 'name')
							@la_input($module, 'started_at')
							@la_input($module, 'ended_at')
							<div class="form-group">
								<label for="max">Tiền hàng tối thiểu :</label>
								<input class="form-control valid currency" placeholder="Nhập tiền hàng tối thiểu " name="min_order_amount" type="text" value="0" aria-invalid="false">
							</div>
							<div class="form-group">
								<label for="type">Kiểu phát hành* :</label>
							</div>
							<div>
								<label style="font-weight: normal">
									<input name="type" type="radio" value="1" checked>Một mã sử dụng nhiều lần
								</label>
								<br />
								<label  style="font-weight: normal">
									<input name="type" type="radio" value="2">Nhiều mã chỉ sử dụng 1 lần
								</label>
							</div>
						</div>
						<div class="col-md-6 col-sm-12">
							@la_input($module, 'code')
							@la_input($module, 'quantity')
							@la_input($module, 'percent')
							<div class="form-group">
								<label for="max">Tối đa :</label>
								<input class="form-control valid currency" placeholder="Nhập Tối đa" name="max" type="text" value="0" aria-invalid="false">
							</div>
							@la_input($module, 'owner_id')
							@la_input($module, 'groups_ids')
						</div>
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
	function typeChange()
	{
		var value = $('input[name="type"]:checked').val();
		if (value == 2) {
			$('label[for="code"]').html('Ký tự bắt đầu* :')
		} else {
			$('label[for="code"]').html('Mã* :')
		}

	}
$(function () {
	$('input[name="type"]').change(() => typeChange()).change();
	$("#example1").DataTable({
		processing: true,
        serverSide: true,
        ajax: "{{ url(config('laraadmin.adminRoute') . '/voucher_dt_ajax') }}",
		language: {
			lengthMenu: "_MENU_",
			search: "_INPUT_",
			searchPlaceholder: "Search"
		},
		@if($show_actions)
		columnDefs: [ { orderable: false, targets: [-1] }],
		@endif
	});
	$("#voucher-add-form").validate({
		
	});
});
</script>
@endpush
