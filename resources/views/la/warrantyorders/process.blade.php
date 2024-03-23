<div class="progress">
    @foreach($processTotals as $process)
        <div class="progress-bar progress-bar-{{ $process['color'] }}" style="width: {{ $process['percent'] }}%" data-placement="bottom" data-toggle="tooltip" title="{{ $process['label'] }}">{{ $process['title'] }}</div>
    @endforeach
</div>