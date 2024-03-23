@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/groups') }}">{{ trans('messages.groups') }}</a> :
@endsection
@section("contentheader_description", $group->$view_col)
@section("section", trans('messages.groups'))
@section("section_url", url(config('laraadmin.adminRoute') . '/groups'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Groups Edit : ".$group->$view_col)

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
				{!! Form::model($group, ['route' => [config('laraadmin.adminRoute') . '.groups.update', $group->id ], 'method'=>'PUT', 'id' => 'group-edit-form']) !!}
					@la_input($module, 'name')
					@la_input($module, 'display_name')
					@if(!auth()->user()->haveRoleMustBeExcludeFromRoutes())
					<div class="form-group">
						<label for="status" style="margin-right: 20px">Kho :</label>
						<select name="store_id" id="store_id" class="form-control ajax-select" model="stores">
						</select>
					</div>
					@else
					<input type="hidden" name="store_id" value="{{ auth()->user()->store_id }}"/>
					@endif
                    <br>
					<div class="form-group">
						<label for="action">{{ trans('messages.product_category') }} :</label><select
								class="form-control select2-hidden-accessible"
								data-placeholder="{{ trans('messages.product_category') }}" multiple="" rel="select2" name="product_category_ids[]"
								tabindex="-1" aria-hidden="true">
								@foreach(\App\Models\ProductCategory::all() as $category)
									<option value="{{ $category->id }}" @if (in_array($category->id, $group->product_category_ids ? json_decode($group->product_category_ids, true) : [])) selected="selected" @endif>{{ $category->name }}</option>
								@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="require_payment">
							<input id="require_payment" name="require_payment" type="checkbox" style="margin-right: 5px;" value="1" @if (!$group->require_payment) checked="checked" @endif>
							Không cần thanh toán trước
						</label>
					</div>
					<br>
					<div class="form-group">
						{!! Form::submit( trans('messages.update'), ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/groups') }}">{{ trans('messages.cancel') }}</a></button>
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
	$("#group-edit-form").validate({
		
	});
});
</script>
@endpush
