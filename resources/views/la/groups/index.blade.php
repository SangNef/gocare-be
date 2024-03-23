@extends("la.layouts.app")

@section("contentheader_title", trans('messages.groups'))
@section("section", trans('messages.groups'))
@section("sub_section", trans('messages.groups'))
@section("htmlheader_title", trans('messages.groups'))

@section("headerElems")
@la_access("Groups", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">{{ trans('messages.add-group')  }}</button>
@endla_access
@endsection

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

<div class="box box-success">
	<!--<div class="box-header"></div>-->
	<div class="box-body">
		<table id="example1" class="table table-bordered">
		<thead>
		<tr class="success">
			@foreach( $listing_cols as $col )
			<th>{{ $module->fields[$col]['label'] or ucfirst($col) }}</th>
			@endforeach
			@if($show_actions)
			<th>Actions</th>
			@endif
		</tr>
		</thead>
		<tbody>
			
		</tbody>
		</table>
	</div>
</div>

@la_access("Groups", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">{{ trans('messages.add-group')  }}</h4>
			</div>
			{!! Form::open(['action' => 'LA\GroupsController@store', 'id' => 'group-add-form']) !!}
			<div class="modal-body">
				<div class="box-body">
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
					<div class="form-group">
						<label for="action">{{ trans('messages.product_category') }} :</label><select
								class="form-control select2-hidden-accessible"
								data-placeholder="{{ trans('messages.product_category') }}" multiple="" rel="select2" name="category_ids[]"
								tabindex="-1" aria-hidden="true">
							@foreach(\App\Models\ProductCategory::where('use_at_fe', 1)->get() as $category)
								<option value="{{ $category->id }}">{{ $category->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="require_payment">
							<input id="require_payment" name="require_payment" type="checkbox" style="margin-right: 5px;" value="1">
							Không cần thanh toán trước
						</label>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel')  }}</button>
				{!! Form::submit( trans('messages.submit'), ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endla_access

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
$(function () {
	$("#example1").DataTable({
		processing: true,
        serverSide: true,
        ajax: "{{ url(config('laraadmin.adminRoute') . '/group_dt_ajax') }}",
		language: {
			lengthMenu: "_MENU_",
			search: "_INPUT_",
			searchPlaceholder: "Search"
		},
		@if($show_actions)
		columnDefs: [ { orderable: false, targets: [-1] }],
		@endif
	});
	$("#group-add-form").validate({
		
	});
});
</script>
@endpush
