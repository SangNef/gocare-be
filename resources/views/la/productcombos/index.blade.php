@extends("la.layouts.app")

@section("contentheader_title", "Sản phẩm combo")
@section("contentheader_description", "Danh sách sản phẩm combo")
@section("section", "Sản phẩm combo")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Danh sách sản phẩm combo")

@section("headerElems")
@la_access("ProductCombos", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm sản phẩm combo</button>
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
			<th>&nbsp;</th>
			@endif
		</tr>
		</thead>
		<tbody>
			
		</tbody>
		</table>
	</div>
</div>

@la_access("ProductCombos", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm sản phẩm combo</h4>
			</div>
			{!! Form::open(['action' => 'LA\ProductCombosController@store', 'id' => 'productcombo-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					@la_input($module, 'product_id')
					@la_input($module, 'quantity')
					@la_input($module, 'discount')
					@la_input($module, 'note')
					@la_input($module, 'status')
					<div class="form-group">
						<label for="related">Sản phẩm liên quan : <button class="btn btn-sm btn-link" type="button" id="add-related-product">Thêm</button></label>
						<div class="row" id="related-product-items">
							<div class="col-sm-12">
								<div class="col-sm-10 text-center"><strong>Sản phẩm</strong></div>
								<div class="col-sm-4 text-center" style="display: none"><strong>Số lượng</strong></div>
							</div>
							<div class="col-sm-12 related-product-item">
								<div class="col-sm-10">
									<select class="form-control ajax-select" model="product" name="products[]" required></select>
								</div>
								<div class="col-sm-4" style="display: none">
									<input class="form-control" name="quantities[]" value="1" min="1">
								</div>
								<div class="col-sm-2">
									<button type="button" disabled class="btn btn-sm btn-danger remove-related-product"><i class="fa fa-remove"></i></button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group" style="display: none">
						<label for="store_id" style="margin-right: 20px">Nhóm khách hàng :</label>
						@foreach($groups as $key => $group)
							<div class="row">
								<div class="col-sm-6">{{ $group->display_name }}</div>
								<div class="col-sm-6">
									<input type="hidden" name="group[{{$key}}][group_id]" value="{{ $group->id }}">
									<input class="form-control" name="group[{{$key}}][discount]" value="0" type="number">
								</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
				{!! Form::submit( 'Submit', ['class'=>'btn btn-success']) !!}
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
	$("#example1").DataTable({
		processing: true,
        serverSide: true,
        ajax: "{{ url(config('laraadmin.adminRoute') . '/productcombo_dt_ajax') }}",
		language: {
			lengthMenu: "_MENU_",
			search: "_INPUT_",
			searchPlaceholder: "Search"
		},
		@if($show_actions)
		columnDefs: [ { orderable: false, targets: [-1] }],
		@endif
	});
	$("#productcombo-add-form").validate({
		
	});
	$('#add-related-product').click(function () {
		$('#related-product-items').append('' +
			'<div class="col-sm-12 related-product-item">\n' +
			'<div class="col-sm-6">\n' +
			'<select name="products[]"  class="form-control ajax-select" model="product" required></select>\n' +
			'</div>\n' +
			'<div class="col-sm-4">\n' +
			'<input name="quantities[]" class="form-control" value="1" min="1">\n' +
			'</div>\n' +
			'<div class="col-sm-2">\n' +
			'<button type="button" class="btn btn-sm btn-danger remove-related-product"><i class="fa fa-remove"></i></button>\n' +
			'</div>' +
			'</div>')
		initAjaxSelect();
		$('.related-product-item .remove-related-product').prop('disabled', false);
	});
	$(document).on('click', '.remove-related-product', function () {
		$(this).parents('.related-product-item').remove();
		if ($('.related-product-item').length == 1)
		{
			$('.related-product-item .remove-related-product').prop('disabled', true);
		}
	});
});
</script>
@endpush
