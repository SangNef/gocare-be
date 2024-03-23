
@extends("la.layouts.app")

@section("contentheader_title", $name)
@section("section", $name)
@section("htmlheader_title", $name)

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

@include('la.datatable.index', ['id' => 'addresses', 'path' => $datatable_path, 'cols' => $cols,
])

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
$(function () {
});
</script>
@endpush
