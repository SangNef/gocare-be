@extends('la.layouts.app')

@section('htmlheader_title')
	Xem chi tiết nhóm khách hàng
@endsection


@section('main-content')
<div id="page-content" class="profile2">
	<div class="bg-primary clearfix">
		<div class="col-md-4">
			<div class="row">
				<div class="col-md-3">
					<!--<img class="profile-image" src="{{ asset('la-assets/img/avatar5.png') }}" alt="">-->
					<div class="profile-icon text-primary"><i class="fa {{ $module->fa_icon }}"></i></div>
				</div>
				<div class="col-md-9">
					<h4 class="name">{{ $group->$view_col }}</h4>
				</div>
			</div>
		</div>
		<div class="col-md-3">

		</div>
		<div class="col-md-4">

		</div>
		<div class="col-md-1 actions">
			@la_access("Groups", "edit")
				<a href="{{ url(config('laraadmin.adminRoute') . '/groups/'.$group->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
			@endla_access
			
			@la_access("Groups", "delete")
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.groups.destroy', $group->id], 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
				{{ Form::close() }}
			@endla_access
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/groups') }}" data-toggle="tooltip" data-placement="right" title="Back to Groups"><i class="fa fa-chevron-left"></i></a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-info" data-target="#tab-info"><i class="fa fa-bars"></i> Thông tin chung</a></li>
		<li class=""><a role="tab" data-toggle="tab" href="#tab-discount" data-target="#tab-discount"><i class="fa fa-discount"></i> {{ trans('messages.discount') }}</a></li>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>Thông tin chung</h4>
					</div>
					<div class="panel-body">
						@la_display($module, 'name')
						@la_display($module, 'display_name')
					</div>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-discount">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<div class="row">
							<div class="col-sm-9">
								<h4>Chiết khấu theo danh mục sản phẩm</h4>
							</div>
						</div>
					</div>
					<div class="panel-body">
						<table class="table table-bordered">
							<thead>
								<tr class="success">
									<th class="text-center" rowspan="2">STT</th>
									<th class="text-center" rowspan="2">Danh mục sản phẩm</th>
									<th colspan="{{ count($withProductQuantities) }}" class="text-center">Chiết khấu nhập hàng <button value="1" class="ml-2 btn btn-xs btn-primary" data-toggle="modal" data-target="#AddQuantity"><i class="fa fa-plus"></i></button></th>
									<th colspan="{{ count($withoutProductQuantities) }}" class="text-center">Chiết khấu không nhập hàng <button value="2" class="ml-2 btn btn-xs btn-primary" data-toggle="modal" data-target="#AddQuantity"><i class="fa fa-plus"></i></button></th>
{{--									<th class="text-center" rowspan="2">Ghi chú</th>--}}
								</tr>
								<tr class="success">
									@if (count($withProductQuantities) > 0)
										@foreach ($withProductQuantities as $q)
											<th class="text-center">{{ number_format($q) }}</th>
										@endforeach
									@else
										<th class="text-center">&nbsp;</th>
									@endif
									@if (count($withoutProductQuantities) > 0)
										@foreach ($withoutProductQuantities as $q)
											<th class="text-center">{{ number_format($q) }}</th>
										@endforeach
									@else
										<th class="text-center">&nbsp;</th>
									@endif
								</tr>
							</thead>
							<tbody>
							@if ($categories->count() > 0)
								@foreach($categories as $index => $cate)
									<tr>
										<td>{{ $index + 1 }}</td>
										<td>{{ $cate->name }}</td>
										@foreach ($withProductQuantities as $q)
											<td class="text-center discount-item" style="cursor: pointer">
												{{ @$discount[$cate->id . '_1_' . $q]['discount_text'] }}
												@if (@$discount[$cate->id . '_1_' . $q])
													<button class="btn btn-xs btn-danger pull-right delete-discount" value="{{ $cate->id . '_1_' . $q }}" ><i class="fa fa-minus"></i></button>
												@endif
												<input type="hidden" class="type" value="1" />
												<input type="hidden" class="cate_id" value="{{@$discount[$cate->id . '_1_' . $q]['cate_id'] }}" />
												<input type="hidden" class="quantity" value="{{$q }}" />
												<input type="hidden" class="discount" value="{{@$discount[$cate->id . '_1_' . $q]['discount'] }}" />
												<input type="hidden" class="discount_1" value="{{ (int)@$discount[$cate->id . '_1_' . $q]['discount_1'] }}" />
											</td>
										@endforeach
										@foreach ($withoutProductQuantities as $q)
											<td class="text-center discount-item" style="cursor: pointer">
												{{ @$discount[$cate->id . '_2_' . $q]['discount_text'] }}
												@if (@$discount[$cate->id . '_2_' . $q])
													<button class="btn btn-xs btn-danger pull-right delete-discount" value="{{ $cate->id . '_2_' . $q }}" ><i class="fa fa-minus"></i></button>
												@endif
												<input type="hidden" class="type" value="1" />
												<input type="hidden" class="cate_id" value="{{@$discount[$cate->id . '_2_' . $q]['cate_id'] }}" />
												<input type="hidden" class="quantity" value="{{$q }}" />
												<input type="hidden" class="discount" value="{{@$discount[$cate->id . '_2_' . $q]['discount'] }}" />
												<input type="hidden" class="discount_1" value="{{ (int) @$discount[$cate->id . '_2_' . $q]['discount_1'] }}" />
											</td>
										@endforeach
									</tr>
								@endforeach
							@else
								<tr>
									<td colspan="{{ 2 + count($withProductQuantities) + count($withoutProductQuantities) }}">
										{{ trans('messages.no_data_found') }}
									</td>
								</tr>
							@endif
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		
	</div>
	</div>
	</div>
