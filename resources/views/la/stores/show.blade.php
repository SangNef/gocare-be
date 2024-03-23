@extends('la.layouts.app')

@section('htmlheader_title')
	Store View
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
					<h4 class="name">{{ $store->$view_col }}</h4>
					<div class="row stats">
						<div class="col-md-4"><i class="fa fa-facebook"></i> 234</div>
						<div class="col-md-4"><i class="fa fa-twitter"></i> 12</div>
						<div class="col-md-4"><i class="fa fa-instagram"></i> 89</div>
					</div>
					<p class="desc">Test Description in one line</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="dats1"><div class="label2">Admin</div></div>
			<div class="dats1"><i class="fa fa-envelope-o"></i> superadmin@gmail.com</div>
			<div class="dats1"><i class="fa fa-map-marker"></i> Pune, India</div>
		</div>
		<div class="col-md-4">
			<!--
			<div class="teamview">
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user1-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user2-160x160.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user3-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user4-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user5-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user6-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user7-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user8-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user5-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user6-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user7-128x128.jpg') }}" alt=""></a>
			</div>
			-->
			<div class="dats1 pb">
				<div class="clearfix">
					<span class="pull-left">Task #1</span>
					<small class="pull-right">20%</small>
				</div>
				<div class="progress progress-xs active">
					<div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
						<span class="sr-only">20% Complete</span>
					</div>
				</div>
			</div>
			<div class="dats1 pb">
				<div class="clearfix">
					<span class="pull-left">Task #2</span>
					<small class="pull-right">90%</small>
				</div>
				<div class="progress progress-xs active">
					<div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 90%" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
						<span class="sr-only">90% Complete</span>
					</div>
				</div>
			</div>
			<div class="dats1 pb">
				<div class="clearfix">
					<span class="pull-left">Task #3</span>
					<small class="pull-right">60%</small>
				</div>
				<div class="progress progress-xs active">
					<div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 60%" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
						<span class="sr-only">60% Complete</span>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-1 actions">
			@la_access("Stores", "edit")
				<a href="{{ url(config('laraadmin.adminRoute') . '/stores/'.$store->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
			@endla_access
			
			@la_access("Stores", "delete")
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.stores.destroy', $store->id], 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
				{{ Form::close() }}
			@endla_access
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/stores') }}" data-toggle="tooltip" data-placement="right" title="Back to Stores"><i class="fa fa-chevron-left"></i></a></li>
		<li @if (!request('tp'))class="active"@endif><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> General Info</a></li>
		<li @if (request('tp'))class="active"@endif><a role="tab" data-toggle="tab" href="#tab-products" data-target="#tab-products"><i class="fa fa-bars"></i> Sản phẩm</a></li>
		<li><a role="tab" data-toggle="tab" href="#tab-shipping" data-target="#tab-shipping"><i class="fa fa-bars"></i> Vận chuyển</a></li>
		<li><a role="tab" data-toggle="tab" href="#tab-observes" data-target="#tab-observes"><i class="fa fa-bars"></i> Theo dõi công nợ</a></li>
		<li><a role="tab" data-toggle="tab" href="#tab-setting" data-target="#tab-setting"><i class="fa fa-bars"></i> Cài đặt</a></li>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane @if (!request('tp')) active @endif fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>General Info</h4>
					</div>
					<div class="panel-body">
						@la_display($module, 'name')
						@la_display($module, 'address')
						@la_display($module, 'started_at')
						@la_display($module, 'status')
						@if (!auth()->user()->store_id)
							@la_display($module, 'owner_id')
						@endif
					</div>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white @if (request('tp')) active @endif" id="tab-products">
			<form action="" id="product-search-form" style="margin-bottom: 20px">
				<div class="row">
					<div class="col-md-2">
						<input class="form-control" id="product-search-text" name="sku" placeholder="Nhập SKU, Tên sản phẩm" value="{{ request('sku') }}">
					</div>
					<div class="col-md-2">
						<select name="pc_ids[]" multiple class="form-control ajax-select filter-category-id" model="productcategory" placeholder="{{ trans('messages.product_category') }}">
						</select>
					</div>
					<div class="col-md-2 form-group">
						<select name="product_search" class="form-control">
							<option value="" checked>Tất cả</option>
							<option value="minimun" >Chỉ hiển thị sản phẩm ít hơn hạn mức</option>
							<option value="in_stock" checked>Chỉ hiển thị sản phẩm còn hàng</option>
							<option value="out_of_stock" checked>Chỉ hiển thị sản phẩm hết hàng</option>
						</select>
					</div>
					<div class="col-md-3">
						@la_access("Stores", "view")
							<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#viewProductQuantity" type="button">Cập nhập số lượng</button>
						@endla_access
						<a href="{{ route('store.export-products', ['id' => $store->id]) }}" class="btn btn-sm btn-success" type="button">Xuất Excel</a>
						<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#print-export-products">In báo giá</button>
					</div>
				</div>
			</form>
			<table id="example1" class="table table-bordered">
				<thead>
					<tr class="success">
						<th>ID</th>
						<th>SKU</th>
						<th>Tên sản phẩm</th>
						<th>Trạng thái</th>
						<th>Số lượng sản phẩm</th>
						<th>Hàng mới</th>
						<th>Hàng cũ</th>
						<th>Thông báo</th>
					</tr>
				</thead>
				<tbody id="store-products">
					<tr>
						<td colspan="7" class="text-center"><i class="fa fa-refresh"></i></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane fade in" id="tab-shipping">
			<div class="tab-content">
				<div class="panel-default panel-heading">
					<h4>Cài đặt chia sẻ cho cộng tác viên đặt hàng</h4>
				</div>
				{!! Form::open(['url' => route('store.sharing', ['id' => $store->id]), 'id' => 'store-sharing']) !!}
				<div class="modal-body">
					<div class="box-body">
						<div class="form-group">
							<label for="role">Cho phép cộng tác viên đặt hàng từ kho: <input type="checkbox" name="sharing" value="1" @if ($store->sharing) checked @endif/></label>
						</div>
					</div>
				</div>
				{!! Form::close() !!}
				<div class="panel-default panel-heading">
					<h4>Đối tác vận chuyển</h4>
				</div>
				<div class="panel infolist">
					<table id="example1" class="table table-bordered">
						<thead>
						<tr class="success">
							<th>Đối tác</th>
							<th>Trạng thái</th>
							<th>Thông tin kết nối</th>
						</tr>
						</thead>
						<tbody>
							@foreach ($providers as $partner => $provider)
								<tr>
									<td>
										{{ $provider['name'] }}
									</td>
									<td>
										@if ($provider['status'] === null)
											<span class="label label-warning">Chưa cài đặt</span>
										@elseif ($provider['status'] == 1)
											<span class="label label-success">Bật</span>
										@else
											<span class="label label-danger">Tắt</span>
										@endif
									</td>
									<td>
										{!! Form::open(['url' => route('store.update-shipping', ['id' => $store->id, 'provider' => $partner]), 'class' => 'store-update-shipping']) !!}
											@foreach($provider['api_connection'] as $key => $value)
												@if ($key == 'discount_type')
													<div class="row" style="margin-bottom: 5px">
														<div class="col-sm-6">{{ trans('store_shipping.' . $key) }}</div>
														<div class="col-sm-6">
															<select class="form-control" name="{{ $key }}">
																<option value="">Tắt</option>
																<option value="1" @if ($value == 1) selected @endif>Tăng (cố định)</option>
																<option value="2" @if ($value == 2) selected @endif>Tăng (phần trăm)</option>
																<option value="3" @if ($value == 3) selected @endif>Giảm (cố định)</option>
																<option value="4" @if ($value == 4) selected @endif>Giảm (phần trăm)</option>
															</select>
														</div>
													</div>
												@elseif ($partner === 'vnpost' && $key === 'sender_list')
													<div class="row" style="margin-bottom: 5px">
														<div class="col-sm-12">
															{{ trans('store_shipping.' . $key) }}
															<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#VnpostSender" type="button">Thêm</button>
														</div>
														<div class="col-sm-12">
															<table class="table table-bordered">
																<thead>
																	<tr class="success">
																		<th width="20%">Tên</th>
																		<th width="15%">SĐT</th>
																		<th width="15%">Địa chỉ</th>
																		<th width="15%">Xã/Phường</th>
																		<th width="15%">Quận/Huyện</th>
																		<th width="15%">Tỉnh/Thành phố</th>
																		<th width="5%"></th>
																	</tr>
																</thead>
																<tbody id="vnpost-senders">
																	<tr><td colspan="7" class="text-center">No data</td></tr>
																</tbody>
															</table>
														</div>
													</div>
												@else
													<div class="row" style="margin-bottom: 5px; @if($partner === 'vnpost' && $key === 'vnpostDefaultStoreId') display: none; @endif">
														<div class="col-sm-6">{{ trans('store_shipping.' . $key) }}</div>
														<div class="col-sm-6"><input type="text" class="form-control" name="{{ $key }}" value="{{ $value }}"></div>
													</div>
												@endif
											@endforeach
											<button class="btn btn-primary btn-sm">Cài đặt</button>
										{!! Form::close() !!}
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					<hr />
					<div class="panel-default panel-heading">
						<h4>Khách hàng ko áp dụng tăng/giảm giá : <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#UpdateCustomerExcludeModal" type="button">Thêm</button></h4>
					</div>
					<table class="table table-bordered">
						<thead>
						<tr class="success">
							<th>ID</th>
							<th>Tên khách hàng</th>
							<th>SĐT</th>
							<th></th>
						</tr>
						</thead>
						<tbody id="customer-excludes">
						<tr>
							<td colspan="4" class="text-center"><i class="fa fa-refresh"></i></td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-observes">
			<div class="panel-default panel-heading">
				<h4>Công nợ kho theo khách hàng : <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#AddObserveModal" type="button">Thêm</button></h4>
			</div>
			<table id="example1" class="table table-bordered">
				<thead>
				<tr class="success">
					<th>ID</th>
					<th>Khách hàng</th>
					<th>Số điện thoại</th>
					<th>Công nợ hiện tại</th>
					<th></th>
				</tr>
				</thead>
				<tbody id="store-observes">
					<tr>
						<td colspan="5" class="text-center"><i class="fa fa-refresh"></i></td>
					</tr>
				</tbody>
			</table>
			<div class="panel-default panel-heading audit-heading" style="display: none">
				<h4>Lịch sử công nợ</h4>
			</div>
			<div class="audit-heading" style="display: none">
				<table id="example1" class="table table-bordered">
					<table id="example1" class="table table-bordered">
						<thead>
						<tr class="success">
							<th>Đơn hàng</th>
							<th>Số tiền</th>
							<th>Công nợ</th>
							<th>Thời gian</th>
						</tr>
						</thead>
						<tbody id="observer-audits" >
							<tr>
								<td colspan="4" class="text-center"><i class="fa fa-refresh"></i></td>
							</tr>
						</tbody>
				</table>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-setting">
			<div class="panel-default panel-heading">
				<h4>Cài đặt</h4>
			</div>
			{!! Form::open(['url' => route('store.product.setting', ['id' => $store->id]), 'id' => 'store-setting-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="role">Email nhận thông báo sản phẩm sắp hết :</label>
						<textarea class="form-control" name="email_product_min_notification" rows="5">{{ @$setting['email_product_min_notification'] }}</textarea>
						<span class="small">* Có thể nhập nhiều email, mỗi email 1 dòng</span>
					</div>
					<div class="form-group">
						<label for="role">Group Tele nhận thông báo :</label>
						<textarea class="form-control" name="tele_product_min_notification" rows="1">{{ @$setting['tele_product_min_notification'] }}</textarea>
					</div>
					<div class="form-group">
						<label for="role">Group Tele nhận thông báo đơn hàng từ FE :</label>
						<textarea class="form-control" name="tele_fe_order_notification" rows="1">{{ @$setting['tele_fe_order_notification'] }}</textarea>
					</div>
					<div class="form-group">
						<label for="role">Group Tele nhận thông báo đơn hàng từ BE :</label>
						<textarea class="form-control" name="tele_be_order_notification" rows="1">{{ @$setting['tele_be_order_notification'] }}</textarea>
					</div>
					<div class="form-group">
						<label for="role">Group Tele nhận thông báo đơn hàng bảo hành :</label>
						<textarea class="form-control" name="tele_warranty_order_notification" rows="1">{{ @$setting['tele_warranty_order_notification'] }}</textarea>
					</div>
					<div class="form-group">
						<label for="role">Nhóm Cộng tác viên :</label>
						<select class="form-control ajax-select" name="commission_groups[]" model="group" multiple>
							@foreach($selectedCommissionGroup as $id => $selected)
								<option value="{{ $id }}" selected="selected">{{ $selected }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="banks">Ngân hàng nhận tiền thanh toán online :</label>
						<select class="form-control ajax-select" name="online_receiver_bank" model="banks">
							@if ($selectedBank)
								<option value="{{ $selectedBank->id }}" selected="selected">{{ $selectedBank->name . '-' . $selectedBank->acc_name . '-' . $selectedBank->acc_id }}</option>
							@endif
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				{!! Form::submit( 'Submit', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@la_access("Stores", "view")
<div class="modal fade" id="UpdateQuantityModal" role="dialog" aria-labelledby="UpdateQuantityModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Cập nhập số lượng sản phẩm</h4>
			</div>
			{!! Form::open(['url' => route('store.update-quantity', ['id' => $store->id]), 'id' => 'store-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="role">Sản phẩm :</label>
						<select class="form-control ajax-select" extra_param="1" model="product" name="product_id" required="required">
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{!! Form::submit( 'Submit', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="UpdateCustomerExcludeModal" role="dialog" aria-labelledby="UpdateCustomerExcludeModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm khách hàng ko áp dụng tăng/giảm giá :</h4>
			</div>
			{!! Form::open(['url' => route('store.exclude-customer', ['id' => $store->id]), 'id' => 'store-add-excluded-customer']) !!}
				<div class="modal-body">
					<div class="box-body">
						<label for="role">Khách hàng :</label>
						<input type="hidden" id="customer_store_id" name="store_id" value="{{ $store->id }}">
						<select class="form-control ajax-select" model="customer" name="customer_id" lookup="customer_store_id" id="store_excluded_customer">
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					{!! Form::submit( 'Thêm', ['class'=>'btn btn-success']) !!}
				</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="AddObserveModal" role="dialog" aria-labelledby="AddObserveModalModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm khách hàng theo dõi công nợ :</h4>
			</div>
			{!! Form::open(['url' => route('store.observer.add', ['id' => $store->id]), 'id' => 'store-add-observe']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="role">Khách hàng :</label>
						<select class="form-control ajax-select" model="customer" name="customer_id" id="store_observer">
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{!! Form::submit( 'Thêm', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="updateProductMinimum" role="dialog" aria-labelledby="updateProductMinimumModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Nhận thông báo nếu số lượng sản phẩm nhỏ hơn :</h4>
			</div>
			{!! Form::open(['url' => route('store.product.minimum', ['id' => $store->id]), 'id' => 'store-product-minimum']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="role">Số lượng :</label>
						<input type="hidden" name="product_id" value="">
						<input type="number" class="form-control" name="min" value="0">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{!! Form::submit( 'Thêm', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="viewProductQuantity" role="dialog" aria-labelledby="viewProductQuantityModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Số lượng sản phẩm theo thuộc tính sản phẩm :</h4>
			</div>
			{!! Form::open(['url' => route('store.product.group-attribute-extra.save', ['id' => $store->id]), 'id' => 'group-attribute-extra']) !!}
			<div class="modal-body">
				<div class="form-group product-select">
					<label for="role">Sản phẩm :</label>
					<select class="form-control ajax-select" extra_param="1" model="product">
					</select>
				</div>
				<div class="box-body">

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{!! Form::submit( 'Cập nhập', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="viewProductPrice" role="dialog" aria-labelledby="viewProductPriceModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Cài đặt giá sản phẩm theo nhóm khách hàng :</h4>
			</div>
			{!! Form::open(['url' => route('store.product.product-price.save', ['id' => $store->id]), 'id' => 'product-price']) !!}
			<div class="modal-body">
				<div class="box-body">

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{!! Form::submit( 'Cập nhập', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="print-export-products" role="dialog" aria-labelledby="myModalLabel">
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
							<input type="hidden" id="store_id" name="store_id" value="{{ $store->id }}">
							<select required name="customer_id" class="form-control ajax-select submit-required" lookup="store_id" model="customer">
							</select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="col-sm-12" style="text-align: right">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-success onetime-click" id="btn-export">In</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@include('la.partials.modals.vnpost_create_sender')
@endla_access

@endsection
@push('scripts')
<script>
	function loadVnpostSenders(page = 1)
	{
		$.ajax({
			url: '{{ route('store.vnpost-senders.get', ['id' => $store->id]) }}',
			data: {
				page: page
			},
			success: function (data) {
				$('#vnpost-senders').html(data);
			}
		});
	}
	function loadExcludedCustomers(page)
	{
		$.ajax({
			url: '{{ route('store.excluded-customers.get', ['id' => $store->id]) }}',
			data: {
				page: page
			},
			success: function (data) {
				$('#customer-excludes').html(data);
			}
		});
	}
	var pRequest;
	var currentProductPage;
	function loadProducts(page)
	{
		if (typeof pRequest !== 'undefined') {
			pRequest.abort();
		}
		currentProductPage = page;
		const formData = $('#product-search-form').serializeObject();
		pRequest = $.ajax({
			url: '{{ route('store.products.get', ['id' => $store->id]) }}',
			data: {
				page: page,
				...formData
			},
			success: function (data) {
				$('#store-products').html(data);
			}
		});
	}

	function loadObserves()
	{
		$.ajax({
			url: '{{ route('store.observes.get', ['id' => $store->id]) }}',
			success: function (data) {
				$('#store-observes').html(data);
			}
		});
	}

	var currentOb = 0;
	function loadObserverAudit(page = 1)
	{
		$.ajax({
			url: '{{ route('store.observes.audit.get', ['id' => $store->id]) }}',
			data: {
				observer_id: currentOb,
				page: page
			},
			success: function (data) {
				$('#observer-audits').html(data);
			}
		});
	}

	function loadProductGroupAttribute(productId)
	{
		var url = '{{ route('store.product.group-attribute-extra', ['id' => $store->id]) }}?product_id=' + productId;
		$('#viewProductQuantity .box-body').html('');
		$('#viewProductQuantity input[type="submit"]').prop('disabled', true);
		$.ajax({
			url: url,
			success: function (data) {
				$('#viewProductQuantity .box-body').html(data);
				if ($('#viewProductQuantity input[name="product_id"]').length > 0)
				{
					$('#viewProductQuantity input[type="submit"]').prop('disabled', false);
				}
			},
			error: function (data) {
				alert('There is an error');
			}
		})
	}
	
	function loadProductPrice(productId)
	{
		var url = '{{ route('store.product.product-price', ['id' => $store->id]) }}?product_id=' + productId;
		$('#viewProductPrice .box-body').html('');
		$.ajax({
			url: url,
			success: function (data) {
				$('#viewProductPrice .box-body').html(data);
			},
			error: function (data) {
				alert('There is an error');
			}
		})
	}

	$(function () {
		$('.store-update-shipping').submit(function (e) {
			e.preventDefault();
			var form = $(this);
			form.find('button').prop('disabled', true);
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				type: 'POST',
				success: function (data) {
					form.find('button').prop('disabled', false);
					//alert('Cập nhập thành công');
					//window.location.reload();
				},
				error: function (data) {
					alert('There is an error');
				}
			})
		});
		$('#store-add-excluded-customer').submit(function (e) {
			e.preventDefault();
			var form = $(this);
			form.find('input[type="submit"]').prop('disabled', true);
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				type: 'POST',
				success: function (data) {
					form.find('input[type="submit"]').prop('disabled', false);
					loadExcludedCustomers(1);
					$('#UpdateCustomerExcludeModal').modal('hide');
					$('#store_excluded_customer').val('');
				},
				error: function (data) {
					alert('There is an error');
					location.reload();
				}
			})
		});
		$(document).on('click', '.remove-excluded-customer', function () {
			if (confirm('Bạn có chắc muốn xoá khách hàng này không')) {
				$(this).prop('disabled', true);
				var id = $(this).val();
				$.ajax({
					url: '{{ route('store.remove-excluded-customer', ['id' => $store->id]) }}',
					data: {
						customer_id: id
					},
					success: function (data) {
						loadExcludedCustomers(1);
					},
					error: function (data) {
						alert('There is an error');
						location.reload();
					}
				})
			}
		});
		loadVnpostSenders();
		loadExcludedCustomers(1);
		loadProducts(1);
		loadObserves();
		// $('#product-search-text').keyup(function () {
		// 	loadProducts(1);
		// })
		// $('#product-search-minimum, #product-search-out-of-stock').change(function () {
		// 	loadProducts(1);
		// })
		$('#product-search-form input,#product-search-form select').change(function () {
			loadProducts(1);
		})
		$(document).on('click', '#store-products .pagination a', function (e) {
			e.preventDefault();
			var page = $(this).text();
			if (page == '«' || page == '»') {
				var currechangentPage = $('#store-products .pagination li.active span').text();
				page = page == '<<' ? parseInt(currentPage) - 1 : parseInt(currentPage) + 1;
			}
			loadProducts(page);
		})

		$('#store-add-observe').submit(function (e) {
			e.preventDefault();
			var form = $(this);
			form.find('input[type="submit"]').prop('disabled', true);
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				type: 'POST',
				success: function (data) {
					form.find('input[type="submit"]').prop('disabled', false);
					loadObserves(1);
					$('#AddObserveModal').modal('hide');
					$('#store_observer').val('');
					$('#observer_balance').val(0);
				},
				error: function (data) {
					alert('There is an error');
					location.reload();
				}
			})
		});

		$(document).on('click', '.remove-observer', function () {
			if (confirm('Bạn có chắc muốn xoá khách hàng này không')) {
				$(this).prop('disabled', true);
				var id = $(this).val();
				$.ajax({
					url: '{{ route('store.observer.remove', ['id' => $store->id]) }}',
					data: {
						observer_id: id
					},
					success: function (data) {
						loadObserves();
					},
					error: function (data) {
						alert('There is an error');
						location.reload();
					}
				})
			}
		});
		$(document).on('click', '.observer-audit', function () {
			$('.audit-heading').show();
			var id = $(this).val();
			currentOb = id;
			loadObserverAudit(1);
		});

		$(document).on('click', '#observer-audits .pagination a', function (e) {
			e.preventDefault();
			var page = $(this).text();
			if (page == '«' || page == '»') {
				var currentPage = $('#observer-audits .pagination li.active span').text();
				page = page == '<<' ? parseInt(currentPage) - 1 : parseInt(currentPage) + 1;
			}
			loadObserverAudit(page);
		})
		$('#updateProductMinimum').on('shown.bs.modal', function (e) {
			var productId = $(e.relatedTarget).attr('id').substr(8);
			$('#updateProductMinimum').find('input[name="product_id"]').val(productId);
		});
		$('#updateProductMinimum form').submit(function (e) {
			e.preventDefault();
			var form = $(this);
			form.find('input[type="submit"]').prop('disabled', true);
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				type: 'POST',
				success: function (data) {
					form.find('input[type="submit"]').prop('disabled', false);
					loadProducts(currentProductPage);
					$('#updateProductMinimum').modal('hide');
					$('#updateProductMinimum').find('input[name="product_id"]').val(0);
					$('#updateProductMinimum').find('input[name="min"]').val(0);
				},
				error: function (data) {
					alert('There is an error');
				}
			})
		})

		$('#viewProductQuantity').on('shown.bs.modal', function (e) {
			var id = $(e.relatedTarget).attr('id');
			var productId = id.length > 17 ? $(e.relatedTarget).attr('id').substr(17) : '';
			if (!productId) {
				$('#viewProductQuantity .product-select').show();
			} else {
				$('#viewProductQuantity .product-select').hide();
				loadProductGroupAttribute(productId);
			}
		});
		$('#viewProductQuantity .product-select select').change(function (e) {
			var productId = $(this).val();
			loadProductGroupAttribute(productId);
		})

		$('#viewProductQuantity form').submit(function (e) {
			e.preventDefault();
			var form = $(this);
			form.find('input[type="submit"]').prop('disabled', true);
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				type: 'POST',
				success: function (data) {
					form.find('input[type="submit"]').prop('disabled', false);
					loadProducts(currentProductPage);
					$('#viewProductQuantity').modal('hide');
					$('#viewProductQuantity .box-body').html('');
				},
				error: function (data) {
					alert('There is an error');
				}
			})
		})

		$('#viewProductPrice').on('shown.bs.modal', function (e) {
			var id = $(e.relatedTarget).attr('id');
			var productId = id.length > 14 ? $(e.relatedTarget).attr('id').substr(14) : '';
			if (!productId) {
				$('#viewProductPrice .product-select').show();
			} else {
				$('#viewProductPrice .product-select').hide();
				loadProductPrice(productId);
			}
		});

		$('#viewProductPrice form').submit(function (e) {
			e.preventDefault();
			var form = $(this);
			form.find('input[type="submit"]').prop('disabled', true);
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				type: 'POST',
				success: function (data) {
					form.find('input[type="submit"]').prop('disabled', false);
					$('#viewProductPrice').modal('hide');
					$('#viewProductPrice .box-body').html('');
				},
				error: function (data) {
					alert('There is an error');
				}
			})
		});

		$('#VnpostSender').on('hidden.bs.modal', function (e) {
			loadVnpostSenders();
			VnpostSenderForm.prop('action', '{{ route('store.vnpost-senders.post', ['id' => $store->id]) }}');
			VnpostSenderForm.prop('method', 'POST');
			VnpostSenderForm.find('input[name="SenderId"]').remove();
		});

		$(document).on('click', '#vnpost-senders .pagination a', function (e) {
			e.preventDefault();
			let page = $(this).text();
			if (page == '«' || page == '»') {
				let currentPage = $('#vnpost-senders .pagination li.active span').text();
				page = page == '<<' ? parseInt(currentPage) - 1 : parseInt(currentPage) + 1;
			}
			loadVnpostSenders(page);
		});
		
		$('#store-sharing input').change(function(){
			$('#store-sharing').submit();
		});

		$('#store-sharing').submit(function (e) {
			e.preventDefault();
			var form = $(this);
			$.ajax({
				url: form.attr('action'),
				data: form.serialize(),
				type: 'POST',
				success: function (data) {
					
				},
				error: function (data) {
					alert('There is an error');
				}
			})
		})

		$(document).on('click', '#vnpost-senders .remove-sender', function () {
			if (confirm('Bạn có chắc muốn xoá địa chỉ này không?')) {
				$(this).prop('disabled', true);
				let id = $(this).val();
				$.ajax({
					url: '{{ route('store.vnpost-senders.remove', ['id' => $store->id]) }}',
					data: {
						sender_id: id
					},
					success: function (data) {
						loadVnpostSenders();
					},
					error: function (data) {
						alert('There is an error');
						location.reload();
					}
				})
			}
		});

		$(document).on('click', '#vnpost-senders .edit-sender', function () {
			const senderData = JSON.parse($(this).val());
			const vnpostModal = VnpostSenderForm.parents('#VnpostSender');
			vnpostModal.modal('show');
			VnpostSenderForm.find('input[type="submit"]').prop('disabled', true);
			VnpostSenderForm.prop('action', '{{ route('store.vnpost-senders.update', ['id' => $store->id]) }}');
			VnpostSenderForm.prop('method', 'PUT');

			for (key in senderData) {
				if (['SenderFullname', 'SenderTel', 'SenderAddress'].includes(key)) {
					VnpostSenderForm.find(`input[name="${key}"]`).val(senderData[key]).change();
				}
			}
			const { SenderId, SenderProvinceId, SenderDistrictId, SenderWardId, default: isDefault } = senderData;
			VnpostSenderForm.find('input[name="default"]').prop('checked', isDefault);
			VnpostSenderForm.append(`<input type="hidden" name="SenderId" value="${SenderId}" />`);

			VnpostSenderForm.find('select.sender-province').val(SenderProvinceId).change();
			$.when(provinceDeferred).then(function() {
				VnpostSenderForm.find('select.sender-district').val(SenderDistrictId).change();
				$.when(districtDeferred).then(function() {
					VnpostSenderForm.find('select.sender-ward').val(SenderWardId).change();
					VnpostSenderForm.find('input[type="submit"]').prop('disabled', false);
				})
			})
		});

		$('#print-export-products form').submit(function (e) {
			e.preventDefault();
			const data = {...$(this).serializeObject(), ...$('#product-search-form').serializeObject()};
			if (checkRequiredInputs('print-export-products')) {
				let url = objectToQueryString("{{ route('products.export') }}", data);
				let iframe = document.createElement('iframe');
				iframe.className='pdfIframe'
				document.body.appendChild(iframe);
				iframe.style.display = 'none';
				iframe.onload = function () {
					setTimeout(function () {
						iframe.focus();
						URL.revokeObjectURL(url);
						document.body.removeChild(iframe);
						$('#btn-export').removeAttr('disabled').html('In báo giá');
					}, 1);
				};
				iframe.src = url;
			} else {
				$('#btn-export').removeAttr('disabled').html('In báo giá');
			}
		});
	});
</script>
@endpush
