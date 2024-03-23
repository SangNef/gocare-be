@extends("la.layouts.app")
zz
@section("contentheader_title", trans('messages.customers'))
@section("section", trans('messages.customers'))
@section("sub_section", trans('messages.customers'))
@section("htmlheader_title", trans('messages.customers'))

@section("headerElems")
@la_access("Customers", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">{{ trans('messages.add-customer')  }}</button>
    <a href="{{ route('customer.export') }}" class="btn btn-warning btn-sm pull-right mr-1" id="export-customer">Export</a>
@endla_access
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

@php
    $type = request()->query('type');
    $path = '';

    if ($type == 'ctv') {
        $path = 'customer_dt_ajax/?type=ctv';
    } elseif ($type == 'khachhang') {
        $path = 'customer_dt_ajax/?type=khachhang';
    } elseif ($type == 'daily') {
        $path = 'customer_dt_ajax/?type=daily';
    } else {
        $path = 'customer_dt_ajax';
    }
@endphp

@include('la.datatable.index', ['id' => 'users', 'path' => $path, 'cols' => [

    [
        'title' => 'ID',
        'field' => 'id',
    ],[
        'title' => 'Mã đại lý',
        'field' => 'code',
    ],[
        'title' => 'Kho',
        'field' => 'store_id',
    ],
    [
        'title' => 'Tên',
        'field' => 'name',
    ],
    [
        'title' => 'Email',
        'field' => 'email',
    ],
    [
        'title' => 'Nhóm',
        'field' => 'group_id',
    ],
    [
        'title' => 'Số điện thoại',
        'field' => 'phone',
    ],
    [
        'title' => 'Địa chỉ',
        'field' => 'address',
    ],
    [
        'title' => 'Người quản lý',
        'field' => 'parent_id'
    ],
    [
        'title' => 'Nợ trước',
        'field' => 'debt_in_advance',
    ],
    [
        'title' => 'Tổng nợ',
        'field' => 'debt_total',
    ],
    [
        'title' => 'CCCD',
        'field' => 'cccd',
    ],
    [
        'title' => 'Ngân hàng',
        'field' => 'bank_name',
    ],
    [
        'title' => 'Số tài khoản',
        'field' => 'bank_acc',
    ],
    [
        'title' => 'Chủ tài khoản',
        'field' => 'bank_acc_name',
    ],
], 'extraFilter' => 'la.customers.extra-filter', 'totals' => [
    'import' => 'Nhập',
    'export' => 'Xuất',
    'has' => 'Có',
    'debt' => 'Nợ',
    'debt_total' => 'Tổng nợ'
]])

@la_access("Customers", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">{{ trans('messages.add-customer')  }}</h4>
			</div>
			{!! Form::open(['action' => 'LA\CustomersController@store', 'id' => 'customer-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
                    @la_input($module, 'name')
                    <div class="form-group">
                        <label for="customer_currency" style="margin-right: 20px">Loại tiền tệ :</label>
                        <label style="margin-right: 10px"><input type="radio" value="1" name="customer_currency" checked>VNĐ</label>
                        <label><input type="radio" value="2" name="customer_currency">NDT</label>
                    </div>
					@la_input($module, 'email')
                    @la_input($module, 'phone')
                    @la_input($module, 'address')
					<div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="province">Tỉnh/Thành phố :</label>
                                <select class="form-control select2-hidden-accessible" data-placeholder="Enter Tỉnh/Thành phố" rel="select2" name="province" id="province" tabindex="-1" aria-hidden="true">
                                    <option value="">Chọn Tỉnh/Thành phố</option>
                                    @foreach($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district">Quận/Huyện :</label>
                                <select class="form-control select2-hidden-accessible" data-placeholder="Enter Quận/Huyện" rel="select2" name="district" id="district" tabindex="-1" aria-hidden="true">
                                    <option class="selected" value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="province">Xã/Phường :</label>
                                <select class="form-control select2-hidden-accessible" data-placeholder="Enter Xã/Phường" rel="select2" name="ward" id="ward" tabindex="-1" aria-hidden="true">
                                    <option class="selected" value="">Chọn Xã/Phường</option>
                                </select>
                            </div>
                        </div>
                    </div>
					@la_input($module, 'parent_id')
                    @la_input($module, 'username')
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
                        <label for="debt_in_advance">Nợ trước*:</label>
                        <input type="text" value="0" name="debt_in_advance" id="debt_in_advance" class="form-control currency valid" aria-invalid="false"/>
                    </div>
					@la_input($module, 'note')
                    @if (!auth()->user()->store_id)
                        <div class="form-group">
                            <label for="store_id" style="margin-right: 20px">Kho :</label>
                            <select name="store_id" class="form-control ajax-select" id="store_id" model="stores" required>

                            </select>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="status" style="margin-right: 20px">Nhóm :</label>
                        <select name="group_id" id="group_id" class="form-control ajax-select" model="group" lookup="store_id" required>

                        </select>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel')  }}</button>
				{!! Form::submit( trans('messages.submit'), ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endla_access

@endsection

@push('styles')

@endpush

@push('scripts')
<script>
var url = "{{ url(config('laraadmin.adminRoute') . '/transaction_dt_ajax') }}";
$(function () {
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
    $('#store_id').change(function () {
        $('#group_id').val('').change();
    })
});
</script>
@endpush
