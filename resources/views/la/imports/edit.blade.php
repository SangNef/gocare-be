@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/imports') }}">Import</a> :
@endsection
@section("contentheader_description", $import->$view_col)
@section("section", "Imports")
@section("section_url", url(config('laraadmin.adminRoute') . '/imports'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Imports Edit : ".$import->$view_col)

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
				{!! Form::model($import, ['route' => [config('laraadmin.adminRoute') . '.imports.update', $import->id ], 'method'=>'PUT', 'id' => 'import-edit-form']) !!}
{{--					@la_form($module)--}}
					@la_input($module, 'code')
					@la_input($module, 'store_id')
					@la_input($module, 'customer_id')
                    <br>
					<div class="col-sm-12">
						<label><strong>Sản phẩm</strong></label>
					</div>
					<div class="col-sm-12" style="margin-bottom: 10px">
						<div class="col-sm-9">Sản phẩm*</div>
						<div class="col-sm-2">Số lượng*</div>
						<div class="col-sm-1"><button type="button" class="btn btn-success btn-sm ml-3 p-product-add">Thêm</button></div>
					</div>
					<div class="p-product-items">
						@foreach ($import->products as $index => $product)
							<div class="col-sm-12 p-product-item">
								<div class="col-sm-6">
									<select class="ajax-select p-product-id p-product-{{ $index  }}" required="1" model="product" name="products[{{ $index  }}][product_id]">
										<option value="{{ $product->product_id }}" selected="selected">{{ $product->product->name }}</option>
									</select>
								</div>
								<div class="col-sm-3 p-product-attrs">
									@if ($product->attrs_value)
										@foreach ($product->attrs() as $attr)
											<div class="form-group">
												<select class="form-control" name="products[{{ $index  }}][attrs_value][]" required="1">
													@foreach ($attr['values'] as $value)
														<option value="{{ $value['id'] }}" @if (@$value["selected"]) selected="selected" @endif>{{ $value['value'] }}</option>
													@endforeach
												</select>
											</div>
										@endforeach
									@endif
								</div>
								<div class="col-sm-2">
									<input class="form-control" required="1" name="products[{{ $index  }}][quantity]" value="{{ $product->quantity }}" />
								</div>
								<div class="col-sm-1">
									<button type="button" class="btn btn-danger btn-sm p-product-remove"><i class="fa fa-times"></i></button>
								</div>
								<div class="col-sm-12" style="margin-top: 5px;">
									<input class="form-control" name="products[{{ $index  }}][note]" placeholder="Ghi chú" value="{{ $product->note }}" />
								</div>
							</div>
						@endforeach
					</div>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/imports') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@endsection

@include('la.produces.scripts', ['itemIndex' => $import->products->count(), 'includeNote' => 1])
@push('scripts')
<script>
$(function () {
	$("#import-edit-form").validate({
		
	});
});
</script>
@endpush
