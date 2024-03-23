@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/vouchers') }}">Voucher</a> :
@endsection
@section("contentheader_description", $voucher->$view_col)
@section("section", "Vouchers")
@section("section_url", url(config('laraadmin.adminRoute') . '/vouchers'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Vouchers Edit : ".$voucher->$view_col)

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

<div class="box">
	<div class="box-header">
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				{!! Form::model($voucher, ['route' => [config('laraadmin.adminRoute') . '.vouchers.update', $voucher->id ], 'method'=>'PUT', 'id' => 'voucher-edit-form']) !!}
					<div class="row">
						<div class="col-md-6 col-sm-12">
							@la_input($module, 'name')
							@la_input($module, 'started_at')
							@la_input($module, 'ended_at')
							<div class="form-group">
								<label for="max">Tiền hàng tối thiểu :</label>
								<input class="form-control valid currency" placeholder="Nhập tiền hàng tối thiểu " name="min_order_amount" type="text" value="{{ $voucher->min_order_amount }}" aria-invalid="false">
							</div>
							<div class="form-group">
								<label for="type">Kiểu phát hành* :</label>
							</div>
							<div>
								<label style="font-weight: normal">
									<input name="type" type="radio" value="1" @if ($voucher->type == 1) checked @else disabled @endif>Một mã sử dụng nhiều lần
								</label>
								<br />
								<label  style="font-weight: normal">
									<input name="type" type="radio" value="2" @if ($voucher->type == 2) checked @else disabled @endif>Nhiều mã chỉ sử dụng 1 lần
								</label>
							</div>
						</div>
						<div class="col-md-6 col-sm-12">
							@la_input($module, 'code')
							@la_input($module, 'quantity')
							@la_input($module, 'percent')
							<div class="form-group">
								<label for="max">Tối đa :</label>
								<input class="form-control valid currency" placeholder="Nhập Tối đa" name="max" type="text" value="{{ $voucher->max }}" aria-invalid="false">
							</div>
							@la_input($module, 'owner_id')
							<div class="form-group">
								<label>Nhóm khách hàng:</label>
								<select class="form-control ajax-select" model="group" name="groups_ids[]">
									@foreach(\App\Models\Group::whereIn('id', json_decode($voucher->groups_ids, true))->get() as $group)
										<option value="{{ $group->id }}" selected>{{ $group->display_name }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label for="type">Sản phẩm : <button type="button" class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#AddProduct">Thêm sản phẩm</button></label>
								@include('la.vouchers.products', ['edit' => true])
							</div>
						</div>
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/vouchers') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="AddProduct" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-70" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm sản phẩm</h4>
			</div>
			<div class="modal-body">
				<div class="box-body">
					@include('la.partials.created_filter', [
						'useExtraFilter' => false,
						'filterCreatedDate' => false,
						'filterDate' => false,
						'totals' => [],
						'filterColumns' => [],
						'filterOptions' => [],
						'extraForm' => '',
						'show_actions' => false,
						'listing_cols' => ['id', 'featured_image', 'sku', 'name']
					])
					<table id="example1" class="table table-bordered" style="width: 100%">
						<thead>
						<tr class="success">
							<th>ID</th>
							<th>Ảnh</th>
							<th>SKU</th>
							<th>Tên</th>
						</tr>
						</thead>
						<thead id="filter_bar">
							<tr class="success">
								<th colname="id">ID</th>
								<th colname="featured_image">Ảnh</th>
								<th colname="sku">SKU</th>
								<th colname="name">Tên</th>
							</tr>
						</thead>
						<tbody>

						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
				<button type="button" class="btn btn-success" data-dismiss="modal">Thêm</button>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
	var excludes = {!! $voucher->product_ids !!};
	var url = "{!!  url(config('laraadmin.adminRoute')) . '/product_dt_ajax?' . http_build_query([
		'listing_cols' => 'id,featured_image,sku,name',
		'selectable' => '1',
		'store_id' => request('store_id', 1)
]) !!}";
	function syncNo()
	{
		excludes = [];
		if ($('.voucher-products tbody tr').length > 0) {
			$('.voucher-products tr').each(function (value, index) {
				$(this).find('td:first-child').html((index + 1) + '')
				excludes.push($(this).find('input').val());
			})
		} else {
			$('table.voucher-products tbody').html('<tr><td class="text-center" colspan="4">Không có dữ liệu</td></tr>')
		}
	}
$(function () {
	$("#voucher-edit-form").validate({
		
	});
	$(document).on('click', '.voucher-product-item', function () {
		$(this).parents('tr').remove();
		syncNo();
	});
	$('#AddProduct .btn-success').click(function () {
		selectedProducts = [];
		$('#example1 input[type="checkbox"]').each(function () {
			if ($(this).prop('checked')) {
				selectedProducts.push({
					id: $(this).val(),
					sku: $(this).attr('data-sku'),
					name: $(this).attr('data-name'),
				});
			}
		})
		if (excludes.length == 0 && selectedProducts.length > 0) {
			$('table.voucher-products tbody').html('');
		}
		selectedProducts.map(function (product) {
			excludes.push(product.id);
			$('table.voucher-products tbody').append('<tr>'+
					'<td class="text-center">'+ excludes.length +'</td>'+
					'<td>'+ product.sku +'</td>'+
					'<td>'+ product.name +'</td>'+
					'<td>'+
						'<a class="btn btn-danger voucher-product-item"><i class="fa fa-trash"></i></a>'+
						'<input type="hidden" name="product_ids[]" value="'+ product.id +'" /> </td>'+
					'</tr>')
		})
		console.log(excludes);
	})
	$( "#AddProduct" ).on('shown.bs.modal', function(){
		table.draw()
	});
});
</script>
@endpush
