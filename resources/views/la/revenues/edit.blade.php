@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/revenues') }}">Revenue</a> :
@endsection
@section("contentheader_description", $revenue->$view_col)
@section("section", "Revenues")
@section("section_url", url(config('laraadmin.adminRoute') . '/revenues'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Revenues Edit : ".$revenue->$view_col)

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
				{!! Form::model($revenue, ['route' => [config('laraadmin.adminRoute') . '.revenues.update', $revenue->id ], 'method'=>'PUT', 'id' => 'revenue-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'store_id')
					@la_input($module, 'total')
					@la_input($module, 'product_amount')
					@la_input($module, 'bank_amount')
					@la_input($module, 'customer_amount')
					@la_input($module, 'reported_at')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/revenues') }}">Huỷ</a></button>
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
	$("#revenue-edit-form").validate({
		
	});
});
</script>
@endpush
