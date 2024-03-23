@push('scripts')
<script>
$(function () {
	let count = $('.history-detail').length;
	$(document).on('click', '.add-history-detail', function () {
		count++;
		console.log(count);
		let html = '<div class="row history-detail">\n' +
			'<div class="col-sm-2">\n' +
			'<div class="form-group">\n' +
			'<label for="status">Ngày:</label>\n' +
			'<input class="form-control datepicker" name="histories[__index__][created_at]" value="{{ \Carbon\Carbon::today()->format('Y/m/d') }}"/>\n' +
			'</div>\n' +
			'</div>\n' +
			'<div class="col-sm-6">\n' +
			'<div class="form-group">\n' +
			'<label for="status">Chi tiết :</label>\n' +
			'<input class="form-control" name="histories[__index__][detail]" />\n' +
			'</div>\n' +
			'</div>\n' +
			'<div class="col-sm-3">\n' +
			'<div class="form-group">\n' +
			'<label for="status">Người xử lý :</label>\n' +
			'<input type="hidden" class="form-control" name="histories[__index__][handler_id]" value="{{ auth()->user()->id }}">\n' +
			'<input type="text" class="form-control" readonly value="{{ auth()->user()->name }}">\n' +
			'</div>\n' +
			'</div>\n' +
			'<div class="col-sm-1 col-xs-2">\n' +
			'<div class="form-group">\n' +
			'<button type="button" class="btn btn-success btn-xs add-history-detail"><i class="fa fa-plus"></i></button>\n' +
			'<button type="button" class="btn btn-danger btn-xs remove-history-detail" disabled><i class="fa fa-minus"></i></button>\n' +
			'</div>\n' +
			'</div>\n' +
			'</div>';
		$('.history-info').append(html.replace(/__index__/g, count));
		$('.remove-history-detail').removeAttr('disabled');
		initDatapicker();
	});

	$(document).on('click', '.remove-history-detail',function () {
		if ($('.history-detail').length > 1) {
			$(this).parents('.history-detail').remove();
		}
		if ($('.history-detail').length == 1) {
			$('.remove-history-detail').prop('disabled', true);
		}
	});
});
</script>
@endpush
