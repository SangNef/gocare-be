@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/request-warranties') }}">RequestWarranties</a> :
@endsection
@section("contentheader_description", $requestWarranty->$view_col)
@section("section", "RequestWarranties")
@section("section_url", url(config('laraadmin.adminRoute') . '/request-warranties'))
@section("sub_section", "Edit")

@section("htmlheader_title", "RequestWarranties Edit : ".$requestWarranty->$view_col)

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
		<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
			<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i>{{ trans('messages.general_info') }}</a></li>
			<li><a role="tab" data-toggle="tab" href="#tab-histories" data-target="#tab-histories"><i class="fa fa-clock-o"></i>{{ trans('messages.request_warranty_histories') }}</a></li>
		</ul>
	</div>
	<div class="box-body">
	{!! Form::model($requestWarranty, ['route' => [config('laraadmin.adminRoute') . '.request-warranties.update', $requestWarranty->id ], 'method'=>'PUT', 'id' => 'request-warranties-edit-form']) !!}
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
						@if(!empty($images))
						<div class="form-group">
							<label for="product_gallery" style="display:block;">Thư viện ảnh sản phẩm :</label>
							<div class="uploaded_files" style="overflow: auto; height: 100px">
								@foreach($images as $imagePath)
								<a class="uploaded_file2" target="_blank" href="{{ $imagePath }}" style="width: 90px; height: 90px">
									<img src="{{ $imagePath }}" style="width: 100%; height: 100%">
								</a>
								@endforeach
							</div>
						</div>
						@endif
						<div class="form-group">
							{!! Form::label('seri_number', 'Mã Seri') !!}
							{{ Form::text('seri_number', $requestWarranty->seri_number, ['class' => 'form-control', 'readonly' => true]) }}
						</div>
						<div class="form-group">
							{!! Form::label('product_name', 'Tên sản phẩm') !!}
							{{ Form::text('product_name', $requestWarranty->product_name, ['class' => 'form-control', 'readonly' => true]) }}
						</div>
						<div class="form-group">
							{!! Form::label('name', 'Họ tên') !!}
							{{ Form::text('name', $requestWarranty->name, ['class' => 'form-control', 'readonly' => true]) }}
						</div>
						<div class="form-group">
							{!! Form::label('address_id', 'Địa chỉ') !!}
							{{ Form::text('', $requestWarranty->getFullAddress(), ['class' => 'form-control', 'disabled' => true]) }}
						</div>
						<div class="form-group">
							{!! Form::label('phone', 'SĐT') !!}
							{{ Form::text('phone', $requestWarranty->phone, ['class' => 'form-control', 'disabled' => true]) }}
						</div>
						<div class="form-group">
							{!! Form::label('content', 'Nội dung') !!}
							{{ Form::textarea('content', $requestWarranty->content, ['class' => 'form-control']) }}
						</div>
						<div class="form-group">
							{!! Form::label('status', 'Trạng thái') !!}
							{{ Form::select('status', \App\Models\RequestWarranty::getListStatus(), $requestWarranty->status, ['id' => 'status', 'class' => 'form-control']) }}
						</div>
						<div class="form-group">
							<label for="user_id">Người xử lý</label>
							<select id="user_id" name="user_id" @if($requestWarranty->user_id) disabled @endif class="form-control ajax-select" model="user" data-allow-clear="false">
								@if($user = $requestWarranty->user)
									<option value="{{ $user->id }}" selected>{{ $user->name }}</option>
								@endif
							</select>
						</div>
						<div class="form-group">
							<label for="group_id">Đơn vị xử lý</label>
							<select id="group_id" name="group_id" @if($requestWarranty->group_id) disabled @endif class="form-control ajax-select" model="group" data-allow-clear="false">
								@if($group = $requestWarranty->group)
									<option value="{{ $group->id }}" selected>{{ $group->display_name }}</option>
								@endif
							</select>
						</div>
						<br>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="tab-histories">
						<div class="row">
							<div class="col-12 history-info">
							@if(!$histories->isEmpty())
							@foreach($histories as $key => $history)
								<div class="row history-detail">
									<div class="col-sm-2">
										<div class="form-group">
											<label for="status">Ngày:</label>
											<input class="form-control datepicker" name="histories[{{ $key }}][created_at]" value="{{ $history->created_at->format('Y/m/d') }}" disabled/>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label for="status">Chi tiết :</label>
											<input class="form-control" name="histories[{{ $key }}][detail]" value="{{ $history->detail }}" disabled/>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="status">Người xử lý :</label>
											<input type="text" class="form-control" disabled value="{{ $history->handler->name }}">
										</div>
									</div>
									<div class="col-sm-1">
										<div class="form-group">
											<button type="button" class="btn btn-success btn-xs add-history-detail" @if($requestWarranty->status == 2) disabled @endif><i class="fa fa-plus"></i></button>
										</div>
									</div>
								</div>
							@endforeach
							@else
								<div class="row history-detail">
									<div class="col-sm-2">
										<div class="form-group">
											<label for="status">Ngày:</label>
											<input class="form-control datepicker" name="histories[1][created_at]" value="{{ \Carbon\Carbon::today()->format('Y/m/d') }}"/>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label for="status">Chi tiết :</label>
											<input class="form-control" name="histories[1][detail]" />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label for="status">Người xử lý :</label>
											<input type="hidden" class="form-control" name="histories[1][handler_id]" value="{{ auth()->user()->id }}">
											<input type="text" class="form-control" disabled value="{{ auth()->user()->name }}">
										</div>
									</div>
									<div class="col-sm-1">
										<div class="form-group">
											<button type="button" class="btn btn-success btn-xs add-history-detail"><i class="fa fa-plus"></i></button>
											<button type="button" class="btn btn-danger btn-xs remove-history-detail" disabled><i class="fa fa-minus"></i></button>
										</div>
									</div>
								</div>
							@endif
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-12" style="text-align: right">
				<button type="submit" class="btn btn-success onetime-click" @if($requestWarranty->status == 2) disabled @endif>{{ trans('button.save') }}</button>
			</div>
		</div>
	{!! Form::close() !!}
	</div>
</div>

@endsection
@include('la.request_warranties.script')
@push('scripts')
<script>
$(function () {
	$("#request-warranties-edit-form").validate({
		
	});
});
</script>
@endpush
