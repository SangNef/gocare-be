@extends("la.layouts.app")

@section("contentheader_title", trans('cod_order.'.$type))
@section("contentheader_description", "Bill listing")
@section("section", trans('cod_order.'.$type))
@section("sub_section", "Listing")
@section("htmlheader_title", trans('cod_order.'.$type)." Listing")

@section("headerElems")
<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#{{ $type }}">Đối soát</button>
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
	'filterCreatedDate' => true,
	'useExtraFilter' => true,
	'totals' => [
		'total_fee' => 'Tổng tiền cước',
		'total_cod' => 'Tổng tiền COD',
		'insurance_value' => 'Tổng tiền hàng'
	],
	'filterColumns' => [0,1,2,3,4,5,6,7,8,9,10,11],
])
<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<table id="example1" class="table table-bordered">
			<thead>
			<tr class="success">
				@foreach( $listing_cols as $k => $col )
					<th>
						{{ ucfirst($col)}}
					</th>
				@endforeach
				@if($show_actions)
					<th>Actions</th>
				@endif
			</tr>
			</thead>
			<thead id="filter_bar"> 
			<tr class="success">
				@foreach( $listing_cols as $k => $col )
					<th colname="{!! $k !!}">{{ ucfirst($col)}}</th>
				@endforeach
			</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
</div>
@include('la.cod_orders.compare_modal', [
    'type' => $type
])
@endsection
@push('styles')
<style>
.stepwizard-step p {
    margin-top: 0px;
    color:#666;
}
.stepwizard-row {
    display: table-row;
}
.stepwizard {
    display: table;
    width: 100%;
    position: relative;
}
.stepwizard-step button[disabled] {
    /*opacity: 1 !important;
    filter: alpha(opacity=100) !important;*/
}
.stepwizard .btn.disabled, .stepwizard .btn[disabled], .stepwizard fieldset[disabled] .btn {
    opacity:1 !important;
    color:#bbb;
}
.stepwizard-row:before {
    top: 14px;
    bottom: 0;
    position: absolute;
    content:" ";
    width: 100%;
    height: 1px;
    background-color: #ccc;
    z-index: 0;
}
.stepwizard-step {
    display: table-cell;
    text-align: center;
    position: relative;
}
.stepwizard-step a {
	pointer-events: none;
}
.btn-circle {
    width: 30px;
    height: 30px;
    text-align: center;
    padding: 6px 0;
    font-size: 12px;
    line-height: 1.428571429;
    border-radius: 15px;
}
</style>
@endpush
@push('scripts')
<script>
	var url = "{{ url(config('laraadmin.adminRoute')) . '/cod-orders/'. $type . '/dt_ajax' }}";
    $(function() {
		$('#cod_bill_ids,#fee_bill_ids').change(function(e) {
			var bills = formatShippingBillIds(e.target.value);
			bills += ',';
			$(this).val(bills);
		});
		var navListItems = $('div.setup-panel div a'),
		allWells = $('.setup-content'),
		allNextBtn = $('.nextBtn, .submitBtn');
		allBackBtn = $('.backBtn');
		allWells.hide();

		navListItems.click(function (e) {
			e.preventDefault();
			var $target = $($(this).attr('href')),
				$item = $(this);

			if (!$item.hasClass('disabled')) {
				navListItems.removeClass('btn-success').addClass('btn-default');
				$item.addClass('btn-success');
				allWells.hide();
				$target.show();
			}
		});

		allBackBtn.click(function() {
			var curStep = $(this).closest(".setup-content"),
				curStepBtn = curStep.attr("id"),
				previousStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a");

			previousStepWizard.trigger('click');
		});

		allNextBtn.click(function () {
			var curStep = $(this).closest(".setup-content"),
				curStepBtn = curStep.attr("id"),
				nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
				nextStep = curStep.next(),
				curInputs = curStep.find("input[type='text'],input[type='url'],textarea,select"),
				isValid = true;

			$(".form-group").removeClass("has-error");

			if (curStep.hasClass('check-bills')) {
				var curStepBills = curStep.find('textarea.bills').val(),
					checkBillUrl = '{{ route('co.check-bills', ["type" => $type]) }}';
				if (curStepBills != '') {
					checkBillUrl += '?bills=' + curStepBills;
				}
				$.ajax({
					url: checkBillUrl,
					async: false,
					beforeSend: function() {
						$('#{{$type}} .error-message').empty().hide();
					},
					error: function(res) {
						isValid = false;
						var messages = res.responseJSON,
							listError = '<ul>';
						for (key in messages) {
							listError += '<li>'+ messages[key] +'</li>';
						}
						listError += '</ul>';
						$('#{{$type}} .error-message').append(listError).show();
					}
				});
			}
			if (nextStep.hasClass('get-price') && isValid) {
				var codBillIds = $('#cod_bill_ids').val(),
					feeBillIds = $('#fee_bill_ids').val(),
					discount = $('#discount').val(),
					url = '{{ route('co.get-money', ["type" => $type]) }}?cod_bills=' + codBillIds 
						+ '&fee_bills=' + feeBillIds;

					$.ajax({
						url: url,
						async: false,
						beforeSend: function() {
							$('#{{$type}} .error-message').empty().hide();
							$('#result .cod_amount,#result .real_amount,#result .total_amount').empty();
						},
						success: function (res) {
							for (key in res) {
								$('#result .' + key).append(res[key]);
							}
						}
					});
				
			}
			if (isValid) {
				nextStepWizard.removeAttr('disabled').trigger('click');
			} else {
				$(curInputs).closest(".form-group").addClass("has-error");
			}
		});

		$('div.setup-panel div a.btn-success').trigger('click');
	})
</script>
@endpush
