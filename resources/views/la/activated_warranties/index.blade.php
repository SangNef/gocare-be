@extends("la.layouts.app")

@section("contentheader_title", "Activated Warranties")
@section("contentheader_description", "Activated Warranties")
@section("section", "Activated Warranties")
@section("htmlheader_title", "Activated Warranties")

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
	'filterColumns' => [0,1,2,3,4,5,6],
	'extraForm' => 'la.activated_warranties.extra_filter_form'
])

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<table id="example1" class="table table-bordered">
		<thead>
		<tr class="success">
			@foreach( $listing_cols as $col => $label )
			<th>{{ $label }}</th>
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
@endsection
@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
var url = "{{ url(config('laraadmin.adminRoute') . '/activated_warranties_dt_ajax') }}";
$(function () {
});
</script>
@endpush
