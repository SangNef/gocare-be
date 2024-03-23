@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/audits') }}">Audit</a> :
@endsection
@section("contentheader_description", $audit->$view_col)
@section("section", "Audits")
@section("section_url", url(config('laraadmin.adminRoute') . '/audits'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Audits Edit : ".$audit->$view_col)

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
				{!! Form::model($audit, ['route' => [config('laraadmin.adminRoute') . '.audits.update', $audit->id ], 'method'=>'PUT', 'id' => 'audit-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'customer_id')
					@la_input($module, 'order_id')
					@la_input($module, 'amount')
					@la_input($module, 'balance')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/audits') }}">Huỷ</a></button>
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
	$("#audit-edit-form").validate({
		
	});
});
</script>
@endpush
