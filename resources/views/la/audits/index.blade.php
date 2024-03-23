@extends("la.layouts.app")

@section("contentheader_title", "Audits")
@section("contentheader_description", "Audits listing")
@section("section", "Audits")
@section("sub_section", "Listing")
@section("htmlheader_title", "Audits Listing")

@section("headerElems")

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
	'totals' => ['total_amount' => 'Tổng tiền giao dịch'],
	'filterColumns' => [],
	'filterOptions' => [
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
@endsection
@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script>
	var url = "{{ url(config('laraadmin.adminRoute')) . '/audit_dt_ajax' }}";
	if ("{{request('from')}}") {
		url += "?from={{request('from')}}";
	}
$(function () {
});
</script>
@endpush
