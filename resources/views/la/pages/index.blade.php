@extends("la.layouts.app")

@section("contentheader_title", "Quản lý trang")
@section("contentheader_description", "Danh sách trang")
@section("section", "Quản lý trang")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Danh sách trang")

@section("headerElems")
@la_access("Pages", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm trang</button>
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

@include('la.datatable.index', ['id' => 'page', 'path' => 'page_dt_ajax', 'cols' => [
    [
        'title' => 'ID',
        'field' => 'id',
    ],
    [
        'title' => 'Tiêu đề',
        'field' => 'title',
    ],
    [
        'title' => 'Slug',
        'field' => 'slug',
    ],
    [
        'title' => 'Ngày tạo',
        'field' => 'created_at',
    ]
]])

@la_access("Pages", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-70" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm trang</h4>
			</div>
			{!! Form::open(['action' => 'LA\PagesController@store', 'id' => 'page-add-form', 'method' => 'POST']) !!}
			<div class="modal-body">
				<div class="box-body">
                    <div class="form-group">
                        <label for="title">Tiêu đề :</label>
                        <input id="title" class="form-control" placeholder="Enter Tiêu đề" name="title" />
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug :</label>
                        <input id="slug" class="form-control" name="slug" readonly />
                    </div>
					<div class="form-group">
                        <label for="price">Nội dung :</label>
                        <textarea class="form-control tinymce" placeholder="Enter nội dung" name="content"></textarea>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
				{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!}
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
	$("#page-add-form").validate({
		
	});
    $('#title').change(function() {
        let slug = $(this).val().toLowerCase()
            .replace(/[^\w ]+/g,'')
            .replace(/ +/g,'-');
        console.log(slug);
        $('#slug').val(slug);
    });
    
});
</script>
@endpush
