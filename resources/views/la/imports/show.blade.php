@extends('la.layouts.app')

@section('htmlheader_title')
	Import View
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
					<h4 class="name">{{ $import->$view_col }}</h4>
					<div class="row stats">
						<div class="col-md-4"><i class="fa fa-facebook"></i> 234</div>
						<div class="col-md-4"><i class="fa fa-twitter"></i> 12</div>
						<div class="col-md-4"><i class="fa fa-instagram"></i> 89</div>
					</div>
					<p class="desc">Test Description in one line</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="dats1"><div class="label2">Admin</div></div>
			<div class="dats1"><i class="fa fa-envelope-o"></i> superadmin@gmail.com</div>
			<div class="dats1"><i class="fa fa-map-marker"></i> Pune, India</div>
		</div>
		<div class="col-md-4">
			<!--
			<div class="teamview">
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user1-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user2-160x160.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user3-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user4-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user5-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user6-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user7-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user8-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user5-128x128.jpg') }}" alt=""></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user6-128x128.jpg') }}" alt=""><i class="status-online"></i></a>
				<a class="face" data-toggle="tooltip" data-placement="top" title="John Doe"><img src="{{ asset('la-assets/img/user7-128x128.jpg') }}" alt=""></a>
			</div>
			-->
			<div class="dats1 pb">
				<div class="clearfix">
					<span class="pull-left">Task #1</span>
					<small class="pull-right">20%</small>
				</div>
				<div class="progress progress-xs active">
					<div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
						<span class="sr-only">20% Complete</span>
					</div>
				</div>
			</div>
			<div class="dats1 pb">
				<div class="clearfix">
					<span class="pull-left">Task #2</span>
					<small class="pull-right">90%</small>
				</div>
				<div class="progress progress-xs active">
					<div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 90%" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
						<span class="sr-only">90% Complete</span>
					</div>
				</div>
			</div>
			<div class="dats1 pb">
				<div class="clearfix">
					<span class="pull-left">Task #3</span>
					<small class="pull-right">60%</small>
				</div>
				<div class="progress progress-xs active">
					<div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 60%" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
						<span class="sr-only">60% Complete</span>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-1 actions">
			@la_access("Imports", "edit")
				<a href="{{ url(config('laraadmin.adminRoute') . '/imports/'.$import->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
			@endla_access
			
			@la_access("Imports", "delete")
				{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.imports.destroy', $import->id], 'method' => 'delete', 'style'=>'display:inline']) }}
					<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
				{{ Form::close() }}
			@endla_access
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/imports') }}" data-toggle="tooltip" data-placement="right" title="Back to Imports"><i class="fa fa-chevron-left"></i></a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> Thông tin chung</a></li>
		<li class=""><a role="tab" data-toggle="tab" href="#tab-timeline" data-target="#tab-products"><i class="fa fa-clock-o"></i> Sản phẩm</a></li>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>General Info</h4>
					</div>
					<div class="panel-body">
						@la_display($module, 'store_id')
						@la_display($module, 'customer_id')
						@la_display($module, 'code')
						@la_display($module, 'status')
						@la_display($module, 'imported_at')
					</div>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade in bg-white" id="tab-products">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>Sản phẩm</h4>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="store_id" class="col-md-2">Tổng sản phẩm :</label>
							<div class="col-md-10 fvalue">{{ $import->products->count() }}</div>
						</div>
						<div class="form-group">
							<label for="store_id" class="col-md-2">Tổng số lượng :</label>
							<div class="col-md-10 fvalue">{{ number_format($import->products->sum('quantity')) }}</div>
						</div>
						<div class="form-group">
							<label for="store_id" class="col-md-2">Tổng đã hoàn thành :</label>
							<div class="col-md-10 fvalue">{{ number_format($import->products->sum('done')) }} <button style="margin-left: 10px" class="btn btn-success btn-sm" form="update-import">Cập nhập</button></div>
						</div>
						{{ Form::open(['id' => 'update-import', 'route' => ['imports.update-done', $import->id ]]) }}
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>STT</th>
									<th>Tên sản phẩm</th>
									<th>Thuộc tính</th>
									<th>Số lượng</th>
									<th>Đã hoàn thành</th>
									<th>Ghi chú</th>
									<th><button type="button" data-toggle="modal" data-target="#printSeries" data-id="0" class="btn btn-warning btn-xs print-series" style="display:inline;padding:2px 5px 3px 5px;">In tất cả</button></th>
								</tr>
							</thead>
							<tbody>
								@foreach ($import->products as $index => $product)
									<tr>
										<td>{{ $index + 1 }}</td>
										<td>{{ $product->product->name }}</td>
										<td>{{ $product->attrsName() }}</td>
										<td>{{ number_format($product->quantity) }}</td>
										<td><input class="form-control" type="number" name="quantity[{{ $product->id }}]" value="{{($product->done) }}" /></td>
										<td>{{ $product->note }}</td>
										<td><button type="button" data-toggle="modal" data-target="#printSeries" data-id="{{ $product->id }}" class="btn btn-warning btn-xs print-series" style="display:inline;padding:2px 5px 3px 5px;">IN</button></td>
									</tr>
								@endforeach
							</tbody>
						</table>
						{{ Form::close() }}
					</div>
				</div>
			</div>
		</div>
		
	</div>
	</div>
	</div>
</div>
<div class="modal fade" id="printSeries" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{{ Form::open(['id' => 'print-series-form']) }}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">{{ trans('product.print_series') }}</h4>
			</div>
			<div class="modal-body">
				<div class="box-body">
					<input type="hidden" name="ip_id" id="ip_id">
					<div class="form-group">
						<label style="margin-right: 20px">Chọn khổ giấy: </label>
						<label style="margin-right: 10px"><input type="radio" value="3" name="per_row" checked>35 x 25</label>
						<label><input type="radio" value="4" name="per_row">25 x 25</label>
						<label><input type="radio" value="2" name="per_row">50 x 50</label>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('messages.cancel') }}</button>
					<button type="submit" class="btn btn-success" id="import-print-series">In</button>
				</div>
			</div>
			{{ Form::close() }}
		</div>
	</div>
</div>
@endsection
@push('scripts')
<script>
	function printSeries(perRow = 3, ipId = 0) {
		url = '{{ route('import.series-print', ['id' => $import->id]) }}?per_row=' + perRow + '&ip_id=' + ipId;
		let iframe = document.createElement('iframe');
		iframe.className='pdfIframe'
		document.body.appendChild(iframe);
		iframe.style.display = 'none';
		iframe.src = url;
		return new Promise((resolve) => {
			iframe.onload = function () {
				setTimeout(function () {
					iframe.focus();
					URL.revokeObjectURL(url);
					document.body.removeChild(iframe);
				}, 1);
				resolve(true);
			};
		})
	}

	$(function () {
		$(document).on('click', '.print-series', function (event) {
			$('#ip_id').val($(this).attr('data-id'));
		});
		$('#print-series-form').submit(function(e) {
			e.preventDefault();
			const formData = $(this).serializeObject();
			let perRow = formData.per_row;
			let ipId = formData.ip_id;
			printSeries(perRow, ipId);
		});
	});
</script>
@endpush