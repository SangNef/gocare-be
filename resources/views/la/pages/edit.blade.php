@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/pages') }}">Quản lý trang</a> :
@endsection
@section("contentheader_description", $page->$view_col)
@section("section", "Trang")
@section("section_url", url(config('laraadmin.adminRoute') . '/pages'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa trang : ".$page->$view_col)

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
				{!! Form::model($page, ['route' => [config('laraadmin.adminRoute') . '.pages.update', $page->id ], 'method'=>'PUT', 'id' => 'page-edit-form']) !!}					
					<div class="box-body">
						<div class="form-group">
							<label for="title">Tiêu đề :</label>
							<input id="title" class="form-control" placeholder="Enter Tiêu đề" name="title" value="{{ $page->title }}" />
						</div>
						<div class="form-group">
							<label for="slug">Slug :</label>
							<input id="slug" class="form-control" name="slug" readonly value="{{ $page->slug }}"/>
						</div>
						<div class="form-group">
							<label for="price">Nội dung :</label>
							<textarea class="form-control tinymce" placeholder="Enter nội dung" name="content">{!! $page->content !!}</textarea>
						</div>
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/pages') }}">Huỷ</a></button>
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
	$("#page-edit-form").validate({
		
	});
	$('#title').change(function() {
        let slug = $(this).val().toLowerCase()
            .replace(/[^\w ]+/g,'')
            .replace(/ +/g,'-');
        $('#slug').val(slug);
    });
});
</script>
@endpush
