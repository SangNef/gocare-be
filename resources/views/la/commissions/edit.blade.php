@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/commissions') }}">Commission</a> :
@endsection
@section("contentheader_description", $commission->$view_col)
@section("section", "Commissions")
@section("section_url", url(config('laraadmin.adminRoute') . '/commissions'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Commissions Edit : ".$commission->$view_col)

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
				{!! Form::model($commission, ['route' => [config('laraadmin.adminRoute') . '.commissions.update', $commission->id ], 'method'=>'PUT', 'id' => 'commission-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'customer_id')
					@la_input($module, 'amount')
					@la_input($module, 'order_id')
					@la_input($module, 'trans_id')
					@la_input($module, 'note')
					@la_input($module, 'balance')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/commissions') }}">Huỷ</a></button>
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
	$("#commission-edit-form").validate({
		
	});
});
</script>
@endpush
