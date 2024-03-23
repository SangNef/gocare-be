@if (count($senders) > 0)
	@foreach($senders as $sender)
	<tr>
		<td>
			{{ $sender['SenderFullname'] }}
			@if(@$sender['SenderId'] == $defaultId)
			<span class="label label-success">Mặc định</span>
			@endif
		</td>
		<td>{{ $sender['SenderTel'] }}</td>
		<td>{{ $sender['SenderAddress'] }}</td>
		<td>{{ $sender['SenderWardName'] }}</td>
		<td>{{ $sender['SenderDistrictName'] }}</td>
		<td>{{ $sender['SenderProvinceName'] }}</td>
		<td>
			<button type="button" class="btn btn-sm btn-warning edit-sender" value="{{ json_encode($sender) }}"><i class="fa fa-pencil"></i></button>
			<button type="button" class="btn btn-sm btn-danger remove-sender" value="{{ $sender['SenderId'] }}"><i class="fa fa-trash"></i></button>
		</td>
	</tr>
	@endforeach
	<tr>
		<td colspan="7">
			{{ $senders->links() }}
		</td>
	</tr>
@else
	<tr>
		<td colspan="7" class="text-center">Không có dữ liệu</td>
	</tr>
@endif