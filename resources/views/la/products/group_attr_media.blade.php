<div class="row">
    <input type="hidden" name="avm_id" value="{{ $avmId }}" />
    @foreach ($gallery as $image)
        <div class="col-md-3">
            <div class="border p-1 media-item @if (in_array($image['id'], $selectedMedia)) border-primary active @endif">
                <img style="width: 100%" src="{{ @$image['path'] }}">
                <input type="hidden" class="media_id" name="media_id[]" value="{{ $image['id'] }}" @if (!in_array($image['id'], $selectedMedia)) disabled @endif />
            </div>
        </div>
    @endforeach
</div>
