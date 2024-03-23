@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/storeobserves') }}">StoreObserf</a> :
@endsection
@section("contentheader_description", $storeobserf->$view_col)
@section("section", "StoreObserves")
@section("section_url", url(config('laraadmin.adminRoute') . '/storeobserves'))
@section("sub_section", "Edit")

@section("htmlheader_title", "StoreObserves Edit : ".$storeobserf->$view_col)

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
		
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				{!! Form::model($storeobserf, ['route' => [config('laraadmin.adminRoute') . '.storeobserves.update', $storeobserf->id ], 'method'=>'PUT', 'id' => 'storeobserf-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'store_id')
					@la_input($module, 'customer_id')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/storeobserves') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
	$("#storeobserf-edit-form").validate({
		
	});
});
</script>
@endpush
