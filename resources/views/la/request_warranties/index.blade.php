@extends("la.layouts.app")

@section("contentheader_title", "RequestWarranties")
@section("contentheader_description", "RequestWarranties listing")
@section("section", "RequestWarranties")
@section("sub_section", "Listing")
@section("htmlheader_title", "RequestWarranties Listing")

@section("headerElems")
@la_access("RequestWarranties", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm yêu cầu bảo hành</button>
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
@include('la.partials.created_filter', [
	'useExtraFilter' => true,
	'filterCreatedDate' => true,
	'filterDate' => true,
	'restoreState' => true,
	'totals' => [],
	'filterColumns' => [],
	'filterOptions' => [
		'status' => $statusList,
		'group_id' => $groups,
		'from' => [
			1 => 'Admin',
			2 => 'Trang chủ'
		],
	],
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
				<th colname="{!! isset($module->fields[$col]) ? $module->fields[$col]['colname'] : $col !!}">{{ $module->fields[$col]['label'] or ucfirst($col) }}</th>
			@endforeach
		</tr>
		</thead>
		<tbody>
			
		</tbody>
		</table>
	</div>
</div>

@la_access("RequestWarranties", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm yêu cầu bảo hành</h4>
			</div>
			{!! Form::open(['action' => 'LA\RequestWarrantiesController@store', 'id' => 'request-warranties-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="status" style="margin-right: 20px">Khách hàng :</label>
						<select class="form-control ajax-select" model="customer" id="customer_id">
						</select>
					</div>
					<div class="form-group">
						<label for="name">Họ tên:</label>
						<input name="name" class="form-control" id="name" placeholder="Enter Họ tên">
					</div>
					<div class="form-group">
						<label for="phone">SDT:</label>
						<input name="phone" class="form-control" id="phone" placeholder="Enter SĐT">
					</div>
					<div class="form-group">
						<label for="address">Địa chỉ:</label>
						<input name="address" class="form-control" id="address" placeholder="Enter Địa chỉ">
					</div>
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
					<div class="form-group">
						<label for="product">Tên sản phẩm :</label>
						<select class="form-control ajax-select" model="product" data-placeholder="Chọn sản phẩm" id="product">
						</select>
						<input type="hidden" name="product_name" id="product_name" value="">
					</div>
					<div class="form-group">
						<label for="seri_number">Seri :</label>
						<select class="form-control ajax-select" model="seri" data-placeholder="Chọn seri" extra_param="0" name="seri_number" id="seri_number">
						</select>
					</div>
                    @la_input($module, 'content')
					<div class="form-group">
						<label for="status">Trạng thái :</label>
						<select class='form-control' name="status" id="status">
							@foreach(\App\Models\RequestWarranty::getListStatus() as $key => $value)
								<option value="{{ $key }}">{{ $value }}</option>
							@endforeach
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
@endla_access

@endsection

@push('scripts')
<script>
var url = "{{ url(config('laraadmin.adminRoute') . '/request_warranties_dt_ajax') }}";
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

	$('#product').change(function() {
		const product = $(this).select2('data')[0];
		let productName = "";
		let productId = 0;
		if (typeof product !== 'undefined') {
			productName = product.text;
			productId = product.id;
		}
		$('#product_name').val(productName);
		$('#seri_number').attr('extra_param', productId).val(null).change();
	});

	$('#customer_id').change(function() {
		let value = $(this).val();

		$.ajax({
			url: "{{ url(config('laraadmin.adminRoute') . '/customers/search-username') }}",
			data: {
				customer_id: value
			},
			success: function (data) {
				const select2Input = ['province', 'district', 'ward'];
				for (const key in data) {
					if (select2Input.includes(key)) {
						if (data[key]) {
							const newOption = new Option(data[key].name, data[key].id, true, true);
							$(`#${key}`).append(newOption).trigger('change');
						} else {
							$(`#${key}`).val(null).trigger('change');
						}
						continue;
					}
					$(`#${key}`).val(data[key]);
				}
			}
		});

	});
});
</script>
@endpush
