@extends("la.layouts.app")

@section("contentheader_title", trans('configuration'))
@section("contentheader_description", "")
@section("section", "Configuration")
@section("sub_section", "")
@section("htmlheader_title", "Configuration")

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
		<!-- general form elements disabled -->
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">{{ trans('messages.contact') }}</h3>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
			{{ csrf_field() }}
			<!-- text input -->
				@foreach($contacts as $config)
					<div class="form-group">
						<label>{{ trans('config.' . $config->key) }}</label>
						<input type="text" class="form-control" name="{{ $config->key }}" value="{{ $config->value }}" />
					</div>
				@endforeach
			</div><!-- /.box-body -->
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div><!-- /.box-footer -->
		</div><!-- /.box -->
	</form>
	<form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
		<!-- general form elements disabled -->
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">AZPro</h3>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
			{{ csrf_field() }}
			<!-- text input -->
				@foreach($azConfigs as $config)
				<div class="form-group">
					<label for="">{{ trans('config.'.$config) }}</label>
					<select name="{{ $config }}" class="form-control ajax-select" model="product" placeholder="{{ trans('messages.all') }}" data-allow-clear="false">
						@php
							$product = app(\App\Models\Config::class)->getAzConfigProduct($config);
						@endphp
						@if($product)
							<option value="{{ $product->id }}" selected>{{ $product->name }}</option>
						@endif
					</select>
				</div>
				@endforeach
				
			</div><!-- /.box-body -->
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div><!-- /.box-footer -->
		</div><!-- /.box -->
	</form>
	<form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
		{{ csrf_field() }}
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">{{ trans('config.vtp_config') }}</h3>
			</div>
			<div class="box-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label>Tên đăng nhập :</label>
							<input type="text" class="form-control" name="vtp_configs[username]" value="{{ $vtpConfig->username }}">
						</div>
						<div class="form-group">
							<label>Mật khẩu</label>
							<input type="password" class="form-control" name="vtp_configs[password]" value="{{ $vtpConfig->password }}">
						</div>
					</div>
				</div>
			</div>
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
	<form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
		{{ csrf_field() }}
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">{{ trans('config.ghn_config') }}</h3>
			</div>
			<div class="box-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label>Token :</label>
							<input type="text" class="form-control" name="ghn_configs[token]" value="{{ $ghnConfig ? $ghnConfig->token : "" }}">
						</div>
					</div>
				</div>
			</div>
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
	<form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
		{{ csrf_field() }}
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">{{ trans('config.ghtk_config') }}</h3>
			</div>
			<div class="box-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label>Token :</label>
							<input type="text" class="form-control" name="ghtk_configs[token]" value="{{ $ghtkConfig ? $ghtkConfig->token : "" }}">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label>Webhook Access token :</label>
							<input type="text" class="form-control" name="ghtk_default_webhook_token" disabled value="{{ $ghtkDefaultAccessToken ?? "" }}">
						</div>
					</div>
				</div>
			</div>
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
	<form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
		{{ csrf_field() }}
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">{{ trans('config.warranty_units') }}</h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<label for="warranty_units">{{ trans('config.warranty_units') }}</label>
					<select id="warranty_units" name="warranty_units[]" multiple class="form-control ajax-select" model="group" data-allow-clear="true">
						@foreach($warrantyUnits as $id => $unit)
							<option value="{{ $id }}" selected>{{ $unit }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
	<form action="{{route(config('laraadmin.adminRoute').'.configs.store')}}" method="POST">
		{{ csrf_field() }}
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">{{ trans('config.fe_discount_groups') }}</h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<label for="fe_discount_groups">{{ trans('config.fe_discount_groups') }}</label>
					<select id="fe_discount_groups" name="fe_discount_groups[]" multiple model="group" class="form-control ajax-select" data-allow-clear="true">
						@foreach($feDiscountGroups as $id => $group)
							<option value="{{ $id }}" selected>{{ $group }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="box-footer">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
	<form action="{{route(config('laraadmin.adminRoute').'.configs.homepage-exclude-categories')}}" method="POST">
		{{ csrf_field() }}
		<div class="box box-warning">
			<div class="box-header with-border">
				<h3 class="box-title">{{ trans('config.homepage_exclude_categories') }}</h3>
			</div>
			<div class="box-body">
				<div class="form-group">
					<label for="fe_discount_groups">{{ trans('config.homepage_exclude_categories') }}</label>
					<select id="fe_discount_groups" name="exclude_categories[]" multiple model="category" class="form-control ajax-select" data-allow-clear="true">
						@foreach($feDiscountGroups as $id => $group)
							<option value="{{ $id }}" selected>{{ $group }}</option>
						@endforeach
					</select>
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

@endpush
