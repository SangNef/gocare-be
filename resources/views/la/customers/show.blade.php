@extends('la.layouts.app')

@section('htmlheader_title')
	Customer View
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
				<div class="col-md-6">
					<h4 class="name">{{ $customer->$view_col }}</h4>
				</div>
				<div class="col-md-3 actions">
					@la_access("Customers", "edit")
						<a href="{{ url(config('laraadmin.adminRoute') . '/customers/'.$customer->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
					@endla_access
					
					@la_access("Customers", "delete")
						{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.customers.destroy', $customer->id], 'method' => 'delete', 'style'=>'display:inline']) }}
							<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
						{{ Form::close() }}
					@endla_access
				</div>
			</div>
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/customers') }}" data-toggle="tooltip" data-placement="right" title="Back to Customers"><i class="fa fa-chevron-left"></i></a></li>
		<li @if (!request('report_id')) class="active" @endif><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> Thông tin chung</a></li>
		@if (!$customer->customer_parent_id)
			<li @if (request('report_id')) class="active" @endif><a role="tab" data-toggle="tab" href="#tab-report" data-target="#tab-report"><i class="fa fa-bars"></i> Đối soát</a></li>
		@endif
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane @if (!request('report_id')) active @endif fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>Thông tin chung</h4>
					</div>
					<div class="panel-body">
						@la_display($module, 'name')
						@la_display($module, 'email')
						@la_display($module, 'phone')
						@la_display($module, 'address')
						@la_display($module, 'parent_id')
						@la_display($module, 'username')
						@la_display($module, 'debt_in_advance')
						@la_display($module, 'debt_total')
						@la_display($module, 'note')
						@la_display($module, 'group_id')
					</div>
				</div>
			</div>
		</div>
		@if (!$customer->customer_parent_id)
			<div role="tabpanel" class="tab-pane @if (request('report_id')) active @endif fade in" id="tab-report">
				<div class="tab-content">
					<div class="row">
						<div class="col-12 col-sm-12">
							<div class="panel infolist">
								<div class="panel-default panel-heading">
									<div class="row">
										<div class="col-sm-6">
											<h4>
												Đối soát doanh thu tháng
												<select class="select2" onchange="window.location.href= '/{{config('laraadmin.adminRoute')}}/customers/{{ $customer->id  }}?report_id=' + this.value">
													@foreach($reports as $r)
														<option value="{{ $r->id }}" @if ($r->id === @$report->id) selected @endif>{{ $r->month }}</option>
													@endforeach
												</select>
											</h4>
										</div>
										<div class="col-sm-6 text-right">
											@if ($report && $report->id && $report->accepted_at)
												<span class="label label-primary">Đã đối soát - {{ @$report->accepted_at }}</span>
											@elseif ($report && $report->id)
												<a class="btn btn-primary" href="{{ route('customer.report.accept', ['id' => $customer->id, 'reportId' => $report->id]) }}">Xác nhận</a>
											@endif
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					@if (!empty($report->data))
						<div class="row">
							<div class="col-12 col-md-6">
								<div class="panel infolist">
									<div class="panel-default panel-heading">
										<h4>
											Chiết khấu theo danh mục sản phẩm (không nhập hàng)
										</h4>
									</div>
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<thead>
											<tr class="success">
												<th>STT</th>
												<th>Tên danh mục sản phẩm</th>
												<th>Chiết khấu</th>
												<th>Số lượng</th>
												<th>Tổng tiền</th>
												<th>Thành tiền</th>
											</tr>
											</thead>
											<tbody>
											@foreach(@$report->data['discount_by_cate'] as $key => $cate)
												<tr>
													<td>{{ $key+1 }}</td>
													<td>{{ $cate['name'] }}</td>
													<td>{{ @$cate['discount_text'] }}</td>
													<td>{{ number_format($cate['quantity']) }}</td>
													<td>{{ number_format($cate['amount']) }}đ</td>
													<td>{{ number_format($cate['total_discount']) }}đ</td>
												</tr>
											@endforeach
											</tbody>
										</table>
									</div>
								</div>
								<div class="panel infolist">
									<div class="panel-default panel-heading">
										<h4>Kích hoạt dịch vụ</h4>
									</div>
									<div class="panel-body">
										<div class="form-group">
											<label for="name" class="col-md-2">Số lượng:</label>
											<div class="col-md-10 fvalue">{{ number_format(@$report->data['activation']['amount']) }}</div>
										</div>
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng tiền:</label>
											<div class="col-md-10 fvalue">{{ number_format((int) @$report->data['activation']['total']) }}đ</div>
										</div>
									</div>
								</div>
								<div class="panel infolist">
									<div class="panel-default panel-heading">
										<h4>Kích hoạt dịch vụ qua tài khoản affiliate</h4>
									</div>
									<div class="panel-body">
										<div class="form-group">
											<label for="name" class="col-md-2">Số lượng:</label>
											<div class="col-md-10 fvalue">{{ number_format(@$report->data['affiliate_activation']['amount']) }}</div>
										</div>
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng tiền:</label>
											<div class="col-md-10 fvalue">{{ number_format((int) @$report->data['affiliate_activation']['total']) }}đ</div>
										</div>
									</div>
								</div>

							</div>
							<div class="col-12 col-md-6">
								<div class="panel infolist">
									<div class="panel-default panel-heading">
										<h4>Đơn hàng không nhập hàng</h4>
									</div>
									<div class="panel-body">
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng đơn hàng:</label>
											<div class="col-md-10 fvalue">{{ number_format(@$report->data['orders']['amount']) }}</div>
										</div>
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng tiền:</label>
											<div class="col-md-10 fvalue">{{ number_format((int) @$report->data['orders']['total']) }}đ</div>
										</div>
									</div>
								</div>
								<div class="panel infolist">
									<div class="panel-default panel-heading">
										<h4>Đơn hàng thanh toán online</h4>
									</div>
									<div class="panel-body">
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng đơn hàng:</label>
											<div class="col-md-10 fvalue">{{ number_format(@$report->data['online_orders']['amount']) }}</div>
										</div>
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng tiền:</label>
											<div class="col-md-10 fvalue">{{ number_format((int) @$report->data['online_orders']['total']) }}đ</div>
										</div>
									</div>
								</div>
								<div class="panel infolist">
									<div class="panel-default panel-heading">
										<h4>Đơn hàng affiliate</h4>
									</div>
									<div class="panel-body">
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng đơn hàng:</label>
											<div class="col-md-10 fvalue">{{ number_format(@$report->data['affiliate_orders']['amount']) }}</div>
										</div>
										<div class="form-group">
											<label for="name" class="col-md-2">Tổng tiền:</label>
											<div class="col-md-10 fvalue">{{ number_format((int) @$report->data['affiliate_orders']['total']) }}đ</div>
										</div>
									</div>
								</div>
							</div>

						</div>
					@else
						<div class="row">
							<div class="col-12 col-sm-12">
								<div class="panel infolist">
									<div class="panel-default panel-heading">
										<h4 class="text-center">
											Chưa có dữ liệu
										</h4>
									</div>
								</div>
							</div>
						</div>
					@endif
				</div>
			</div>
		@endif
	</div>
	</div>
	</div>
</div>
@endsection
