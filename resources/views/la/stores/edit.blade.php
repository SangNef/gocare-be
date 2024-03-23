@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/stores') }}">Store</a> :
@endsection
@section("contentheader_description", $store->$view_col)
@section("section", "Stores")
@section("section_url", url(config('laraadmin.adminRoute') . '/stores'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Stores Edit : ".$store->$view_col)

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
				{!! Form::model($store, ['route' => [config('laraadmin.adminRoute') . '.stores.update', $store->id ], 'method'=>'PUT', 'id' => 'store-edit-form']) !!}
{{--					@la_form($module)--}}
					

					@la_input($module, 'name')
					@la_input($module, 'address')
					@la_input($module, 'started_at')
					@la_input($module, 'status')
					@la_input($module, 'website_url')
					@la_input($module, 'owner_id')

                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/stores') }}">Huỷ</a></button>
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
	$("#store-edit-form").validate({
		
	});
});
</script>
@endpush
