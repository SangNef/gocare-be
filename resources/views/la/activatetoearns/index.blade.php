@extends("la.layouts.app")

@section("contentheader_title", "ActivateToEarns")
@section("contentheader_description", "ActivateToEarns listing")
@section("section", "ActivateToEarns")
@section("sub_section", "Listing")
@section("htmlheader_title", "ActivateToEarns Listing")

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
	'filterCreatedDate' => false,
	'filterDate' => false,
	'totals' => [
		'total_successful' => 'Tổng thành công'
	],
	'filterColumns' => [],
	'filterOptions' => [
		'status' => [
			2 => 'Thành công',
			3 => 'Thất bại'
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
@endsection
@push('scripts')
<script>
var url = "{{ url(config('laraadmin.adminRoute')) . '/activatetoearn_dt_ajax' }}";
$(function () {
	$("#activatetoearn-add-form").validate({
		
	});
});
</script>
@endpush
