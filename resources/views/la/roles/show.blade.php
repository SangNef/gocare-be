@extends('la.layouts.app')

@section('htmlheader_title')
	Xem chi tiết nhóm quản trị
@endsection


@section('main-content')
<div id="page-content" class="profile2">
	<div class="bg-primary clearfix">
		<div class="col-md-4">
			<div class="row">
				<div class="col-md-3">
					<!--<img class="profile-image" src="{{ asset('la-assets/img/avatar5.png') }}" alt="">-->
					<div class="profile-icon text-primary"><i class="fa {{ $module->fa_icon }}"></i></div>
				</div>
				<div class="col-md-9">
					<h4 class="name">{{ $role->$view_col }}</h4>
				</div>
			</div>
		</div>
		<div class="col-md-3">
		</div>
		<div class="col-md-4">
		</div>
		<div class="col-md-1 actions">
			@la_access("Roles", "edit")
				<a href="{{ url(config('laraadmin.adminRoute') . '/roles/'.$role->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
			@endla_access
			
			@la_access("Roles", "delete")
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.roles.destroy', $role->id], 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
				{{ Form::close() }}
			@endla_access
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/roles') }}" data-toggle="tooltip" data-placement="right" title="Back to Roles"><i class="fa fa-chevron-left"></i></a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> Thông tin chung</a></li>
		@role("SUPER_ADMIN")
		<li class=""><a role="tab" data-toggle="tab" href="#tab-access" data-target="#tab-access"><i class="fa fa-key"></i> Phân quyền truy cập</a></li>
		@endrole
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>Thông tin chung</h4>
					</div>
					<div class="panel-body">
						@la_display($module, 'name')
						@la_display($module, 'display_name')
						@la_display($module, 'description')
						@la_display($module, 'parent')
						@la_display($module, 'dept')
					</div>
				</div>
			</div>
		</div>
		@role("SUPER_ADMIN")
		<div role="tabpanel" class="tab-pane fade in p20 bg-white" id="tab-access">
			<div class="guide1">
				<span class="pull-left">Quyền truy cập cho nhóm quản trị: {{ $role->display_name }}</span>
				<i class="fa fa-circle gray"></i> Ẩn <i class="fa fa-circle orange"></i> Chỉ đọc <i class="fa fa-circle green"></i> Ghi
			</div>
			<form action="{{ url(config('laraadmin.adminRoute') . '/save_module_role_permissions/'.$role->id) }}" method="post">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table class="table table-bordered dataTable no-footer table-access">
					<thead>
						<tr class="blockHeader">
							<th width="30%">
								<input class="alignTop" type="checkbox" id="module_select_all" id="module_select_all" checked="checked">&nbsp; Chức năng
							</th>
							<th width="14%">
								<input type="checkbox" id="view_all" checked="checked">&nbsp; Xem
							</th>
							<th width="14%">
								<input type="checkbox" id="create_all" checked="checked">&nbsp; Thêm mới
							</th>
							<th width="14%">
								<input type="checkbox" id="edit_all" checked="checked">&nbsp; Sửa
							</th>
							<th width="14%">
							<input class="alignTop" id="delete_all" type="checkbox"  checked="checked">&nbsp; Xoá
							</th>
							<th width="14%">Phân quyền theo cột</th>
						</tr>
					</thead>
					@foreach($modules_access as $module)
						<tr>
							<td><input module_id="{{ $module->id }}" class="module_checkb" type="checkbox" name="module_{{$module->id}}" id="module_{{$module->id}}" checked="checked">&nbsp; {{ $module->name }}</td>
							<td><input module_id="{{ $module->id }}" class="view_checkb" type="checkbox" name="module_view_{{$module->id}}" id="module_view_{{$module->id}}" <?php if($module->accesses->view == 1) { echo 'checked="checked"'; } ?> ></td>
							<td><input module_id="{{ $module->id }}" class="create_checkb" type="checkbox" name="module_create_{{$module->id}}" id="module_create_{{$module->id}}" <?php if($module->accesses->create == 1) { echo 'checked="checked"'; } ?> ></td>
							<td><input module_id="{{ $module->id }}" class="edit_checkb" type="checkbox" name="module_edit_{{$module->id}}" id="module_edit_{{$module->id}}" <?php if($module->accesses->edit == 1) { echo 'checked="checked"'; } ?> ></td>
							<td><input module_id="{{ $module->id }}" class="delete_checkb" type="checkbox" name="module_delete_{{$module->id}}" id="module_delete_{{$module->id}}" <?php if($module->accesses->delete == 1) { echo 'checked="checked"'; } ?> ></td>
							<td>
								<a module_id="{{ $module->id }}" class="toggle-adv-access btn btn-default btn-sm hide_row"><i class="fa fa-chevron-down"></i></a>
							</td>
						</tr>
						<tr class="tr-access-adv module_fields_{{ $module->id }} hide" module_id="{{ $module->id }}" >
							<td colspan=6>
								<table class="table table-bordered">
								@foreach (array_chunk($module->accesses->fields, 3, true) as $fields)
									<tr>
										@foreach ($fields as $field)
											<td><div class="col-md-3"><input type="text" name="{{ $field['colname'] }}_{{ $module->id }}_{{ $role->id }}" value="{{ $field['access'] }}" data-slider-value="{{ $field['access'] }}" class="slider form-control" data-slider-min="0" data-slider-max="2" data-slider-step="1" data-slider-orientation="horizontal"  data-slider-id="{{ $field['colname'] }}_{{ $module->id }}_{{ $role->id }}"></div> {{ $field['label'] }} </td>
										@endforeach
									</tr>
								@endforeach
								</table>
							</td>
						</tr>
					@endforeach
				</table>
				<center><input class="btn btn-success" type="submit" name="Lưu"></center>
			</form>
			<!--<div class="text-center p30"><i class="fa fa-list-alt" style="font-size: 100px;"></i> <br> No posts to show</div>-->
		</div>
		@endrole
	</div>
	</div>
	</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/bootstrap-slider/slider.css') }}"/>
