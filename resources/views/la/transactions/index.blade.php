@extends("la.layouts.app")

@section("contentheader_title", "Giao dịch")
@section("contentheader_description", "Danh sách giao dịch")
@section("section", "Giao dịch")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Danh sách giao dịch")

@section("headerElems")
@la_access("Transactions", "create")
	@if(auth()->user()->isSupperAdminRole())
	<button id="approve-all" class="btn btn-warning btn-sm mr5">Duyệt tất cả</button>
	@endif
	<button class="btn btn-warning btn-sm mr5" data-toggle="modal" data-target="#moneyTransfer">Chuyển tiền</button>
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm giao dịch</button>
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
	'totals' => [
		'total_receive' => 'Tổng tiền nhận',
		'total_transfer' => 'Tổng tiền chuyển',
		'total_amount' => 'Tổng tiền giao dịch'
	],
	'filterColumns' => [],
	'filterOptions' => [
		'status' => [
			1 => 'Mới',
			2 => 'Duyệt',
		],
		'type' => [
			1 => 'Nhận',
			2 => 'Chuyển',
		],
	],
	'extraForm' => 'la.transactions.extra_filter_form',
])

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<table id="example1" class="table table-bordered">
		<thead>
			<tr class="success">
				@foreach( $listing_cols as $k => $col )
					<th>
						@if ($k === 0)		
						<input type="checkbox" id="check-all" />
						@endif
						{{ $module->fields[$col]['label'] or ucfirst($col) }}
					</th>
				@endforeach
				@if($show_actions)
					<th>&nbsp;</th>
				@endif
			</tr>
		</thead>
		<thead id="filter_bar">
			<tr class="success">
				@foreach( $listing_cols as $col )
					<th colname="{!! isset($module->fields[$col]) ? $module->fields[$col]['colname'] : $col !!}">
						{{ $module->fields[$col]['label'] or ucfirst($col) }}
					</th>
				@endforeach
			</tr>
		</thead>
		<thead>
		<tbody>
			
		</tbody>
		</table>
	</div>
</div>

