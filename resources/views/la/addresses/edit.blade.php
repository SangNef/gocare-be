@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/addresses') }}">{{ $name }}</a> :
@endsection
@section("contentheader_description", $address->$view_col)
@section("section", "")
@section("section_url", url(config('laraadmin.adminRoute') . '/addresses'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Address Edit : ".$address->$view_col)

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
				{!! Form::model($address, ['route' => [config('laraadmin.adminRoute') . '.addresses.update', $address->id ], 'method'=>'PUT', 'id' => 'address-edit-form']) !!}
					<div class="form-group">
						{!! Form::label('name', 'Tên') !!}
						{{ Form::text('name', $address->name, ['class' => 'form-control']) }}
					</div>
                    <br>
					<div class="form-group">
                        <input type="hidden" name="type" value="{{ $type }}"/>
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/addresses') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@if($childrens)
<div class="box">
	<div class="box-header"></div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<table class="table table-bordered">
					<thead>
						<tr class="success">
							<th>ID</th>
							<th>Tên</th>
            				<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach($childrens['data'] as $child)
							<tr>
								<td>{{ $child->id }}</td>
								<td>{{ $child->name }}</td>
								<td><a href="{{ url(config('laraadmin.adminRoute') . '/addresses/' . $child->id . '/edit?type=' . $childrens['type']) }}" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a></td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(function () {
});
</script>
@endpush
