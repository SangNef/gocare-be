@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/productcombos') }}">Sản phẩm combo</a> :
@endsection
@section("contentheader_description", $productcombo->$view_col)
@section("section", "Sản phẩm combo")
@section("section_url", url(config('laraadmin.adminRoute') . '/productcombos'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa sản phẩm combo : ".$productcombo->$view_col)

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
			<div class="col-md-8 col-md-offset-2">
				{!! Form::model($productcombo, ['route' => [config('laraadmin.adminRoute') . '.productcombos.update', $productcombo->id ], 'method'=>'PUT', 'id' => 'productcombo-edit-form']) !!}

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
							@foreach($related as $key => $product)
							<div class="col-sm-12 related-product-item">
								<div class="col-sm-10">
									<select class="form-control ajax-select" model="product" name="products[]" required>
										@foreach($selectedProducts as $p)
											<option value="{{ $p->id }}" @if ($product[0] == $p->id) selected @endif>{{ $p->name }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-sm-4" style="display: none">
									<input class="form-control" name="quantities[]" value="{{ $product[1] }}" min="1">
								</div>
								<div class="col-sm-2">
									<button type="button" @if (!$key) disabled @endif class="btn btn-sm btn-danger remove-related-product"><i class="fa fa-remove"></i></button>
								</div>
							</div>
							@endforeach
						</div>
						<div class="form-group" style="display: none">
							<label for="store_id" style="margin-right: 20px">Nhóm khách hàng :</label>
							@foreach($groups as $key => $group)
								<div class="row">
									<div class="col-sm-6">{{ $group->display_name }}</div>
									<div class="col-sm-6">
										<input type="hidden" name="group[{{$key}}][group_id]" value="{{ $group->id }}">
										<input class="form-control" name="group[{{$key}}][discount]" value="{{ @$selectedGroups[$group->id] }}" type="number">
									</div>
								</div>
							@endforeach
						</div>
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/productcombos') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
	$("#productcombo-edit-form").validate({
		
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
