@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/posts') }}">Tin tức</a> :
@endsection
@section("contentheader_description", $post->$view_col)
@section("section", "Tin tức")
@section("section_url", url(config('laraadmin.adminRoute') . '/posts'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa tin tức : ".$post->$view_col)

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
				{!! Form::model($post, ['route' => [config('laraadmin.adminRoute') . '.posts.update', $post->id ], 'method'=>'PUT', 'id' => 'post-edit-form']) !!}
{{--					@la_form($module)--}}

					@la_input($module, 'cate_id')
					@la_input($module, 'image')
					@la_input($module, 'title')
					@la_input($module, 'status')
					<div class="form-group">
						<label for="price">Mô tả ngắn :</label>
						<textarea class="form-control tinymce" placeholder="Nhập mô tả ngắn " cols="30" rows="3" name="short_content">{!! $post->short_content !!}</textarea>
					</div>
					<div class="form-group">
						<label for="price">Nội dung :</label>
						<textarea class="form-control tinymce" placeholder="Nhập nội dung tin tức" cols="30" rows="3" name="content">{!! $post->content !!}</textarea>
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/posts') }}">Huỷ</a></button>
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
	$("#post-edit-form").validate({
		
	});
});
</script>
@endpush