@la_access("Transactions", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm giao dịch</h4>
			</div>
			{!! Form::open(['action' => 'LA\TransactionsController@store', 'id' => 'transaction-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="trans_time">Thời gian:</label>
								<input class="form-control datetime-picker" type="text" id="trans_time" name="trans_time" value=""/>
							</div>
							<div class="form-group">
								<label for="user_id">Người giao dịch*:</label>
								<input class="form-control" type="text" id="user_id" value="{{ auth()->user()->name }}" disabled="disabled"/>
								<input type="hidden" name="user_id" value="{{ auth()->user()->id }}"/>
							</div>
							<div class="form-group">
								<label for="transfer-bank">Ngân hàng :</label>
								<select class="form-control select2-hidden-accessible" required="1" data-placeholder="Enter ngân hàng" rel="select2" name="bank_id" id="bank_id" tabindex="-1" aria-hidden="true" aria-required="true">
										<option value="" selected disabled="true">Chọn ngân hàng</option>
										@foreach($banks as $bank)
										<option value="{{ $bank->id }}" currency-type="{{ $bank->currency_type }}">{{ $bank->name }}</option>
										@endforeach
								</select>
							</div>
							<div class="form-group">
								<label for="customer_id">Tên khách hàng :</label>
								<select name="customer_id" extra_param="1" class="form-control ajax-select" model="customer" id="customer_id">
								</select>
							</div>
							<div class="form-group">
								<label for="fee">Phí:</label>
								<input type="text" value="0" name="fee" id="fee" class="form-control currency valid" aria-invalid="false"/>
							</div>
							@la_input($module, 'note')
						</div>
						<div class="col-md-6">
							@la_input($module, 'trans_id')
							<div class="form-group">
								<label for="order_id">Mã đơn hàng :</label>
								<select class="form-control ajax-select" id="order_id" model="customer_orders" extra_param="0" name="order_id">			
								</select>
							</div>
							<div class="form-group">
								<label for="type" class="mr10">Loại :</label>
								<div style="display: inline-block">
									<label class="radio-inline">
										<input type="radio" name="type" value="1" checked>Nhận
									</label>
									<label class="radio-inline">
										<input type="radio" name="type" value="2">Chuyển
									</label>
								</div>
							</div>
							<div class="form-group">
								<label for="amount">Số tiền*:</label>
								<input type="text" value="0" name="amount" id="amount" class="form-control currency valid" aria-invalid="false"/>
							</div>
							@la_input($module, 'desc')
						</div>
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

<div class="modal fade" id="moneyTransfer" role="dialog" aria-labelledby="moneyTransfer">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Chuyển tiền</h4>
			</div>
			{!! Form::open(['action' => 'LA\TransactionsController@moneyTransfer', 'id' => 'transaction-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="transfer-bank">Ngân hàng gửi :</label>
								<select class="form-control select2-hidden-accessible" required="1" data-placeholder="Enter ngân hàng gửi" rel="select2" name="transfer_bank" id="transfer-bank" tabindex="-1" aria-hidden="true" aria-required="true">
									<option value="" selected disabled="true">Enter ngân hàng gửi</option>
									@foreach($banks as $bank)
									<option value="{{ $bank->id }}" currency-type="{{ $bank->currency_type }}">{{ $bank->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label for="receive-bank">Ngân hàng nhận :</label>
								<select class="form-control select2-hidden-accessible" required="1" data-placeholder="Enter ngân hàng nhận" rel="select2" name="receive_bank" id="receive-bank" tabindex="-1" aria-hidden="true" aria-required="true">
									<option value="" selected disabled="true">Enter ngân hàng nhận</option>
									@foreach($banks as $bank)
									<option value="{{ $bank->id }}" currency-type="{{ $bank->currency_type }}">{{ $bank->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label for="transfer_fee">Phí gửi:</label>
								<input type="text" value="0" name="transfer_fee" id="transfer_fee" class="form-control currency valid" aria-invalid="false"/>
							</div>
							<div class="form-group">
								<label for="receive_fee">Phí nhận:</label>
								<input type="text" value="0" name="receive_fee" id="receive_fee" class="form-control currency valid" aria-invalid="false"/>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="trans_time">Thời gian:</label>
								<input class="form-control datetime-picker" type="text" id="trans_time" name="trans_time" value=""/>
							</div>
							<div class="form-group amount">
								<label for="amount">Số tiền*:</label>
								<input type="text" value="0" name="amount" id="amount" class="form-control currency valid" aria-invalid="false"/>
							</div>
							<div class="form-group amount_ndt" style="display: none">
								<label for="amount">Số tiền NDT:</label>
								<input type="text" value="0" name="amount_ndt" id="amount_ndt" class="form-control currency valid" aria-invalid="false"/>
							</div>
							@la_input($module, 'desc')
						</div>
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
@endsection

@push('scripts')
<script>
	var url = "{{ url(config('laraadmin.adminRoute') . '/transaction_dt_ajax') }}";

	$(function() {
		$('#check-all').change(function (event) {
			if ($(this).prop('checked')) {
				$('input.row').prop('checked', true);
			} else {
				$('input.row').prop('checked', false);
			}
		});

		$('#approve-all').click(function() {
			var selected = [];
			var approveUrl = "{{ url(config('laraadmin.adminRoute') . '/approve-transaction') }}";
			$('input.row').each(function () {
				if ($(this).prop('checked')) {
					selected.push($(this).val());
				}
			})
			
			if (selected.length > 0) {
				approveUrl += '?ids=' + selected.join(',');
				location.href = approveUrl;
			} else {
				alert('Chọn giao dịch');
			}
		});

		$('#example1 tbody').on('click', '.trans-approve', function() {
			return confirm('Đồng ý duyệt giao dịch?');
		})
		
		$('#customer_id').change(function() {
			let customerId = $(this).val();
			$('#order_id').val(null).change().attr('extra_param', customerId);
			initAjaxSelect();
		})

		$('#transfer-bank, #receive-bank').change(function() {
			var transferBank = $('#transfer-bank option:selected');
			var receiveBank = $('#receive-bank option:selected');
			if (transferBank.attr('currency-type') == 2 && receiveBank.attr('currency-type') == 2) {
				$('.amount_ndt').show();
				$('.amount').hide();
			} else if ((transferBank.attr('currency-type') == 1 && receiveBank.attr('currency-type') == 2)
				|| (transferBank.attr('currency-type') == 2 && receiveBank.attr('currency-type') == 1)) {
				$('.amount_ndt').show();
				$('.amount').show();
			} else {
				$('.amount_ndt').hide();
				$('.amount').show();
			}
		})

		$('#bank_id').change(function() {
			let selectedBank = $(this).find('option:selected');
			$('#customer_id').attr('extra_param', selectedBank.attr('currency-type'));
			$('#customer_id').val(null).change();
		});
	})
</script>
@endpush