<style>
.btn-default{border-color:#D6D3D3}
.slider .tooltip{display:none !important;}
.slider.gray .slider-handle{background-color:#888;}
.slider.orange .slider-handle{background-color:#FF9800;}
.slider.green .slider-handle{background-color:#8BC34A;}

.guide1{text-align: right;margin: 0px 15px 15px 0px;font-size:16px;}
.guide1 .fa{font-size:22px;vertical-align:bottom;margin-left:17px;}
.guide1 .fa.gray{color:#888;}
.guide1 .fa.orange{color:#FF9800;}
.guide1 .fa.green{color:#8BC34A;}

.table-access{border:1px solid #CCC;}
.table-access thead tr{background-color: #DDD;}
.table-access thead tr th{border-bottom:1px solid #CCC;padding:10px 10px;text-align:center;}
.table-access thead tr th:first-child{text-align:left;}
.table-access input[type="checkbox"]{margin-right:5px;vertical-align:text-top;}
.table-access > tbody > tr > td{border-bottom:1px solid #EEE !important;padding:10px 10px;text-align:center;}
.table-access > tbody > tr > td:first-child {text-align:left;}

.table-access .tr-access-adv {background:#b9b9b9;}
.table-access .tr-access-adv .table{margin:0px;}
.table-access .tr-access-adv > td{padding: 7px 6px;}
.table-access .tr-access-adv .table-bordered td{padding:10px;}
</style>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/bootstrap-slider/bootstrap-slider.js') }}"></script>
<script>
$(function () {
	@role("SUPER_ADMIN")
	/* ================== Access Control ================== */
	
	$('.slider').slider();
	
	$(".slider.slider-horizontal").each(function(index) {
		var field = $(this).next().attr("name");
		var value = $(this).next().val();
		console.log(""+field+" ^^^ "+value);
		switch (value) {
			case '0':
				$(this).removeClass("orange");
				$(this).removeClass("green");
				$(this).addClass("gray");
				break;
			case '1':
				$(this).removeClass("gray");
				$(this).removeClass("green");
				$(this).addClass("orange");
				break;
			case '2':
				$(this).removeClass("gray");
				$(this).removeClass("orange");
				$(this).addClass("green");
				break;
		}
	});
	
	$('.slider').bind('slideStop', function(event) {
		if($(this).next().attr("name")) {
			var field = $(this).next().attr("name");
			var value = $(this).next().val();
			console.log(""+field+" = "+value);
			if(value == 0) {
				$(this).removeClass("orange");
				$(this).removeClass("green");
				$(this).addClass("gray");
			} else if(value == 1) {
				$(this).removeClass("gray");
				$(this).removeClass("green");
				$(this).addClass("orange");
			} else if(value == 2) {
				$(this).removeClass("gray");
				$(this).removeClass("orange");
				$(this).addClass("green");
			}
		}
	});	
	
	$("#module_select_all,  #view_all").on("change", function() {
		$(".module_checkb").prop('checked', this.checked);
		$(".view_checkb").prop('checked', this.checked);
		$(".edit_checkb").prop('checked', this.checked)
		$(".create_checkb").prop('checked', this.checked);
		$(".delete_checkb").prop('checked', this.checked);
		$("#module_select_all").prop('checked', this.checked);
		$("#view_all").prop('checked', this.checked);
		$("#create_all").prop('checked', this.checked);
		$("#edit_all").prop('checked', this.checked);
		$("#delete_all").prop('checked', this.checked);		
	});
	
	$(".module_checkb,  .view_checkb").on("change", function() {
		var val = $(this).attr( "module_id" );
		$("#module_"+val).prop('checked', this.checked)
		$("#module_view_"+val).prop('checked', this.checked);
		$("#module_create_"+val).prop('checked', this.checked)
		$("#module_edit_"+val).prop('checked', this.checked);
		$("#module_delete_"+val).prop('checked', this.checked);
	});
	
	$(".create_checkb,  .edit_checkb, .delete_checkb").on("change", function() {
		var val = $(this).attr( "module_id" );
		$(this).prop('checked', this.checked);
		if(!$("#module_"+val).is(':checked')){
			$("#module_"+val).prop('checked', this.checked);
		}
		if(!$("#module_view_"+val).is(':checked')){
			$("#module_view_"+val).prop('checked', this.checked);
		}		
	});
	
	$("#create_all").on("change", function() {
		$(".create_checkb").prop('checked', this.checked);
		if($('#create_all').is(':checked')){
			$(".module_checkb").prop('checked', this.checked);
			$(".view_checkb").prop('checked', this.checked);
			$("#module_select_all").prop('checked', this.checked);
			$("#view_all").prop('checked', this.checked);
		}
	});
	
	$("#edit_all").on("change", function() {
		$(".edit_checkb").prop('checked', this.checked);
		if($('#edit_all').is(':checked')){
			$(".module_checkb").prop('checked', this.checked);
			$(".view_checkb").prop('checked', this.checked);
			$("#module_select_all").prop('checked', this.checked);
			$("#view_all").prop('checked', this.checked);
		}
	});
	
	$("#delete_all").on("change", function() {
		$(".delete_checkb").prop('checked', this.checked);
		if($('#delete_all').is(':checked')){
			$(".module_checkb").prop('checked', this.checked);
			$(".view_checkb").prop('checked', this.checked);
			$("#module_select_all").prop('checked', this.checked);
			$("#view_all").prop('checked', this.checked);
		}
	});
	
	$(".hide_row").on("click", function() { 
		var val = $(this).attr( "module_id" );
		var $icon = $(".hide_row[module_id="+val+"] > i");
		if($('.module_fields_'+val).hasClass('hide')) {
			$('.module_fields_'+val).removeClass('hide');
			$icon.removeClass('fa-chevron-down');
			$icon.addClass('fa-chevron-up');
		} else {
			$('.module_fields_'+val).addClass('hide');
			$icon.removeClass('fa-chevron-up');
			$icon.addClass('fa-chevron-down');
		}
	});
	@endrole
});
</script>
@endpush

