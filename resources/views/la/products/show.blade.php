@extends('la.layouts.app')

@section('htmlheader_title')
	Chi tiết sản phẩm
@endsection


@section('main-content')
<div id="page-content" class="profile2">
	<div class="bg-primary clearfix">
		<div class="col-md-4">
			<div class="row">
				<div class="col-md-3">
					<div class="profile-icon text-primary"><i class="fa {{ $module->fa_icon }}"></i></div>
				</div>
				<div class="col-md-9">
					<h4 class="name">{{ $product->$view_col }}</h4>
				</div>
			</div>
		</div>
		<div class="col-md-3">
		</div>
		<div class="col-md-4">

		</div>
		<div class="col-md-1 actions">
			@la_access("Products", "edit")
				<a href="{{ url(config('laraadmin.adminRoute') . '/products/'.$product->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
			@endla_access
			
			@la_access("Products", "delete")
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.products.destroy', $product->id], 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
				{{ Form::close() }}
			@endla_access
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/products') }}" data-toggle="tooltip" data-placement="right" title="Back to Products"><i class="fa fa-chevron-left"></i></a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> {{ trans('messages.general_info') }}</a></li>
		@if ($product->type == \App\Models\Product::TYPE_GROUP_PRODUCT)
			<li class=""><a role="tab" data-toggle="tab" href="#tab-related-product" data-target="#tab-related-product"><i class="fa fa-clock-o"></i> Sản phẩm con</a></li>
		@endif
{{--		<li class=""><a role="tab" data-toggle="tab" href="#tab-series" data-target="#tab-series"><i class="fa fa-clock-o"></i> {{ trans('product.series') }}</a></li>--}}
	</ul>
	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif
	@if (session('success'))
		<div class="alert alert-success">
			<ul>
				<li>{{ session('success') }}</li>
			</ul>
		</div>
	@endif
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>{{ trans('messages.general_info') }}</h4>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="status" class="col-md-2">Danh mục sản phẩm :</label>
							<div class="col-md-10 fvalue">
								{{ $product->categories->implode('name', ',')  }}
							</div>
						</div>
						<div class="form-group">
							<label for="status" class="col-md-2">{{ trans('messages.type') }} :</label>
							<div class="col-md-10 fvalue">
								{{ trans('product.type_' . $product->type) }}
							</div>
						</div>
						<div class="form-group">
							<label for="status" class="col-md-2">{{ trans('messages.fetured_image') }} :</label>
							<div class="col-md-10 fvalue">
								{!! $product->getFullFeaturedImage(80) !!}
							</div>
						</div>
						@la_display($module, 'sku')
						@la_display($module, 'name')
						<div class="form-group">
							<label for="min_stock" class="col-md-2">Thông số kỹ thuật :</label>
							<div class="col-md-10 fvalue" >
								{!! html_entity_decode($product->short_desc)  !!}
							</div>
						</div>
						<div class="form-group">
							<label for="min_stock" class="col-md-2">Thông tin sản phẩm  :</label>
							<div class="col-md-10 fvalue" >
								{!! html_entity_decode($product->desc)  !!}
							</div>
						</div>
						@la_display($module, 'price')
						@la_display($module, 'price_in_ndt')
						@la_display($module, 'n_quantity')
						@la_display($module, 'w_quantity')
						<div class="form-group">
							<label for="unit" class="col-md-2">Đơn vị :</label>
							<div class="col-md-10 fvalue">
								{{ ucfirst($product->unit) }}
							</div>
						</div>
						<div class="form-group">
							<label for="warranty_period" class="col-md-2">Thời hạn bảo hành (Tháng) :</label>
							<div class="col-md-10 fvalue">
								{{ $product->warranty_period }}
							</div>
						</div>
						<div class="form-group">
							<label for="status" class="col-md-2">{{ trans('messages.quantity') }} :</label>
							<div class="col-md-10 fvalue">
								@if ($product->quantity > 0)
									{{$product->quantity}}
								@else
									<div class="label label-danger">{{ trans('status.product_out_of_stock') }}</div>
								@endif
							</div>
						</div>
						<div class="form-group">
							<label for="status" class="col-md-2">{{ trans('messages.status') }} :</label>
							<div class="col-md-10 fvalue">
								@if ($product->status)
									<span class="label label-success">{{ trans('status.product_available') }}</span>
								@else
									<div class="label label-danger">{{ trans('status.product_stoped') }}</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		@if ($product->type == \App\Models\Product::TYPE_GROUP_PRODUCT)
			<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-related-product">
				<button class="btn btn-danger btn-sm pull-right" id="delete-related-products">{{ trans('button.delete') }}</button>
				<button class="btn btn-success btn-sm pull-right mr-1" data-toggle="modal" data-target="#AddProductModal">{{ trans('button.add') }}</button>

				<table class="table table-bordered" style="margin-top: 40px">
					<thead>
						<tr class="success">
							<th>
								{{ trans('messages.index') }}<br />
								<label style="cursor: pointer"><input type="checkbox" class="ck_all" data-target="ck_item"/>{{ trans('button.check_all') }}</label>
							</th>
							<th>{{ trans('messages.sku') }}</th>
							<th>{{ trans('messages.name') }}</th>
							<th>{{ trans('messages.quantity') }}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach($product->products as $key => $rp)
							<tr>
								<td>
									<input type="checkbox" class="ck_item" value="{{ $rp->id }}">
									{{ $key + 1 }}
								</td>
								<td>{{ $rp->child->sku }}</td>
								<td>{{ $rp->child->name }}</td>
								<td>{{ $rp->quantity }}</td>
								<td>
									<button class="btn btn-danger btn-sm remove-related-product" data-id="{{ $rp->id }}"><i class="fa fa-trash"></i></button>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
{{--		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-series">--}}
{{--			<div class="tab-content">--}}
{{--				<div class="row">--}}
{{--					<div class="col-md-8 col-md-offset-2">--}}
{{--						<button class="btn btn-warning btn-sm pull-right" id="print-qr">In QR-Code</button>--}}
{{--						<button class="btn btn-warning btn-sm pull-right mr5" id="update-status">Cập nhật trạng thái</button>--}}
{{--						<button data-toggle="modal" data-target="#extraSeries" class="btn btn-warning btn-sm pull-right mr5" id="print-extra-series">In trước Seri</button>--}}
{{--						@include('la.products.series.list', [--}}
{{--							'url' => request()->url(),--}}
{{--						])--}}
{{--					</div>--}}
{{--				</div>--}}
{{--			</div>--}}
{{--		</div>--}}
	</div>
	</div>
	</div>
</div>
@if ($product->type == \App\Models\Product::TYPE_GROUP_PRODUCT)
	<div class="modal fade" id="AddProductModal" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">{{ trans('messages.adding_related_product') }}</h4>
				</div>
				<div class="modal-body">
					<div class="box-body">
						@include('la.products_selecting.selecting', [
							'excludeFilter' => [
								   'filter[1000]' => [
									   'field' => 'id',
									   'operation' => 'nin',
									   'value' => $product->products->implode('r_id', ',')
									]
								]
							])
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
						<button type="button" class="btn btn-success" id="product-submit">Lưu</button>
					</div>
				</div>

			</div>
		</div>
	</div>
@endif
@endsection
@push('scripts')
<script>
	function deleteRelatedProduct(ids)
	{
		if (confirm('{!! trans('messages.delete_confirming') !!}')) {
			var url = '{{ route('products.related.delete', ['id' => $product->id]) }}?ids=' + ids + '#tab-related-product';
			location.href = url;
		}
	}
	$(function () {
		$(document).on('click', '.remove-related-product', function () {
			deleteRelatedProduct($(this).attr('data-id'));
		});
		$('#delete-related-products').click(function () {
			var ids = getCheckedValue('.ck_item');
			deleteRelatedProduct(ids.join(','));
		});

		$('#product-submit').click(function (event) {
			event.preventDefault();
			$(this).prop('disabled', true);
			var url = '{{ route('products.related.add', ['id' => $product->id]) }}?' + $.param($('#selected-product-form').serializeArray());
			location.href = url;
		})
	});
</script>
@endpush
