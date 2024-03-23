@extends("la.layouts.app")

@section("contentheader_title", "Sản phẩm")
@section("contentheader_description", "Danh sách sản phẩm")
@section("section", "Sản phẩm")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Danh sách sản phẩm")

@section("headerElems")
@la_access("Products", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm sản phẩm</button>
{{--	<button class="btn btn-warning btn-sm pull-right mr-1" data-toggle="modal" data-target="#export">Báo giá</button>--}}
@endla_access
	<button class="btn btn-warning btn-sm pull-right" data-toggle="modal" data-target="#Import">Import sản phẩm</button>
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
@include('la.partials.created_filter', [
	'useExtraFilter' => true,
	'filterCreatedDate' => true,
	'filterDate' => true,
	'totals' => [],
	'filterColumns' => [],
	'filterOptions' => [
		'status' => [
			'Ngừng kinh doanh',
			'Đang kinh doanh'
		],
		'type' => [
			1 => trans('product.type_1'),
			2 => trans('product.type_2'),
			3 => trans('product.type_3'),
		]
	],
	'extraForm' => 'la.products.extra_filter_form',
])
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
			<thead id="filter_bar">
				<tr class="success">
					@foreach( $listing_cols as $col )
						<th @if($col == 'id') style="width: 8%" @endif colname="{!! isset($module->fields[$col]) ? $module->fields[$col]['colname'] : $col !!}">
							{{ $module->fields[$col]['label'] or ucfirst($col) }}
						</th>
					@endforeach
				</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
</div>

{{--<div class="box box-success">--}}
{{--	<!--<div class="box-header"></div>-->--}}
{{--	<div class="box-body">--}}
{{--		<table id="example1" class="table table-bordered">--}}
{{--		<thead>--}}
{{--		<tr class="success">--}}
{{--			@foreach( $listing_cols as $col )--}}
{{--			<th>{{ $module->fields[$col]['label'] or ucfirst($col) }}</th>--}}
{{--			@endforeach--}}
{{--			@if($show_actions)--}}
{{--			<th>Actions</th>--}}
{{--			@endif--}}
{{--		</tr>--}}
{{--		</thead>--}}
{{--		<tbody>--}}
{{--			--}}
{{--		</tbody>--}}
{{--		</table>--}}
{{--	</div>--}}
{{--</div>--}}

@la_access("Products", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	{!! Form::open(['action' => 'LA\ProductsController@store', 'id' => 'product-add-form']) !!}
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Thêm sản phẩm</h4>
				</div>
				<div class="modal-body">
					<div class="box-body">
						<div class="row">
							<div class="col-lg-6 col-sm-12">
								<div class="form-group">
									<label for="action">{{ trans('messages.product_category') }} :</label><select
											class="form-control select2-hidden-accessible"
											data-placeholder="{{ trans('messages.product_category') }}" multiple="" rel="select2" name="category_ids[]"
											tabindex="-1" aria-hidden="true">
										@foreach(\App\Models\ProductCategory::all() as $category)
											<option value="{{ $category->id }}">{{ $category->name }}</option>
										@endforeach
									</select>
								</div>
								@la_input($module, 'featured_image')
								@la_input($module, 'product_gallery')
								@la_input($module, 'sku')
								@la_input($module, 'name')
								<div class="form-group">
									<label for="price">Thông số kỹ thuật :</label>
									<textarea class="form-control tinymce" placeholder="Nhập Thông số kỹ thuật" cols="30" rows="3" name="desc"></textarea>
								</div>
								<div class="form-group">
									<label for="price">Thông tin sản phẩm :</label>
									<textarea class="form-control tinymce" placeholder="Enter Thông tin sản phẩm" cols="30" rows="3" name="short_desc"></textarea>
								</div>
								<div class="form-group" style="display: none">
									<label for="status" style="margin-right: 20px">Kiểu :</label>
									<label style="margin-right: 10px"><input type="radio" value="1" name="type" checked>Sản phẩm thường</label>
									<label style="margin-right: 10px"><input type="radio" value="2" name="type" >Sản phẩm gộp</label>
									<label><input type="radio" value="3" name="type">Phụ kiện</label>
								</div>
								<div class="form-group" style="display: none" id="use-child-product">
									<label for="status" style="margin-right: 20px">Trừ luôn số lượng sản phẩm con :</label>
									<label style="margin-right: 10px"><input type="radio" value="1" name="use_child_product" checked>Đồng ý</label>
									<label><input type="radio" value="2" name="use_child_product" >Không</label>
								</div>
							</div>
							<div class="col-lg-6 col-sm-12">
								<div class="row">
									<div class="form-group col-md-6">
										<label for="price">Giá nhập :</label>
										<input class="form-control valid currency" placeholder="Enter Giá nhập" name="price" type="text" value="0" aria-invalid="false">
									</div>
									<div class="form-group col-md-6">
										<label for="price">Giá bán lẻ :</label>
										<input class="form-control valid currency" placeholder="Nhập Giá bán lẻ" name="retail_price" type="text" value="0" aria-invalid="false">
									</div>
									<div class="form-group col-md-6" style="display: none">
										<label for="price">Giá nhập NDT :</label>
										<input class="form-control valid currency" placeholder="Enter Giá nhập NDT" name="price_in_ndt" type="text" value="0" aria-invalid="false">
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-6" style="display: none">
										<label for="n_quantity">Số lượng :</label>
										<input id="n_quantity" class="form-control valid integer" placeholder="Số lượng hàng mới" name="n_quantity" type="text" value="0" aria-invalid="false">
									</div>
									<div class="form-group col-md-6" style="display: none">
										<label for="w_quantity">Số lượng bảo hành :</label>
										<input id="w_quantity" class="form-control valid integer" placeholder="Số lượng hàng bảo hành" name="w_quantity" type="text" value="0" aria-invalid="false">
									</div>
								</div>
								<div class="row">
									<div class="form-group col-md-6">
										<label for="unit">Đơn vị :</label>
										<select name="unit" id="unit" class="form-control">
											<option value="">Chọn</option>
											@foreach(\App\Models\Setting::getProductUnit() as $unit)
												<option value="{{ $unit }}">{{ ucfirst($unit) }}</option>
											@endforeach
										</select>
									</div>
{{--									<div class="form-group col-md-6">--}}
{{--										<label for="min_stock">Số lượng hàng tối thiểu :</label>--}}
{{--										<input type="number" class="form-control" name="min_stock" id="min_stock" value="0"/>--}}
{{--									</div>--}}

									<div class="form-group col-md-6">
										<label for="warranty_period">Thời hạn bảo hành (Tháng) :</label>
										<input type="number" class="form-control" name="warranty_period" id="warranty_period" value="0"/>
									</div>
								</div>
								<div class="form-group">
									<label for="price">Tổng :</label>
									<input class="form-control" disabled id="quantity" value="0"/>
								</div>
								<div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Khối lượng (gram) *</label>
                                            <input type="number" class="form-control" name="weight" value="0"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Chiều dài (cm) *</label>
                                            <input type="number" class="form-control" name="length" value="0"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Chiều cao (cm) *</label>
                                            <input type="number" class="form-control" name="height" value="0"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Chiều rộng (cm) *</label>
                                            <input type="number" class="form-control" name="width" value="0"/>
                                        </div>
                                    </div>

                                </div>
								<div class="form-group">
									<label for="price">Nhập trạng thái :</label>
									<input class="form-control" name="status_text" id="status_text" value="" placeholder="Sắp ra mắt" maxlength="20"/>
								</div>
								@la_input($module, 'status')
								@la_input($module, 'has_series')
								<div class="form-group" style="display: none;">
									<label for="status">{{ trans('messages.discount') }} :</label>
									<table class="table table-bordered">
										<thead>
											<tr class="success">
												<td style="text-align: center; color: black; font-weight: bold">Nhóm</td>
												<td style="text-align: center; color: black; font-weight: bold">VND</td>
												<td style="text-align: center; color: black; font-weight: bold">Giảm theo %</td>
											</tr>
										</thead>
										<tbody>
											@foreach(\App\Models\Group::all() as $group)
											<tr>
												<td>{{ $group->name }}</td>
												<td>
													<input class="form-control" name="group_discount[discount][{{$group->id}}]" value="{{ @$discount[$group->id] ? $discount[$group->id] : 0 }}" />
												</td>=
												<td>
													<input type="number" min="0" max="100" class="form-control" name="group_discount[discount_percent][{{$group->id}}]" value="{{ @$discountPercent[$group->id] ? $discountPercent[$group->id] : 0 }}" />
												</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div id="adding-products" style="display: none">
							@include('la.products_selecting.selecting')
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
						<button type="submit" class="btn btn-success onetime-click">Lưu</button>
					</div>
				</div>
		</div>
	</div>
	{!! Form::close() !!}
</div>
@endla_access

<div class="modal fade" id="export" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Xuất file báo giá cho khách hàng</h4>
					<div class="error alert alert-danger" style="display: none"></div>
				</div>
				<div class="modal-body">
					<div class="box-body">
						<div class="form-group">
							<label for="price">Chọn khách hàng :</label>
							<select required name="customer_id" class="form-control ajax-select submit-required" model="customer">
							</select>
						</div>
						<div class="form-group">
							<label for="price">Danh mục sản phẩm :</label>
							<select required class="form-control select2-hidden-accessible"
								data-placeholder="{{ trans('messages.product_category') }}" multiple="" rel="select2" name="pc_ids[]"
								tabindex="-1" aria-hidden="true">
								@foreach(\App\Models\ProductCategory::all() as $category)
									<option value="{{ $category->id }}">{{ $category->name }}</option>
								@endforeach
							</select>
							<small class="text-danger">* Nếu không chọn danh mục, hệ thống sẽ xuất toàn bộ sản phẩm</small>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
						<button type="submit" class="btn btn-success onetime-click" id="btn-export">Xuất file</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="Import" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST" enctype="multipart/form-data" id="import-product-seri">
				<div class="modal" id="modal-loading" data-backdrop="static">
					<div class="modal-dialog modal-sm">
						<div class="modal-content">
							<div class="modal-body text-center">
								<div class="loading-spinner mb-2"></div>
								<div>Đang import</div>
							</div>
						</div>
					</div>
				</div>
				{{csrf_field()}}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Import sản phẩm</h4>
				</div>
				<div class="modal-body">
					<div class="box-body">
						<div class="alert alert-danger" style="display: none">
							<ul>

							</ul>
						</div>
						<div class="alert alert-success" style="display: none">
							<ul>
								<li>Import seri sản phẩm thành công</li>
							</ul>
						</div>
						<div class="form-group">
							<label for="price">Chọn file excel (.xlsx):</label>
							<input type="file" name="file">

						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" checked="checked" value="2" id="defaultCheck2" name="qr_code_status">
							<label class="form-check-label" for="defaultCheck2">
								Sản phẩm thiết bị
							</label><br />
							<input class="form-check-input" type="radio" value="0" id="defaultCheck3" name="qr_code_status">
							<label class="form-check-label" for="defaultCheck3">
								Gói dịch vụ
							</label><br />
							<input class="form-check-input" type="radio" value="1" id="defaultCheck1" name="qr_code_status">
							<label class="form-check-label" for="defaultCheck1">
								Gói dịch vụ thẻ cứng
							</label>
						</div>

						<p style="color:red"> Lưu ý mỗi lần import tối đa 2.000 sản phẩm</p>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
						<button type="submit" class="btn btn-success">Import</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<script>
var url = "{{ url(config('laraadmin.adminRoute')) . '/product_dt_ajax?store_id=' . request('store_id', 1) }}";

$(function () {
	$('#import-product-seri').submit(function (event) {
		event.preventDefault();
		var el = $(this);
		var formData = new FormData(this);
		$(this).find('button[type="submit"]').prop('disabled', true);
		el.find('.alert-danger').hide();
		el.find('.alert-success').hide();
		$('#modal-loading').modal('show');
		$.ajax({
			type: "POST",
			url: '{{ route('products.seri.import') }}',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) {
				el.find('button[type="submit"]').prop('disabled', false);
				el.find('.alert-success').show();
				$('#modal-loading').modal('hide');
			},
			error: function (data) {
				var mess = data.responseJSON;
				var text = [];
				Object.values(mess).map((item) => {
					text = [...text, ...item.map(value => '<li>' + value + '</li>')]
				})
				el.find('button[type="submit"]').prop('disabled', false);
				el.find('.alert-danger ul').html(text.join(''))
				el.find('.alert-danger').show();
				$('#modal-loading').modal('hide');
			},
		});
	})
	$('input[name=type]').change(function () {
		var value = $(this).val();
		if (value == '{{ \App\Models\Product::TYPE_GROUP_PRODUCT }}') {
			$('#adding-products').show();
			$('#use-child-product').show();
		} else {
			$('#adding-products').hide();
			$('#use-child-product').hide();
		}
	});

	$('#w_quantity, #n_quantity').keypress(function () {
		var wQuantity = $('#w_quantity').val().replace(/[^\d]/g, '');
		var nQuantity = $('#n_quantity').val().replace(/[^\d]/g, '');
		var quantity = parseInt(wQuantity) + parseInt(nQuantity);
		$('#quantity').val(quantity.toLocaleString());
	})

	$("#product-add-form").validate({
		
	});

	$('#export form').submit(function (e) {
		e.preventDefault();
		if (checkRequiredInputs('export')) {
			let url = objectToQueryString("{{ route('products.export') }}", $(this).serializeObject());
			let iframe = document.createElement('iframe');
			iframe.className='pdfIframe'
			document.body.appendChild(iframe);
			iframe.style.display = 'none';
			iframe.onload = function () {
				setTimeout(function () {
					iframe.focus();
					URL.revokeObjectURL(url);
					document.body.removeChild(iframe);
					$('#btn-export').removeAttr('disabled').html('Xuất file');
				}, 1);
			};
			iframe.src = url;
		} else {
			$('#btn-export').removeAttr('disabled').html('Xuất file');
		}
	});

	$(document).on('click', '.reorder', function() {
		let tr = $(this).closest('tr');
		let categoryId = $("#filter_bar th[colname='category_ids'] .filter-item").val();
		let productId = tr.find('.sorting_1').text();
		let type = $(this).val();
		let oldPosition = Number($(this).closest('.reorder-group').data('position'));
        let oldIndex = Number(tr.index()) + 1;
		switch (type) {
			case "up":
				tr.prev().before(tr);
				break;
			case "down":
				tr.next().after(tr);
				break;
			case "top":
				tr.siblings().first().before(tr);
				break;
			case "bottom":
				tr.siblings().last().after(tr);
				break;
			default:
				break;
		}
        let newIndex = Number(tr.index()) + 1;
        let step = newIndex - oldIndex;
        let newPosition = oldPosition + step;
        if (categoryId && productId) {
            $.ajax({
                method: "POST",
                url: "{{ route('products.reorder-position') }}",
                beforeSend: function() {
                    $('.reorder').attr('disabled', true);
                },
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    category_id: categoryId,
                    old: oldPosition,
                    new: newPosition
                },
                success: function() {
                    $('#example1').DataTable().draw();
                    $('.reorder').attr('disabled', false);
                }
            })
        }
	});
});
</script>
@endpush
