@extends("la.layouts.app")

@section("contentheader_title", "Transactionhistories")
@section("contentheader_description", "Transactionhistories listing")
@section("section", "Transactionhistories")
@section("sub_section", "Listing")
@section("htmlheader_title", "Transactionhistories Listing")

@section("headerElems")
@la_access("Transactionhistories", "create")

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

@include('la.datatable.index', ['id' => 'transactionhistories', 'path' => 'transactionhistory_dt_ajax', 'cols' => [
    [
        'title' => 'ID',
        'field' => 'id',
    ],
    [
        'title' => 'Khách hàng',
        'field' => 'customer_id',
    ],
    [
        'title' => 'Tên khách hàng',
        'field' => 'customer_name',
    ],
    [
        'title' => 'Giao dịch',
        'field' => 'transaction_id',
    ],
    [
        'title' => 'Đơn hàng',
        'field' => 'order_id',
    ],
    [
        'title' => 'Số tiền',
        'field' => 'amount',
    ],
    [
        'title' => 'Số dư',
        'field' => 'balance',
    ],
    [
        'title' => 'Ngày tạo',
        'field' => 'created_at',
    ]
]])

@endsection

@push('scripts')
<script>
$(function () {
	$("#transactionhistory-add-form").validate({
		
	});
});
</script>
@endpush
