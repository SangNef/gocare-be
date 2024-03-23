@extends('la.layouts.app')

@section('htmlheader_title')
	Xem chi tiết giao dịch
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
				<div class="col-md-6">
					<h4 class="name">{{ $transaction->$view_col }}</h4>
				</div>
				<div class="col-md-3 actions">
					@la_access("Transactions", "edit")
						<a href="{{ url(config('laraadmin.adminRoute') . '/transactions/'.$transaction->id.'/edit') }}" class="btn btn-xs btn-edit btn-default"><i class="fa fa-pencil"></i></a><br>
					@endla_access
					
					@la_access("Transactions", "delete")
						{{ Form::open(['route' => [config('laraadmin.adminRoute') . '.transactions.destroy', $transaction->id], 'method' => 'delete', 'style'=>'display:inline']) }}
							<button class="btn btn-default btn-delete btn-xs" type="submit"><i class="fa fa-times"></i></button>
						{{ Form::close() }}
					@endla_access
				</div>
			</div>
		</div>
	</div>

	<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
		<li class=""><a href="{{ url(config('laraadmin.adminRoute') . '/transactions') }}" data-toggle="tooltip" data-placement="right" title="Back to Transactions"><i class="fa fa-chevron-left"></i></a></li>
		<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i> Thông tin chung</a></li>
	</ul>

	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
			<div class="tab-content">
				<div class="panel infolist">
					<div class="panel-default panel-heading">
						<h4>Thông tin chung</h4>
					</div>
					<div class="panel-body">
						@la_display($module, 'user_id')
						@la_display($module, 'customer_id')
						@la_display($module, 'desc')
						@la_display($module, 'trans_id')
						@la_display($module, 'order_id')
						@la_display($module, 'bank_id')
						@la_display($module, 'type')
						@la_display($module, 'received_amount')
						@la_display($module, 'transfered_amount')
						@la_display($module, 'fee')
						@la_display($module, 'bank_history')
						@la_display($module, 'note')
						@la_display($module, 'status')
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</div>
</div>
@endsection
