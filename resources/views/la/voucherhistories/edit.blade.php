@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/voucherhistories') }}">Voucherhistory</a> :
@endsection
@section("contentheader_description", $voucherhistory->$view_col)
@section("section", "Voucherhistories")
@section("section_url", url(config('laraadmin.adminRoute') . '/voucherhistories'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Voucherhistories Edit : ".$voucherhistory->$view_col)

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
				{!! Form::model($voucherhistory, ['route' => [config('laraadmin.adminRoute') . '.voucherhistories.update', $voucherhistory->id ], 'method'=>'PUT', 'id' => 'voucherhistory-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'voucher_id')
					@la_input($module, 'customer_id')
					@la_input($module, 'used_at')
					@la_input($module, 'code')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Update', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/voucherhistories') }}">Cancel</a></button>
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
	$("#voucherhistory-edit-form").validate({
		
	});
});
</script>
@endpush
