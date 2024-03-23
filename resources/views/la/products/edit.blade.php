@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/products') }}">Sản phẩm</a> :
@endsection
@section("contentheader_description", $product->$view_col)
@section("section", "Sản phẩm")
@section("section_url", url(config('laraadmin.adminRoute') . '/products'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa sản phẩm : ".$product->$view_col)

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
		<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
			<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i>{{ trans('messages.general_info') }}</a></li>
			<li><a role="tab" data-toggle="tab" class="active" href="#tab-product-attribute" data-target="#tab-product-attribute"><i class="fa fa-bars"></i>{{ trans('messages.product_attribute') }}</a></li>
			@if ($product->isUseSeries())
			<li><a role="tab" data-toggle="tab" href="#tab-series" data-target="#tab-series"><i class="fa fa-dollar"></i>{{ trans('product.series') }}</a></li>
			@endif
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
				<div class="row">
					<div class="col-md-8 col-md-offset-2">
						{!! Form::model($product, ['route' => [config('laraadmin.adminRoute') . '.products.update', $product->id ], 'method'=>'PUT', 'id' => 'product-edit-form']) !!}
							@la_input($module, 'featured_image')
							@la_input($module, 'product_gallery')
							<div class="form-group">
								<label for="action">{{ trans('messages.product_category') }} :</label><select
										class="form-control select2-hidden-accessible"
										data-placeholder="{{ trans('messages.product_category') }}" multiple="" rel="select2" name="category_ids[]"
										tabindex="-1" aria-hidden="true">
										@foreach(\App\Models\ProductCategory::all() as $category)
											<option value="{{ $category->id }}" @if (in_array($category->id, json_decode($product->category_ids, true))) selected="selected" @endif>{{ $category->name }}</option>
										@endforeach
								</select>
							</div>
							@la_input($module, 'sku')
							@la_input($module, 'name')
							<div class="form-group">
								<label for="price">Thông số kỹ thuật :</label>
								<textarea class="form-control tinymce" placeholder="Nhập Thông số kỹ thuật" cols="30" rows="3" name="desc">{!! $product->desc !!}</textarea>
							</div>
							<div class="form-group">
								<label for="price">Thông tin sản phẩm :</label>
								<textarea class="form-control tinymce" placeholder="Enter Mô tả ngắn" cols="30" rows="3" name="short_desc">{!! $product->short_desc ?: '' !!}</textarea>
							</div>
							@la_input($module, 'price')
							@la_input($module, 'retail_price')
{{--							@la_input($module, 'price_in_ndt')--}}
{{--							@la_input($module, 'n_quantity')--}}
{{--							@la_input($module, 'w_quantity')--}}
{{--							<div class="form-group">--}}
{{--								<label for="min_stock">Số lượng hàng tối thiểu :</label>--}}
{{--								<input type="number" class="form-control" name="min_stock" id="min_stock" value="{{ $product->min_stock }}"/>--}}
{{--							</div>--}}
							<div class="form-group">
								<label for="unit">Đơn vị :</label>
								<select name="unit" id="unit" class="form-control">
									<option value="" selected disabled>Chọn</option>
									@foreach(\App\Models\Setting::getProductUnit() as $unit)
										<option @if($product->unit === $unit) selected @endif value="{{ $unit }}">{{ ucfirst($unit) }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label for="warranty_period">Thời hạn bảo hành (Tháng) :</label>
								<input type="number" class="form-control" name="warranty_period" id="warranty_period" value="{{ $product->warranty_period }}"/>
							</div>
							<div class="row">
								<div class="col-sm-3">
									<div class="form-group">
										<label for="">Khối lượng (gram) *</label>
										<input type="number" class="form-control" name="weight" value="{{ $product->weight }}"/>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label for="">Chiều dài (cm) *</label>
										<input type="number" class="form-control" name="length" value="{{ $product->length }}"/>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label>Chiều cao (cm) *</label>
										<input type="number" class="form-control" name="height" value="{{ $product->height }}"/>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label>Chiều rộng (cm) *</label>
										<input type="number" class="form-control" name="width" value="{{ $product->width }}"/>
									</div>
								</div>
							</div>
						<div class="form-group">
							<label for="price">Nhập trạng thái :</label>
							<input class="form-control" name="status_text" id="status_text" value="{{ $product->status_text }}" placeholder="Sắp ra mắt" maxlength="20"/>
						</div>
							@la_input($module, 'status')
							@la_input($module, 'has_series')
							<div class="form-group" style="display: none">
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
										@foreach($groups as $group)
										<tr>
											<td>{{ $group->name }}</td>
											<td>
												<input class="form-control" name="group_discount[discount][{{$group->id}}]" value="{{ @$discount[$group->id] ? $discount[$group->id] : 0 }}" />
											</td>
											<td>
												<input class="form-control" name="group_discount[discount_ndt][{{$group->id}}]" value="{{ @$discountNdt[$group->id] ? $discountNdt[$group->id] : 0 }}" />
											</td>
											<td>
												<input type="number" min="0" max="100" class="form-control" name="group_discount[discount_percent][{{$group->id}}]" value="{{ @$discountPercent[$group->id] ? $discountPercent[$group->id] : 0 }}" />
											</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
							<br>
							<div class="form-group">
								{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/products') }}">Huỷ</a></button>
							</div>
						{!! Form::close() !!}
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane fade in" id="tab-product-attribute">
				<div class="tab-content">
					<div class="panel infolist">
						<div class="panel-default panel-heading">
							<h4>{{ trans('product.product_attribute') }}</h4>
						</div>
						<div class="panel-body">

						</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane fade in" id="tab-series">
				<div class="row">
					<div class="col-md-12 p20">
						<select name="per_page" class="pull-left form-control input-sm filter-status" style="width: 75px">
							@foreach($pSeriesPaginatorLength as $option)
								<option value="{{ $option }}">{{ $option }}</option>
							@endforeach
						</select>
						<button data-toggle="modal" data-target="#printSeries" class="btn btn-warning btn-sm pull-right mr5" id="print-qr">In QR-Code</button>
{{--						<button class="btn btn-warning btn-sm pull-right mr5" id="update-status">Cập nhật trạng thái</button>--}}
{{--						<button data-toggle="modal" data-target="#extraSeries" class="btn btn-warning btn-sm pull-right mr5" id="print-extra-series">In trước Seri</button>--}}
						<button class="btn btn-danger btn-sm pull-right mr5" id="delete-series">Xóa seri</button>
						<button class="btn btn-primary btn-sm pull-right mr5"  data-toggle="modal" data-target="#set-group-attribute-for-seris" id="set-group-attribute">Cài đặt thuộc tính</button>
						<table class="table table-bordered" id="series">
							<thead>
								<tr class="success">
									<th width="20%">
										<label style="cursor: pointer">
											<input type="checkbox" class="ck_all" data-target="ck_item"/>
											{{ trans('button.check_all') }}
										</label><br/>
										Số seri
									</th>
									<th>Mã kích hoạt</th>
									<th>Đặc điểm</th>
									<th>Trạng thái in mã</th>
									<th>Trạng thái bán hàng</th>
									<th>Trạng thái kích hoạt</th>
									<th>Ngày tạo</th>
								</tr>
								<tr class="success">
									<th>
										<input class="filter-status" name="seri_number" style="width: 100%;"/>
									</th>
									<th>
										<input class="filter-status" name="activation_code" style="width: 100%;"/>
									</th>
									<th>
										<select class="filter-attr" id="attr_id" name="attr_id" style="width: 100%">
											<option value="" selected>Tất cả</option>
											@foreach($attrs as $value)
												<option value="{{ $value->id }}">{{ $value->attribute_value_texts }}</option>
											@endforeach
										</select>
									</th>
									<th>
										<select class="filter-status" id="stock_status" name="qr_code_status" style="width: 100%">
											<option value="" selected>Tất cả</option>
											@foreach(\App\Models\ProductSeri::getQrCodeStatus() as $key => $value)
												<option @if(request('qr_code_status') != '' && request('qr_code_status') == $key) selected @endif value="{{ $key }}">{{ $value }}</option>
											@endforeach
										</select>
									</th>
									<th>
										<select class="filter-status" id="stock_status" name="stock_status" style="width: 100%">
											<option value="" selected>Tất cả</option>
											@foreach(\App\Models\ProductSeri::getAvailableStockStatus() as $key => $value)
												<option @if(request('stock_status') != '' && request('stock_status') == $key) selected @endif value="{{ $key }}">{{ $value }}</option>
											@endforeach
										</select>
									</th>
									<th>
										<select class="filter-status" id="import_status" name="import_status" style="width: 100%">
											<option value="" selected>Tất cả</option>
											@foreach(\App\Models\ProductSeri::getImportStatus() as $key => $value)
												<option @if(request('import_status') != '' && request('import_status') == $key) selected @endif value="{{ $key }}">{{ $value }}</option>
											@endforeach
										</select>
									</th>
									<th>
										<input type="text" name="created_at" placeholder="Chọn ngày" class="form-control input-sm datepicker filter-status"/>
									</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="extraSeries" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{{ Form::open(['url' => route('product.extra-series'), 'id' => 'extra-series-form']) }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">{{ trans('product.extra_series') }}</h4>
				</div>
				<div class="modal-body">
					<div class="box-body">
						<div class="form-group">
							<label>Số lượng <strong class="text-danger">*Chỉ nên in trước từ 500 series trở xuống</strong>: </label>
							<input type="number" id="extra-number" name="extra_series" class="form-control" value="0">
						</div>
						<div class="form-group">
							<label>Chọn thuộc tính</label>
							<select class="ajax-select form-control" name="avm_id" model="group_attribute" extra_param="{{ $product->id }}">

							</select>
						</div>
						<div class="form-group">
							<label style="margin-right: 20px">Chọn khổ giấy: </label>
							<label style="margin-right: 10px"><input type="radio" value="3" name="per_row" checked>35 x 25</label>
							<label><input type="radio" value="4" name="per_row">25 x 25</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel') }}</button>
						<button type="submit" class="btn btn-success onetime-click" id="order-approve-submit">Lưu & In</button>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel') }}</button>
						<button type="submit" class="btn btn-success onetime-click" id="order-approve-submit">Lưu & In</button>
					</div>
				</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
<div class="modal fade" id="printSeries" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{{ Form::open(['id' => 'print-series-form']) }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">{{ trans('product.print_series') }}</h4>
				</div>
				<div class="modal-body">
					<div class="box-body">
						<div class="form-group">
							<label for="print_all">In tất cả series: </label>
							<input type="checkbox" id="print_all">
						</div>
						<div class="form-group">
							<label style="margin-right: 20px">Chọn khổ giấy: </label>
							<label style="margin-right: 10px"><input type="radio" value="3" name="per_row" checked>35 x 25</label>
							<label><input type="radio" value="4" name="per_row">25 x 25</label>
							<label><input type="radio" value="2" name="per_row">50 x 50</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel') }}</button>
						<button type="submit" class="btn btn-success" id="order-approve-submit">In</button>
					</div>
				</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
<div class="modal fade" id="add-group-attribute-media" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{!! Form::open(['url' => route('products.attribute-value.media.save', ['id' => $product->id]), 'method' => 'GET', 'id' => 'add-group-attribute-media-form']) !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm/Xoá ảnh cho nhóm thuộc tính sản phẩm</h4>
			</div>
			<div class="modal-body">
				<div class="box-body">

				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
					<button type="submit" class="btn btn-success" id="btn-export">Cập nhập</button>
				</div>
			</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
<div class="modal fade" id="set-group-attribute-for-seris" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{!! Form::open(['url' => route('products.attribute-value.seri.save', ['id' => $product->id]), 'method' => 'POST', 'id' => 'set-group-attribute-for-seris-form']) !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Cài đặt thuộc tính sản phẩm cho seri</h4>
			</div>
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label>Chọn thuộc tính</label>
						<select class="ajax-select form-control" name="avm_id" model="group_attribute" extra_param="{{ $product->id }}">

						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
					<button type="submit" class="btn btn-success" id="btn-export">Cập nhập</button>
				</div>
			</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
var params = {};
var xhrPool = [];
function abortAll() {
	xhrPool.map(function(jqXHR) {
		jqXHR.abort();
	});
	xhrPool = [];
}

function loadProductGalery(attrValueMediaId)
{
	const url = '{{ route('products.attribute-value.media.get', ['id' => $product->id]) }}';
	$.ajax({
		method: "GET",
		url: url,
		data: {
			avm_id: attrValueMediaId
		},
		success: function(res) {
			$("#add-group-attribute-media .box-body").html(res);
		}
	});
}

function loadSeries(params = {}) {
	const url = objectToQueryString("{{ route('products.load-series', ['productId' => $product->id]) }}", params);
	$.ajax({
		method: "GET",
		url: url,
		beforeSend: function (jqXHR, settings) {
			abortAll();
			xhrPool.push(jqXHR);
		},
		success: function(res) {
			$("table#series tbody").html(res);	
		}
	});
}

function printSeries(ids = [], perRow = 3) {
	let url = "{{ url(config('laraadmin.adminRoute') . '/product-series-print/' . $product->id) }}?per_row=" + perRow;
	if (ids.length > 0) {
		url += `&ids=${ids.join(',')}`
	}
	let iframe = document.createElement('iframe');
	iframe.className='pdfIframe'
    document.body.appendChild(iframe);
    iframe.style.display = 'none';
    iframe.src = url;
	return new Promise((resolve) => {
        iframe.onload = function () {
            setTimeout(function () {
                iframe.focus();
                URL.revokeObjectURL(url);
                document.body.removeChild(iframe);
            }, 1);
            resolve(true);
        };
    })
}

function processCheckedSeri(url, allSeries = false) {
    if (!allSeries) {
        let ids = getCheckedValue('.ck_item');
        if (ids.length == 0) {
            alert('Chọn seri');
            return;
        }
        url += '?ids=' + ids.join(',');
    }
    location.href = url;
}

function loadProductAttribute()
{
	const url = '{{ route('products.attribute', ['id' => $product->id]) }}';
	$.ajax({
		method: "GET",
		url: url,
		success: function(res) {
			$("#tab-product-attribute .panel-body").html(res);
			initAjaxSelect();
		}
	});
}

$(function () {
	loadSeries();
	loadProductAttribute();
	$("#product-edit-form").validate({
		
	});

	$("#tab-series .filter-status").on('change keyup', function(e) {
		let name = $(this).attr('name');
		let val = $(this).val();
		params[name] = val;
		loadSeries({...params, page: 1});
	});
	$("#tab-series .filter-attr").on('change keyup', function(e) {
		let name = $(this).attr('name');
		let val = $(this).val();
		params[name] = val;
		loadSeries({...params, page: 1});
	});

	$(document).on('click', 'table#series .pagination a', function(e) {
		e.preventDefault();
		const page = GetURLParameter('page',  $(this).attr('href')); 
		params = {...params, page: page}
		loadSeries(params);
	});

	$("#update-status").click(function() {
        event.preventDefault();
		var url = "{{ url(config('laraadmin.adminRoute') . '/product-series/update-status/' . $product->id) }}";
		processCheckedSeri(url);
	})

    $('#delete-series').click(function (event) {
		event.preventDefault();
        var url = "{{ url(config('laraadmin.adminRoute') . '/product-series/delete/' . $product->id) }}";
        processCheckedSeri(url);
	})

	$('.qr-code-status').on('click', function() {
		return confirm('Chuyển trạng thái?');
    })
    
    $('#print-series-form').submit(function(e) {
        e.preventDefault();
		const formData = $(this).serializeObject();
		let perRow = formData.per_row;
		let isPrintAll = $(this).find('#print_all').is(':checked');
		let ids;
		if (!isPrintAll) {
			ids = getCheckedValue('.ck_item');
			if (ids.length === 0) {
				alert('Chọn seri');
				return;
			}
		}
		printSeries(ids, perRow);
    });

    $('#extra-series-form').submit(function(e) {
        e.preventDefault();
		const formData = $(this).serializeObject();
        let numberOfSeries = formData.extra_series;
		let perRow = formData.per_row;
        let productID = "{{ $product->id }}";
        let url = $(this).attr('action');
		var avm_id = $(this).find('select[name="avm_id"]').val();
        $.ajax({
            url: url,
            method: "POST",
            headers: {
                'X-CSRF-Token': '{{ csrf_token() }}'
            },
            data: {
                product_id: productID,
                extra_series: numberOfSeries,
				avm_id: avm_id,
            },
            success: function(res) {
				printSeries(res, perRow)
					.then(function() {
						setTimeout(function() {
							window.location.reload();
						}, 1);
					});
            },
			error: function(error) {
				window.location.reload();
			}
        })
    });

	$('#add-group-attribute-media-form').submit(function(e) {
		e.preventDefault();
		$('#add-group-attribute-media button[type="submit"]').prop('disabled', true);
		$.ajax({
			url: $(this).attr('action'),
			method: "POST",
			headers: {
				'X-CSRF-Token': '{{ csrf_token() }}'
			},
			data: $(this).serialize(),
			success: function(res) {
				$('#add-group-attribute-media').modal('hide');
				$('#add-group-attribute-media button[type="submit"]').prop('disabled', false);
				$("#tab-product-attribute .panel-body").html(res);
				initAjaxSelect();
			},
			error: function(error) {
				window.location.reload();
			}
		})
	});
	$('#set-group-attribute-for-seris-form').submit(function(e) {
		e.preventDefault();
		$('#set-group-attribute-for-seris-form button[type="submit"]').prop('disabled', true);
		let ids = getCheckedValue('.ck_item');
		if (ids.length == 0) {
			alert('Chọn seri');
			return;
		}
		var url = $(this).attr('action') + '?ids=' + ids.join(',');
		$.ajax({
			url: url,
			method: "POST",
			headers: {
				'X-CSRF-Token': '{{ csrf_token() }}'
			},
			data: $(this).serialize(),
			success: function(res) {
				$('#set-group-attribute-for-seris').modal('hide');
				$('#set-group-attribute-for-seris-form button[type="submit"]').prop('disabled', false);
				loadSeries();
			},
			error: function(error) {
				window.location.reload();
			}
		})
	});

    $(document).on('change', '#product-attribute', function (event) {
    	var url = '{{ route('products.attribute.save', ['id' => $product->id]) }}';
    	var values = $(this).val();
		$.ajax({
			url: url,
			method: "GET",
			data: {
				attribute_ids: values
			},
			success: function(res) {
				$("#tab-product-attribute .panel-body").html(res);
				initAjaxSelect();
			},
		})
	});
	$(document).on('change', '.product-attribute-value', function (event) {
		var url = '{{ route('products.attribute-value.save', ['id' => $product->id]) }}';
		var values = $(this).val();
		var attributeId = $(this).attr('id').replace('product-attribute-value-', '');
		$.ajax({
			url: url,
			method: "GET",
			data: {
				attribute_id: attributeId,
				values: values
			},
			success: function(res) {
				$("#tab-product-attribute .panel-body").html(res);
				initAjaxSelect();
			},
		})
	});
	$(document).on('shown.bs.modal', '#add-group-attribute-media', function (e) {
		var el = e.relatedTarget;
		var id = $(el).data('content');
		loadProductGalery(id);
	});
	$(document).on('click', '#add-group-attribute-media-form .media-item', function (e) {
		$(this).toggleClass('active border-primary');
		$(this).find('input').prop('disabled', !$(this).hasClass('active'));
	});
});
</script>
@endpush
