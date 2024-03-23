@extends("la.layouts.app")

@section("contentheader_title", "AZPro Configuration")
@section("contentheader_description", "")
@section("section", "AZPro Configuration")
@section("sub_section", "")
@section("htmlheader_title", "AZPro Configuration")

@section("headerElems")
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
	<form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">Ảnh Slider trang chủ</h3>
			</div>
			<div class="box-body">
			{{ csrf_field() }}
				<div class="row">
					<div class="col-sm-10">
						<div class="form-group">
                            <label for="azpro_slider" style="display:block;">
                                Chọn ảnh :
                            </label>
                            <input class="form-control" placeholder="Enter Thư viện ảnh sản phẩm" name="azpro_slider" type="hidden" value="{{ json_encode(array_keys($sliders)) }}">
                            <div class="uploaded_files">
                                
                                @foreach($sliders as $id => $path)
                                    <a class="uploaded_file2" upload_id="{{ $id }}" target="_blank" href="{{ $path }}">
                                        <img src="{{ $path }}">
                                        <i title="Remove File" class="fa fa-times"></i>
                                    </a>
                                @endforeach
                            </div>
                            <a class="btn btn-default btn_upload_files" file_type="files" selecter="azpro_slider" style="margin-top:5px;">Upload <i class="fa fa-cloud-upload"></i></a>
                        </div>
					</div>
				</div>
			</div>
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
@endsection

@push('styles')
	<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
	<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
	<script>
		$(function() {
			
		})
	</script>

@endpush
