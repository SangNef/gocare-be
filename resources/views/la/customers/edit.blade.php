@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/customers') }}">{{ trans('messages.customers') }}</a> :
@endsection
@section("contentheader_description", $customer->$view_col)
@section("section", trans('messages.customers'))
@section("section_url", url(config('laraadmin.adminRoute') . '/customers'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Customers Edit : ".$customer->$view_col)

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
		<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
			<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i>{{ trans('messages.general_info') }}</a></li>
{{--			<li><a role="tab" data-toggle="tab" href="#tab-transport" data-target="#tab-transport"><i class="fa fa-truck"></i>{{ trans('order.transport') }}</a></li>--}}
			<li><a role="tab" data-toggle="tab" href="#tab-sub-customer" data-target="#tab-sub-customer"><i class="fa fa-truck"></i>Thống kê doanh số</a></li>
		</ul>
	</div>
	<div class="box-body">
		{!! Form::model($customer, ['route' => [config('laraadmin.adminRoute') . '.customers.update', $customer->id ], 'method'=>'PUT', 'id' => 'customer-edit-form']) !!}
			<div class="row">
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
						<div class="col-md-8 col-md-offset-2">
							@la_input($module, 'name')
							<div class="form-group" style="display: none">
								<label for="customer_currency" style="margin-right: 20px">Loại tiền tệ :</label>
								<label style="margin-right: 10px"><input type="radio" value="1" name="customer_currency" @if($customer->customer_currency == 1) checked @endif>VNĐ</label>
								<label><input type="radio" value="2" name="customer_currency" @if($customer->customer_currency == 2) checked @endif>NDT</label>
							</div>
							@la_input($module, 'code')
							@la_input($module, 'email')
							@la_input($module, 'phone')
							@la_input($module, 'address')
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label for="province">Tỉnh/Thành phố :</label>
										<select class="form-control select2-hidden-accessible" data-placeholder="Enter Tỉnh/Thành phố" rel="select2" name="province" id="province" tabindex="-1" aria-hidden="true">
											@if($currentProvince)
											<option selected value="{{ $currentProvince->id }}">{{ $currentProvince->name }}</option>
											@else
											<option class="selected" value="">Chọn Tỉnh/Thành phố</option>
											@endif
											@foreach($provinces as $id => $name)
											<option value="{{ $id }}">{{ $name }}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="district">Quận/Huyện :</label>
										<select class="form-control select2-hidden-accessible" data-placeholder="Enter Quận/Huyện" rel="select2" name="district" id="district" tabindex="-1" aria-hidden="true">
											@if($currentDistrict)
											<option selected value="{{ $currentDistrict->id }}">{{ $currentDistrict->name }}</option>
											@else
											<option class="selected" value="">Chọn Quận/Huyện</option>
											@endif
											@foreach($districts as $id => $name)
											<option value="{{ $id }}">{{ $name }}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="province">Xã/Phường :</label>
										<select class="form-control select2-hidden-accessible" data-placeholder="Enter Xã/Phường" rel="select2" name="ward" id="ward" tabindex="-1" aria-hidden="true">
											@if($currentWard)
											<option selected value="{{ $currentWard->id }}">{{ $currentWard->name }}</option>
											@else
											<option class="selected" value="">Chọn Xã/Phường</option>
											@endif
											@foreach($wards as $id => $name)
											<option value="{{ $id }}">{{ $name }}</option>
											@endforeach                                    
										</select>
									</div>
								</div>
							</div>
							@la_input($module, 'parent_id')
							<div class="form-group">
								<label for="name">Name*:</label>
								<input type="text" name="name" id="name" class="form-control" value="{{ $customer->name ?? old('name') }}">
							</div>
							<div class="row">
								<div class="form-group col-md-6">
									<label for="password">Mật khẩu*:</label>
									<input type="password" name="password" id="password" class="form-control"/>
								</div>
								<div class="form-group col-md-6">
									<label for="password_confirmation">Xác nhận mật khẩu*:</label>
									<input type="password" name="password_confirmation" id="password_confirmation" class="form-control"/>
								</div>
							</div>
							<div class="form-group">
								<label for="cccd">CCCD*:</label>
								<input type="number" name="cccd" id="cccd" class="form-control" value="{{ $customer->cccd ?? old('cccd') }}">
							</div>
							<div class="form-group">
								<label for="bank_name">Ngân hàng*:</label>
								<input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ $customer->bank_name ?? old('bank_name') }}">
							</div>
							<div class="form-group">
								<label for="bank_acc">Số tài khoản*:</label>
								<input type="number" name="bank_acc" id="bank_acc" class="form-control" value="{{ $customer->bank_acc ?? old('bank_acc') }}">
							</div>
							<div class="form-group">
								<label for="bank_acc_name">Chủ tài khoản*:</label>
								<input type="text" name="bank_acc_name" id="bank_acc_name" class="form-control" value="{{ $customer->bank_acc_name ?? old('bank_acc_name') }}">
							</div>
							@if(auth()->user()->isChairmanUser())
							<div class="form-group">
								<label for="debt_in_advance">Nợ trước*:</label>
								<input type="text" value="{{ $customer->debt_in_advance }}" name="debt_in_advance" id="debt_in_advance" class="form-control currency valid" aria-invalid="false"/>
							</div>
							<div class="form-group">
								<label for="debt_total">Tổng nợ*:</label>
								<input type="text" value="{{ $customer->debt_total }}" name="debt_total" id="debt_total" class="form-control currency valid" aria-invalid="false"/>
							</div>
							@endif
							@la_input($module, 'note')
							@la_input($module, 'group_id')
							@if(!$customer->accesstoken()->exists())
							<div class="form-group" style="margin-bottom: 0; display: none">
								<label style="font-size: 15px" for="create_accesstoken">
									<input type="checkbox" id="create_accesstoken" name="create_accesstoken" style="margin-right: 5px;">
									Tạo Access token
								</label>
							</div>
							@else
							<div class="form-group" style="display: none">
								<label for="vtp_id">Access token :</label>
								<input class="form-control" name="accesstoken_id" type="text" value="{{ $customer->accesstoken->api_key }}" disabled>
							</div>
							@endif
							<div class="form-group">
								<label for="vtp_id">
									<input name="can_create_sub" type="checkbox" style="margin-right: 5px;" value="1" @if ($customer->can_create_sub == 1) checked="checked" @endif>
									Có quyền tạo tài khoản con <small class="text-warning">(Chỉ áp dụng cho nhóm đại lý)</small>
								</label>
								
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane" id="tab-transport">
						<div class="col-md-8 col-md-offset-2">
							@foreach ($providers as $provider)
							<div>
								<div class="form-group" style="margin-bottom: 0;">
									<label style="font-size: 15px" for="setup_{{ $provider['key'] }}_account">
										<input type="checkbox" id="setup_{{ $provider['key'] }}_account" name="shipping_setups[{{ $provider['key'] }}][is_active]" style="margin-right: 5px;" @if($provider['is_active']) checked @endif>
										Thiết lập tài khoản {{ $provider['name'] }}
									</label>
								</div>
								<div class="row setup_{{ $provider['key'] }}_account_form" @if(!$provider['is_active']) style="display: none" @endif>
									@foreach ($provider['connection'] as $key => $value)
									<div class="col-sm-12">
										<div class="form-group">
											<label>{{ ucfirst($key) }} :</label>
											<input type="text" class="form-control" name="shipping_setups[{{ $provider['key'] }}][connection][{{ $key }}]" value="{{ $value }}">
										</div>
									</div>
									@endforeach
									@if ($provider['is_active'] && count($provider['stores']) > 0)
									<div class="col-sm-12">
										<div class="form-group">
											<label>Kho hàng :</label>
											<select name="shipping_setups[{{ $provider['key'] }}][inventory]" class="form-control">
												<option selected>Chọn kho hàng</option>
												@foreach($provider['stores'] as $id => $store)
												<option @if($provider['inventory'] == $id) selected @endif value="{{ $id }}">{{ $store }}</option>
												@endforeach
											</select>
										</div>
									</div>
									@endif
								</div>
							</div>
							<hr/>
							@endforeach
						</div>
					</div>
					<div role="tabpanel" class="tab-pane" id="tab-sub-customer">
						<div class="col-md-12">
							<h3>Thống kê doanh số tài khoản và tài khoản cấp dưới</h3>
							<div class="row">
								<div class="col-md-6">
									<form method="get" id="admin-filter-form">
										<div class="form-group">
											<label>
												Thời gian:
											</label>
											<div class="row">
												<div class="col-sm-4">
													<input type="text" id ="sub-customer-form-from" name="from" value="{{ \Carbon\Carbon::now()->subDays(7)->format('Y/m/d') }}" placeholder="Từ ngày" class="form-control input-sm datepicker"/>
												</div>
												<div class="col-sm-4">
													<input type="text"  id ="sub-customer-form-to" name="to" value="{{ \Carbon\Carbon::now()->addDay()->format('Y/m/d') }}" placeholder="Đến ngày" class="form-control input-sm datepicker"/>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="row" id='sub-customers-result'>
								
							</div>
						</div>
					</div>

				</div>

				<div class="col-md-8 col-md-offset-2">
					<br/>
					<div class="form-group">
						{!! Form::submit( trans('messages.update'), ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/customers') }}">{{ trans('messages.cancel') }}</a></button>
					</div>
				</div>
			</div>
		{!! Form::close() !!}
	</div>
</div>

@endsection

@push('scripts')
<script>

var scLoading = false;
function loadSubCustomers(page)
	{
		if (scLoading) {
			scLoading.abort();
		}
		var scLoading =  $.ajax({
			url: '{{ route("customers.sub", ["id" => $customer->id]) }}',
			data: {
				from: $('#sub-customer-form-from').val(),
				to: $('#sub-customer-form-to').val(),
				page: page ? page : 1
			},
			success: function (html) {
				scLoading = false;
				$('#sub-customers-result').html(html);
			}
		})
	}
$(function () {
	const availableProviders = {!! json_encode($providers) !!};

	$("#customer-edit-form").validate({
		
	});

	$('#province').change(function() {
        let id = $(this).find('option:selected').val();
        $('#district,#ward').find('option').not('.selected').remove();
        getAddress(id, 'province', '#district', '{{ route('customer.get-address') }}');
    });

    $('#district').change(function() {
        let id = $(this).find('option:selected').val();
        $('#ward').find('option').not('.selected').remove();
        getAddress(id, 'district', '#ward', '{{ route('customer.get-address') }}');
    });

	Object.keys(availableProviders).map(function (key) {
		const provider = availableProviders[key];
		$(`#setup_${key}_account`).change(function() {
			$(`.setup_${key}_account_form`).toggle();
		});
	});
	loadSubCustomers();
	$('#sub-customer-form-from, #sub-customer-form-to').change(() => loadSubCustomers());
});
</script>
@endpush
