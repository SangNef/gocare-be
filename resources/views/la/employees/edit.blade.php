@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/employees') }}">Nhân viên</a> :
@endsection
@section("contentheader_description", $employee->$view_col)
@section("section", "Nhân viên")
@section("section_url", url(config('laraadmin.adminRoute') . '/employees'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa nhân viên : ".$employee->$view_col)

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
				{!! Form::model($employee, ['route' => [config('laraadmin.adminRoute') . '.employees.update', $employee->id ], 'method'=>'PUT', 'id' => 'employee-edit-form']) !!}
{{--					@la_form($module)--}}


					@la_input($module, 'name')
					@la_input($module, 'designation')
					@la_input($module, 'gender')
					@la_input($module, 'mobile')
					@la_input($module, 'mobile2')
					@la_input($module, 'email')
					@la_input($module, 'dept')
					@la_input($module, 'city')
					@la_input($module, 'address')
					@la_input($module, 'about')
					@la_input($module, 'date_birth')
					@la_input($module, 'date_hire')
					@la_input($module, 'date_left')
					@la_input($module, 'salary_cur')

                    <div class="form-group">
						<label for="role">Nhóm quản trị* :</label>
						<select class="form-control" id="employee-role-id"  required="1" data-placeholder="Select Role" rel="select2" name="role">
							<?php $roles = App\Role::all(); ?>
							@foreach($roles as $role)
								@if($role->id != 1 || Entrust::hasRole("SUPER_ADMIN"))
									@if($user->hasRole($role->name))
										<option value="{{ $role->id }}" selected>{{ $role->name }}</option>
									@else
										<option value="{{ $role->id }}">{{ $role->name }}</option>
									@endif
								@endif
							@endforeach
						</select>
					</div>
					<br>
					<div class="form-group" id="employee-store-id" style="display: none">
						<label for="role">Store* :</label>
						<select class="form-control" required="1" data-placeholder="Select Store" rel="select2" name="store_id">
							<option value=""></option>
							<?php $stores = App\Models\Store::all(); ?>
							@foreach($stores as $store)
								@if($user->store_id == $store->id)
									<option value="{{ $store->id }}" selected>{{ $store->name }}</option>
								@else
									<option value="{{ $store->id }}">{{ $store->name }}</option>
								@endif
							@endforeach
						</select>
					</div>
					<br>
					<div class="form-group">
						{!! Form::submit( 'Cập nhập', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/employees') }}">Huỷ</a></button>
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
	$("#employee-edit-form").validate({
		
	});
	$('#employee-role-id').change(function () {
		$('#employee-store-id').hide();
		if ($(this).val() == '{{ \App\Role::where('name', 'STORE_OWNER')->first()->id }}') {
			$('#employee-store-id').show();
		}
	}).change();
});
</script>
@endpush
