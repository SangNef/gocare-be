<!-- REQUIRED JS SCRIPTS -->



<!-- jquery.validate + select2 -->
<script src="{{ asset('la-assets/plugins/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/select2/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/bootstrap-datetimepicker/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/datepicker/bootstrap-datepicker.js') }}" type="text/javascript"></script>

<!-- AdminLTE App -->
<script src="{{ asset('la-assets/js/app.min.js') }}" type="text/javascript"></script>

<script src="{{ asset('la-assets/plugins/stickytabs/jquery.stickytabs.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/slimScroll/jquery.slimscroll.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/js/autonumeric.js') }}" type="text/javascript"></script>
<script src="https://cdn.tiny.cloud/1/i0lgasg8h266xcnggmfjo6gzdi8qu1nrxekggsxfzxlw65gu/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    var ajax_url = '{{ route('ajax-select') }}';
    var codGetAddressUrl = '{{ route('co.get-address', ['partner' => ':partner']) }}'
</script>
<script src="{{ asset('la-assets/js/scripts.js') }}" type="text/javascript"></script>


<!-- Optionally, you can add Slimscroll and FastClick plugins.
      Both of these plugins are recommended to enhance the
      user experience. Slimscroll is required when using the
      fixed layout. -->

@stack('scripts')