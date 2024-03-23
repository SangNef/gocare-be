@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/banks') }}">Ngân hàng</a> :
@endsection
@section("contentheader_description", $bank->$view_col)
@section("section", "Ngân hàng")
@section("section_url", url(config('laraadmin.adminRoute') . '/banks'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa ngân hàng : ".$bank->$view_col)

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
				{!! Form::model($bank, ['route' => [config('laraadmin.adminRoute') . '.banks.update', $bank->id ], 'method'=>'PUT', 'id' => 'bank-edit-form']) !!}					
					@la_input($module, 'name')
					@la_input($module, 'branch')
					@la_input($module, 'acc_name')
					@la_input($module, 'acc_id')
					@la_input($module, 'printing')
					@if(auth()->user()->isChairmanUser())
					<div class="form-group">
                        <label for="first_balance">Số dư đầu*:</label>
						<input type="text" value="{{ $bank->first_balance }}" name="first_balance" id="first_balance" class="form-control currency valid" aria-invalid="false"/>
                    </div>
					<div class="form-group">
                        <label for="first_balance">Số dư cuối*:</label>
						<input type="text" value="{{ $bank->last_balance }}" name="last_balance" id="last_balance" class="form-control currency valid" aria-invalid="false"/>
                    </div>
					@endif
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/banks') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>
<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<table class="table table-bordered">
					<thead>
						<tr class="success">
							<th></th>
							<th>Chuyển đến</th>
							<th>Chuyển đi</th>
							<th>Phí</th>
						</tr>
					</thead>
					<tbody>
						@forelse($bank->backlogs as $backlog)
							<tr>
								<td>{{ \App\Models\BankBacklog::debtTypeName()[$backlog->debt_type] }}</td>
								<td>{{ number_format($backlog->money_in) . 'đ' }}</td>
								<td>{{ number_format($backlog->money_out) . 'đ' }}</td>
								<td>{{ number_format($backlog->fee) . 'đ' }}</td>
							</tr>
						@empty
							<tr><td colspan="7" class="text-center">Không có dữ liệu phù hợp</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
	
@endsection

@push('scripts')
<script>
$(function () {
	$("#bank-edit-form").validate({
		
	});
});
</script>
@endpush
