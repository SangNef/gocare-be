@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/produces') }}">Produce</a> :
@endsection
@section("contentheader_description", $produce->$view_col)
@section("section", "Produces")
@section("section_url", url(config('laraadmin.adminRoute') . '/produces'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Produces Edit : ".$produce->$view_col)

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
				{!! Form::model($produce, ['route' => @$is_coping ? [config('laraadmin.adminRoute') . '.produces.store'] : [config('laraadmin.adminRoute') . '.produces.update', $produce->id ], 'method'=> (@$is_coping ? 'POST' : 'PUT'), 'id' => 'produce-edit-form']) !!}

					@la_input($module, 'store_id')
					@la_input($module, 'group_id')
					@la_input($module, 'description')
					@la_input($module, 'quantity')
					<div class="form-group">
						<label for="product_id">Sản phẩm* :</label>
						<select class="ajax-select product-id" model="product" name="product_id" required="1" data-placeholder="Chọn sản phẩm">
							<option value="{{ $produce->product_id }}" selected="selected">{{ $produce->product->name }} - {{ $produce->product->sku }}</option>
						</select>
					</div>
					<div class="product-attrs">
						@if (!empty($attrs))
							@foreach ($attrs as $attr)
								<div class="form-group">
									<label>{{ $attr['text'] }}</label>
									<select class="form-control" name="attrs_value[]" required="1">
										@foreach ($attr['values'] as $value) 
											<option value="{{ $value['id'] }}" @if (@$value["selected"]) selected="selected" @endif>{{ $value['value'] }}</option>
										@endforeach
									</select>
								</div>	
							@endforeach 
						@endif
					</div>
                    <br>
					<div class="col-sm-12">
						<label><strong>Sản phẩm con cấu thành lên sản phẩm chính</strong></label>
					</div>
					<div class="col-sm-12" style="margin-bottom: 10px">
						<div class="col-sm-9">Sản phẩm*</div>
						<div class="col-sm-2">Số lượng*</div>
						<div class="col-sm-1">
							@if (!$produce->isSuccess())
								<button type="button" class="btn btn-success btn-sm ml-3 p-product-add">Thêm</button>
							@endif
						</div>
					</div>
					<div class="p-product-items">
						@foreach ($produce->products as $index => $product)
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
						</div>
						@endforeach
					</div>
					<div class="form-group">
						@if (!$produce->isSuccess() || @$is_coping){!! Form::submit( @$is_coping ? 'Add' : 'Lưu', ['class'=>'btn btn-success']) !!} @endif
						<button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/produces') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@endsection
@include('la.produces.scripts', ['itemIndex' => $produce->products->count()])
@push('scripts')
<script>
$(function () {
	$("#produce-edit-form").validate({
		
	});
});
</script>
@endpush
