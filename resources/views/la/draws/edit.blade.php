@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/draws') }}">Draw</a> :
@endsection
@section("contentheader_description", $draw->$view_col)
@section("section", "Draws")
@section("section_url", url(config('laraadmin.adminRoute') . '/draws'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Draws Edit : ".$draw->$view_col)

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
				{!! Form::model($draw, ['route' => [config('laraadmin.adminRoute') . '.draws.update', $draw->id ], 'method'=>'PUT', 'id' => 'draw-edit-form']) !!}
{{--					@la_form($module)--}}

					@la_input($module, 'index')
					@la_input($module, 'title')
					@la_input($module, 'prize')
					@la_input($module, 'prize_img')
					<div style="border: 1px solid #DDDDDD; padding:10px">
						@la_input($module, 'username')
						<div class="row">
							<div class="col-sm-6">
								@la_input($module, 'activated_at_from')
							</div>
							<div class="col-sm-6">
								@la_input($module, 'activated_at')
							</div>
						</div>
						<button class="btn btn-primary" id="update-lists">Cập nhập danh sách đại lý</button>
					</div>
					<div class="form-group">
						<label for="lists">Đại lý :</label>
						<textarea class="form-control valid" placeholder="Enter Đại lý" cols="30" rows="3" name="lists" aria-invalid="false">{{ $draw->lists }}</textarea>
					</div>
{{--					@la_input($module, 'lists')--}}
					@la_input($module, 'winner')

                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/draws') }}">Huỷ</a></button>
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
	$("#draw-edit-form").validate({
		
	});
	$('#update-lists').click(function () {
		el = $(this);
		el.prop('disabled', true);
		$.ajax({
			url: '{{ route('draws.update-lists') }}',
			data: {
				username: $('textarea[name="username"]').val(),
				activated_at_from: $('input[name="activated_at_from"]').val(),
				activated_at: $('input[name="activated_at"]').val()
			},
			success: function (data) {
				el.prop('disabled', false);
				$('textarea[name="lists"]').val(data)
			}
		})
	})
});
</script>
@endpush
