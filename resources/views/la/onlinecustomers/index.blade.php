@extends("la.layouts.app")

@section("contentheader_title", "OnlineCustomers")
@section("contentheader_description", "OnlineCustomers listing")
@section("section", "OnlineCustomers")
@section("sub_section", "Listing")
@section("htmlheader_title", "OnlineCustomers Listing")

@section("headerElems")
@la_access("OnlineCustomers", "create")
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
	'restoreState' => false,
	'totals' => [],
	'filterColumns' => [],
	'filterOptions' => [],
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
						@if ($col == 'number_of_products')
						SL
						@else
						{{ $module->fields[$col]['label'] or ucfirst($col) }}
						@endif
					</th>
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
@endsection

@push('styles')
@endpush

@push('scripts')
<script>
var url = "{{ url(config('laraadmin.adminRoute')) . '/onlinecustomer_dt_ajax?' }}";
</script>
@endpush
