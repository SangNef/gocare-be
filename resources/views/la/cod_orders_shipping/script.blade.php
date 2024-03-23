@push('scripts')
<script>
    function getSelectedCodes()
    {
        const selectedCodes = "{{ isset($selectedCodes) ? json_encode($selectedCodes) : '[]' }}";
        return JSON.parse(selectedCodes.replace(/&quot;/g,'"'));
    }

    function getCurrentParnter()
    {
        const { partner } = getFormData();
        return partner;
    }

    function getCurrentHandle()
    {
        const { handle_type: handle } = getFormData();
        return handle;
    }

    function searchOrderIni()
    {
        const {partner, handle_type: handle} = getFormData();
        let url = `{{route('cos.search')}}?partner=${partner}`;
        let search = $('#search-order').select2({
            allowClear: true,
            placeholder: "Nhập mã vận đơn",
            cache: true,
            disabled: handle == 2 ? true : false ,
            ajax: {
                url: url,
                dataType: 'json',
                cache: true,
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 20) < data.count_filtered
                        }
                    };
                }
            }
        });
    }
    searchOrderIni();

    function addOrder(codes)
    {
        const partner = getCurrentParnter();
        const url = `{{ route('cos.get.id') }}?partner=${partner}&codes=${codes}`;
        $.ajax({
            url: url,
            success: function (data) {
                $('#selected_orders tbody').html(data);
                updateTotal();
                initNumberInput('#selected_orders input.currency');
            }
        });
    }

    function updateTotal()
    {
        var codAmount = feeAmount = 0;
        $('#selected_orders .cod_amount').each(function() { 
            codAmount += parseInt($(this).val().replace(/\D/g,''));
        });
        $('#selected_orders .fee_amount').each(function() { 
            feeAmount += parseInt($(this).val().replace(/\D/g,''));
        });
        $('#selected_orders tfoot .total_cod').text(codAmount.toLocaleString() + ' đ');
        $('#selected_orders tfoot .total_fee').text(feeAmount.toLocaleString() + ' đ');
    }

    function getFormData()
    {
        return $('#shipping-order-form').serializeObject();
    }

    var code = "";
    var scannedCodes = [...getSelectedCodes()];
    function onScanCode(e)
    {
        var charCode = e.keyCode;
        if (charCode != 13) {
            code += String.fromCharCode(charCode);
        } else {
            e.preventDefault();
            e.stopImmediatePropagation();
            const partner = getCurrentParnter();
            var completeCode = code;
            if (!isCodeScanned(completeCode) && validateCode(partner, completeCode)) {
                scannedCodes.push(completeCode);
                addOrder(scannedCodes);
            }
            code = "";
        }
    }

    function isCodeScanned(code)
    {
        return scannedCodes.includes(code);
    }

    function validateCode(partner, code)
    {
        var isValid = true;
        const url = `{{ route('cos.check-code') }}?partner=${partner}&code=${code}`;
        $.ajax({
            url: url,
            async: false,
            beforeSend: function() {
				$('.errors').hide().find('ul').empty();
            },
            error: function(xhr) {
                isValid = false;
                var errors = xhr.responseJSON;
				for (const key of Object.keys(errors)) {
					$('.errors').show().find('ul').append(`<li>${errors[key][0]}</li>`);
				}
            }
        });
        return isValid;
    }

    function attachScan()
    {
        const handle = getCurrentHandle();
        if (handle == 2) {
            document.addEventListener('keypress', onScanCode);
        } else {
            detachScan();
        }
    }
    
    function detachScan()
    {
        return document.removeEventListener('keypress', onScanCode);
    }

    function resetScannedCodes()
    {
        return scannedCodes = [];
    }

    $(function() {
        $('#AddModal').on('shown.bs.modal', function () {
            attachScan();
        }).on('hidden.bs.modal', function() {
            detachScan();
        });

        $('#search-order').change(function() {
            const codes = $(this).val();
            addOrder(codes);
        });

        $('input[name="partner"]').change(function() {
            searchOrderIni();
            $('#search-order').val(null).trigger('change');
            resetScannedCodes();
        });

        $('input[name="handle_type"]').change(function() {
            searchOrderIni();
            attachScan();
        });
        $(document).on('change', '#selected_orders .cod_amount, #selected_orders .fee_amount', () => updateTotal());
    });
</script>
@endpush
