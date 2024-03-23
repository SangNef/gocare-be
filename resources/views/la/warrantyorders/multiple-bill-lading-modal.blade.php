<div class="modal fade" id="MultipleBillLadingModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-70" role="document">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Vận đơn</h4>
			</div>
			<div class="modal-body">
				<div class="alert alert-danger errors" style="display: none">
					<ul></ul>
				</div>
				<div class="box box-success">
					<div class="box-body dataTables_wrapper" style="min-height: 500px; max-height: 100%; overflow-y: scroll; position: relative">
						<table id="order-list" class="table table-bordered">
							<thead>
							<tr class="success">
								<th width="10%">Mã đơn hàng</th>
								<th width="20%">Kho hàng</th>
								<th width="20%">Thông tin sản phẩm</th>
								<th width="20%">Thông tin người nhận</th>
								<th width="20%">Thông tin giao hàng</th>
								<th width="10%">Trạng thái</th>
							</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
						<div id="loading-overlay" style="display: none;">
							<div id="order-list-processing" class="dataTables_processing panel panel-default">Đang tải...</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-success" id="cod-submit"Lưu/button>
				</div>
			</div>
        </div>
    </div>
</div>
@push('styles')
<style>
#loading-overlay {
	position: absolute;
	width: 100%; 
	height: 100%;
	top: 0;
	left: 0;
	right: 0;
	background: rgba(255,255,255,.8);
	z-index: 1;
}
</style>
@endpush
