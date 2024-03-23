@extends('la.layouts.app')

@section('htmlheader_title')
	Order View
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
					<h4 class="name">{{ $order->$view_col }}</h4>

				</div>
			</div>
		</div>
		<div class="col-md-1 actions">
            @la_access("Orders", "edit")
                <a href="{{ url(config('laraadmin.adminRoute') . '/orders/'.$order->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
            @endla_access
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/orders') }}" data-toggle="tooltip" data-placement="right" title="Back to Orders"><i class="fa fa-chevron-left"></i></a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i>{{ trans('messages.general_info') }}</a></li>
		<li><a role="tab" data-toggle="tab" href="#tab-products" data-target="#tab-products"><i class="fa fa-bars"></i>{{ trans('messages.products') }}</a></li>
		<li><a role="tab" data-toggle="tab" href="#tab-payment" data-target="#tab-payment"><i class="fa fa-dollar"></i>{{ trans('order.payment') }}</a></li>
		<li><a role="tab" data-toggle="tab" href="#tab-product-series" data-target="#tab-product-series"><i class="fa fa-dollar"></i>{{ trans('order.product_series') }}</a></li>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>{{ trans('messages.general_info') }}</h4>
					</div>
					<div class="panel-body">
						@la_display($module, 'code')
						@la_display($module, 'customer_id')
						<div class="form-group">
							<label for="fee" class="col-md-2">{{ trans('order.type') }} :</label>
							<div class="col-md-10 fvalue">{{ trans('order.type_' . $order->type) }}</div>
						</div>
						<div class="form-group">
							<label for="fee" class="col-md-2">{{ trans('order.sub_type') }} :</label>
							<div class="col-md-10 fvalue">{{ trans('order.sub_type_' . $order->sub_type) }}</div>
						</div>
						@la_display($module, 'number_of_products')
						<div class="form-group">
							<label for="fee" class="col-md-2">{{ trans('order.subtotal') }} :</label>
							<div class="col-md-10 fvalue">{{ number_format($order->subtotal) }} đ</div>
						</div>
                        <div class="form-group">
                            <label for="fee" class="col-md-2">{{ trans('order.fee') }} :</label>
                            <div class="col-md-10 fvalue">{{ number_format($order->fee) }} đ</div>
                        </div>
						<div class="form-group">
							<label for="fee" class="col-md-2">{{ trans('order.discount') }} :</label>
							<div class="col-md-10 fvalue">{{ number_format($order->discount) }} đ</div>
						</div>
						<div class="form-group">
							<label for="fee" class="col-md-2">Giảm giá theo danh mục sản phẩm :</label>
							<div class="col-md-10 fvalue">{{ number_format($order->discount_by_cate) }} đ</div>
						</div>
						<div class="form-group">
							<label for="fee" class="col-md-2">{{ trans('order.total') }} :</label>
							<div class="col-md-10 fvalue">{{ number_format($order->total) }} đ</div>
						</div>
						<div class="form-group">
							<label for="fee" class="col-md-2">{{ trans('order.status') }} :</label>
							<div class="col-md-10 fvalue">
								<span>
									{!! $orderStatus->getStatusHTMLFormatted($order->status) !!}
									@can('update-status', $order)
										<button class="btn btn-sm btn-success" style="margin-left: 20px" data-toggle="modal" data-target="#UpdateStatusModal"><i class="fa fa-edit"></i></button>
									@endcan
								</span>
							</div>
						</div>
						<div class="form-group">
							<label for="fee" class="col-md-2">{{ trans('order.approve') }} :</label>
							<div class="col-md-10 fvalue">
								<span>
									{!! $orderStatus->getApproveHTMLFormatted($order->approve) !!}
									@can('approve-order', $order)
										<button class="btn btn-sm btn-success" style="margin-left: 20px" data-toggle="modal" data-target="#ApproveModal"><i class="fa fa-edit"></i></button>
									@endcan
								</span>
							</div>
						</div>
						@la_display($module, 'note')
					</div>
				</div>
			</div>
		</div>

		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-products">
			<table class="table table-bordered" style="margin-top: 40px">
				<thead>
				<tr class="success">
					<th rowspan="2">
						{{ trans('messages.index') }}<br />
						<label style="cursor: pointer"><input type="checkbox" class="ck_all" data-target="ck_item"/>{{ trans('button.check_all') }}</label>
					</th>
					<th class="text-center" rowspan="2">{{ trans('messages.sku') }}</th>
					<th class="text-center" rowspan="2">{{ trans('messages.name') }}</th>
					<th class="text-center" rowspan="2">{{ trans('messages.price') }}</th>
					<th class="text-center" colspan="2">{{ trans('messages.quantity') }}</th>
					<th class="text-center" rowspan="2">{{ trans('messages.unit') }}</th>
					<th class="text-center" rowspan="2">{{ trans('order.p_total') }}</th>
					<th></th>
				</tr>
				<tr class="success">
					<th class="text-center" >{{ trans('messages.new') }}</th>
					<th class="text-center" >{{ trans('messages.warranty') }}</th>
				</tr>
				</thead>
				<tbody>
				@foreach($order->orderProducts as $key => $orderProduct)
					<tr>
						<td>
							<input type="checkbox" class="ck_item" value="{{ $orderProduct->id }}">
							{{ $key + 1 }}
						</td>
						<td>{{ $orderProduct->product->sku }}</td>
						<td>{{ $orderProduct->product->name }}</td>
						<td class="text-right">{{ number_format($orderProduct->price) }} đ</td>
						<td class="text-right">{{ $orderProduct->quantity }}</td>
						<td class="text-right">{{ $orderProduct->w_quantity }}</td>
						<td class="text-right">{{ ucfirst($orderProduct->product->unit) }}</td>
						<td class="text-right">{{ number_format($orderProduct->total) }} đ</td>
					</tr>
				@endforeach
				<tr>
					<th colspan="7" class="text-right">{{ trans('order.total') }}</th>
					<td class="text-right">{{ number_format($order->subtotal) }} đ</td>
					<td></td>
				</tr>
				</tbody>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-payment">
				<div class="tab-content">
					<div class="panel infolist">
						<div class="panel-body">
							<div class="form-group">
								<label for="fee" class="col-md-2">{{ trans('order.total') }} :</label>
								<div class="col-md-10 fvalue">{{ number_format($order->total) }} đ</div>
							</div>
							<div class="form-group">
								<label for="fee" class="col-md-2">{{ trans('order.paid_amount') }} :</label>
								<div class="col-md-10 fvalue">
									{{ number_format(@$payment['amount']) }} đ
								</div>
							</div>
							<div class="form-group">
								<label for="fee" class="col-md-2">{{ trans('order.remaining_amount') }} :</label>
								<div class="col-md-10 fvalue remaining_amount">{{ number_format($order->total - @$payment['amount']) }} đ</div>
							</div>
							<div class="form-group">
								<label for="fee" class="col-md-2">{{ trans('order.total_debt') }} :</label>
								<div class="col-md-10 fvalue remaining_amount">{{ number_format(@$payment['total_debt']) }} đ</div>
							</div>
							<table class="table table-bordered">
								<thead>
									<tr class="success">
										<th>Ngân hàng</th>
										<th>Mã giao dịch</th>
										<th>Số tiền</th>
										<th>Phí giao dịch</th>
										<th>Ngày GD</th>
									</tr>
								</thead>
								<tbody>
							 		@foreach($transactions as $key => $paid)
										<tr>
											<td>{{ implode(' - ', [$paid->bank->name, $paid->bank->branch, $paid->bank->acc_name, $paid->bank->acc_id]) }}</td>
											<td>{{ $paid->trans_id }}</td>
											<td>{{ number_format(max($paid->received_amount, $paid->transfered_amount)) }} đ</td>
											<td>{{ number_format($paid->fee) }} đ</td>
											<td>{{ $paid->created_at->format('Y/m/d') }}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-product-series">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<table class="table table-bordered">
						<thead>
							<tr class="success">
								<th>Tên sản phẩm</th>
								<th>Số seri</th>
							</tr>
						</thead>
						<tbody>
							@forelse($productSeries as $seri)
								<tr>
									<td>{{ $seri->product->name }}</td>
									<td>{{ $seri->seri_number }}</td>
								</tr>
							@empty
								<tr><td colspan="7" class="text-center">No data</td></tr>
							@endforelse
							<tr>
								<td colspan="3">
									{{ $productSeries->fragment('tab-product-series')->links() }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	</div>
	</div>
</div>
<div class="modal fade" id="UpdateStatusModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{{ Form::open(['url' => route('order.status.update', ['id' => $order->id])]) }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">{{ trans('order.update_status') }}</h4>
				</div>

				<div class="modal-body">
					<div class="box-body">
						<div class="form-group">
							<label>{{ trans('order.status') }}</label>
							<select class="form-control select2" name="status" id="order-update-status-item">
								@foreach ($orderStatus->get() as $value => $label)
									<option value="{{ $value }}" @if ($value == $order->status) selected disabled @endif>{{ $label }}</option>
								@endforeach
							</select>
							<p class="text-danger">{!! trans('order.update_status_note') !!}</p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel') }}</button>
						<button type="submit" class="btn btn-success onetime-click" id="order-update-status-submit" disabled>{{ trans('button.save') }}</button>
					</div>
				</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
<div class="modal fade" id="ApproveModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{{ Form::open(['url' => route('order.status.update', ['id' => $order->id])]) }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">{{ trans('order.approve_order') }}</h4>
				</div>

				<div class="modal-body">
					<div class="box-body">
						<div class="form-group">
							<label>{{ trans('order.status') }}</label>
							<select class="form-control select2" name="approve" id="order-approve-item">
								@foreach ($orderStatus->getApprove() as $value => $label)
									<option value="{{ $value }}" @if ($value == $order->approve) selected disabled @endif>{{ $label }}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel') }}</button>
						<button type="submit" class="btn btn-success onetime-click" id="order-approve-submit" disabled>{{ trans('button.save') }}</button>
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
			$('#order-update-status-item').change(function () {
				$('#order-update-status-submit').prop('disabled', false);
			});
			$('#order-payment-form input[name="amount"]').change(function () {
				var amount = convertNumberInputValue($(this).val());
				var remain = {{ $order->total }} - amount;
				$('#order-payment-form .remaining_amount').html(remain.toLocaleString() + ' đ');
			});
			$('#order-approve-item').change(function () {
				$('#order-approve-submit').prop('disabled', false);
			});
		})
	</script>
@endpush
