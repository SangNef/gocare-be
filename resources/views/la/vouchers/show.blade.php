@extends('la.layouts.app')

@section('htmlheader_title')
	Xem chi tiết mã khuyến mại
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
					<h4 class="name">{{ $voucher->$view_col }}</h4>
				</div>
			</div>
		</div>
		<div class="col-md-3">
		</div>
		<div class="col-md-4">
		</div>
		<div class="col-md-1 actions">
			@la_access("Vouchers", "edit")
				<a href="{{ url(config('laraadmin.adminRoute') . '/vouchers/'.$voucher->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
			@endla_access
			
			@la_access("Vouchers", "delete")
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.vouchers.destroy', $voucher->id], 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
				{{ Form::close() }}
			@endla_access
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/vouchers') }}" data-toggle="tooltip" data-placement="right" title="Back to Vouchers"><i class="fa fa-chevron-left"></i></a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> Thông tin chung</a></li>
		<li class=""><a role="tab" data-toggle="tab" href="#tab-products" data-target="#tab-products"><i class="fa fa-clock-o"></i> Sản phẩm</a></li>
		<li class=""><a role="tab" data-toggle="tab" href="#tab-histories" data-target="#tab-histories"><i class="fa fa-clock-o"></i> Lịch sử sử dụng</a></li>
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
						@la_display($module, 'started_at')
						@la_display($module, 'ended_at')
						<div class="form-group">
							<label for="min_order_amount" class="col-md-2">Tiền hàng tối thiểu :</label>
							<div class="col-md-10 fvalue">{{ number_format($voucher->min_order_amount) }} đ</div>
						</div>
						<div class="form-group">
							<label for="min_order_amount" class="col-md-2">Kiểu phát hành :</label>
							<div class="col-md-10 fvalue">{{ $voucher->type == 1 ? 'Một mã sử dụng nhiều lần' : 'Nhiều mã chỉ sử dụng 1 lần' }}</div>
						</div>
						@la_display($module, 'code')
						@la_display($module, 'quantity')
						@la_display($module, 'percent')
						@la_display($module, 'max')
						@la_display($module, 'owner_id')
						@la_display($module, 'status')
					</div>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-products">
			<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
				<div class="tab-content">
					<div class="panel infolist">
						<div class="panel-default panel-heading">
							<h4>Sản phẩm</h4>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-12">
										@include('la.vouchers.products')
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-histories">
			<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
				<div class="tab-content">
					<div class="panel infolist">
						<div class="panel-default panel-heading">
							<div class="row">
								<div class="col-sm-9">
									<h4>Lịch sử sử dụng</h4>
								</div>
								<div class="col-sm-3">
									<a class="btn btn-warning btn-sm pull-right" href="{{ route('voucherhistory.export') }}">Xuất file Excel</a>
								</div>
							</div>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-12">
									@include('la.partials.created_filter', [
										'useExtraFilter' => false,
										'filterCreatedDate' => false,
										'filterDate' => false,
										'totals' => [],
										'filterColumns' => [],
										'filterOptions' => [],
										'extraForm' => '',
										'show_actions' => false,
										'listing_cols' => ['id', 'code', 'customer_id', 'used_at']
									])
									<table id="example1" class="table table-bordered" style="width: 100%">
										<thead>
										<tr class="success">
											<th>ID</th>
											<th>Mã</th>
											<th>Khách hàng sử dụng</th>
											<th>Thời gian sử dụng</th>
										</tr>
										</thead>
										<thead id="filter_bar">
										<tr class="success">
											<th colname="id">ID</th>
											<th colname="code">Ảnh</th>
											<th colname="customer_id">Khách hàng</th>
											<th colname="used_at">Thời gian sử dụng</th>
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
		</div>
	</div>
	</div>
	</div>
</div>
@endsection
@push('scripts')
	<script>
		var url = "{!!  url(config('laraadmin.adminRoute')) . '/voucherhistory_dt_ajax?' . http_build_query([
		'listing_cols' => 'id,code,customer_id,used_at',
		'voucher_id' => $voucher->id,
]) !!}";

	</script>
@endpush