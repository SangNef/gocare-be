@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/activated-warranties') }}">Activated Warranties</a> :
@endsection
@section("contentheader_description", $seri->seri_number)
@section("section", "ActivatedWarranties")
@section("section_url", url(config('laraadmin.adminRoute') . '/activated-warranties'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Activated Warranties Edit : ".$seri->seri_number)

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
				{!! Form::model($seri, ['route' => [config('laraadmin.adminRoute') . '.activated-warranties.update', $seri->id ], 'method'=>'PUT', 'id' => 'activated-warranties-edit-form']) !!}
                    <div class="form-group">
						{!! Form::label('activated_at', 'Ngày kích hoạt') !!}
						{{ Form::text('activated_at', $seri->activated_at, ['class' => 'form-control datetime-picker', 'readonly' => !$isAdmin]) }}
					</div>
					<div class="form-group">
						{!! Form::label('seri_number', 'Seri') !!}
						{{ Form::text('seri_number', $seri->seri_number, ['class' => 'form-control', 'readonly' => true]) }}
					</div>
                    <div class="form-group">
						{!! Form::label('product_id', 'Sản phẩm') !!}
						{{ Form::text('product_id', $seri->product->name, ['class' => 'form-control', 'readonly' => true]) }}
					</div>
                    <div class="form-group">
						{!! Form::label('name', 'Họ tên') !!}
						{{ Form::text('name', $seri->product->name, ['class' => 'form-control', 'readonly' => true]) }}
					</div>
                    <div class="form-group">
						{!! Form::label('phone', 'SĐT') !!}
						{{ Form::text('phone', $seri->phone, ['class' => 'form-control', 'readonly' => true]) }}
					</div>
                    <div class="form-group">
						{!! Form::label('address', 'Địa chỉ') !!}
						{{ Form::text('address', $seri->warranty_full_address, ['class' => 'form-control', 'readonly' => true]) }}
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success', 'disabled' => !$isAdmin]) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/activated-warranties') }}">Huỷ</a></button>
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
	$("#activated-warranties-edit-form").validate({
		
	});
});
</script>
@endpush
