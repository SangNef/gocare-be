@extends("la.layouts.app")

@section("contentheader_title", "Imports")
@section("contentheader_description", "Imports listing")
@section("section", "Imports")
@section("sub_section", "Listing")
@section("htmlheader_title", "Imports Listing")

@section("headerElems")
@la_access("Imports", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Add Import</button>
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
	'useExtraFilter' => false,
	'filterCreatedDate' => false,
	'filterDate' => false,
	'totals' => [],
	'filterColumns' => [],
	'filterOptions' => [
		'status' => [
			'Đang xử lý' => 'Đang xử lý',
			'Hoàn thành' => 'Hoàn thành'
		],
	],
	'extraForm' => 'la.products.extra_filter_form',
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
					<th @if($col == 'id') style="width: 8%" @endif colname="{!! isset($module->fields[$col]) ? $module->fields[$col]['colname'] : $col !!}">
						{{ $module->fields[$col]['label'] or ucfirst($col) }}
					</th>
				@endforeach
			</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
</div>

@la_access("Imports", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Add Import</h4>
			</div>
			{!! Form::open(['action' => 'LA\ImportsController@store', 'id' => 'import-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
					<div class="row">
						<div class="col-sm-12">
							@la_input($module, 'code')
							@la_input($module, 'store_id')
							@la_input($module, 'customer_id')
						</div>
						<div class="col-sm-12">
							<label><strong>Sản phẩm</strong></label>
						</div>
						<div class="col-sm-12" style="margin-bottom: 10px">
							<div class="col-sm-9">Sản phẩm*</div>
							<div class="col-sm-2">Số lượng*</div>
							<div class="col-sm-1"><button type="button" class="btn btn-success btn-sm ml-3 p-product-add">Thêm</button></div>
						</div>
						<div class="p-product-items">
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

@endsection

@include('la.produces.scripts', [
    'includeNote' => 1
])
{{--@push('styles')--}}
{{--<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>--}}
{{--@endpush--}}

@push('scripts')

<script>
	var url = "{{ url(config('laraadmin.adminRoute')) . '/import_dt_ajax' }}";
$(function () {
	$(document).on('click', '.print-import', function (event) {
		event.preventDefault();
		var el = $(this);
		var iframe = document.createElement('iframe');
		iframe.className='pdfIframe'
		document.body.appendChild(iframe);
		iframe.style.display = 'none';
		iframe.onload = function () {
			setTimeout(function () {
				iframe.focus();
				URL.revokeObjectURL(url)
				document.body.removeChild(iframe)
				el.removeAttr('disabled');
				el.html('IN');
			}, 1);
		};
		iframe.src = $(this).attr('href');
	})
	$("#import-add-form").validate({
		
	});
});
</script>
@endpush