</div>
<div class="modal fade" id="AddQuantity" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{{ Form::open(['url' => route('group.discount.add')]) }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Thêm giảm giá theo danh mục sản phẩm</h4>
					<input type="hidden" id="data-index">
				</div>
				<div class="modal-body">
					<input type="hidden" name="group_id" value="{{ $group->id }}">
					<input type="hidden" name="type" value="1">
					<div class="form-group">
						<label for="status" style="margin-right: 20px">Danh mục sản phẩm :</label>
						<select class="form-control select2" required name="cate_id">
							@foreach(\App\Models\ProductCategory::all() as $cate)
								<option value="{{ $cate->id }}">{{ $cate->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="status" style="margin-right: 20px">Số lượng :</label>
						<input class="form-control text-right" name="quantity" required>
					</div>
					<div class="form-group">
						<label for="status" style="margin-right: 20px">Giảm giá :</label>
						<input class="form-control currency" type="text" value="0" name="discount" />
					</div>
					<div class="form-group">
						<label for="status" style="margin-right: 20px">Giảm gía % :</label>
						<input class="form-control text-right" type="number" value="0" name="discount_1" />
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
						<button type="submit" class="btn btn-success">Lưu</button>
					</div>
				</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
@endsection
@push('scripts')
	<script>
		$(function () {
			$('.delete-discount').click(function(event) {
				event.stopPropagation();
				var id = $(this).val();
				if (confirm('Bạn có chắc chắn muốn xoá?')) {
					location.href = '{{ route('group.discount.delete', ['group_id' => $group->id]) }}&id=' + id;
				}
			})
			$('td.discount-item').click(function() {
				var inputs = $(this).find('input');
				inputs.each((index) => {
					var el = inputs[index];
					$('#AddQuantity input[name="'+ $(el).attr('class') +'"]').val($(el).val());
					$('#AddQuantity select[name="'+ $(el).attr('class') +'"]').val($(el).val());
					if ($(el).attr('class') == 'discount') {
						const element = AutoNumeric.getAutoNumericElement('#AddQuantity input[name="discount"]')
						element.set($(el).val());
					}
				})

				$('#AddQuantity').modal('show');
			})
			$('#AddQuantity').on('show.bs.modal', event => {
				var type = $(event.relatedTarget).val();
				if (type) {
					$('#AddQuantity form')[0].reset()
					$('#AddQuantity input[name="type"]').val(type);
				}
			});
			var avtivatedTab = '{{ request('tab') }}';
			if (avtivatedTab) {
				$('.nav-tabs li').removeClass('active');
				$('.nav-tabs li > a').removeClass('active');
				$('.tab-content > .tab-pane').removeClass('active');
				$('.nav-tabs a[href="#'+ avtivatedTab +'"]').addClass('active');
				$('.nav-tabs a[href="#'+ avtivatedTab +'"]').parent().addClass('active');
				$('#' + avtivatedTab).addClass('active');
			}
		});
	</script>
@endpush
