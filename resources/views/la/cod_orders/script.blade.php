@push('scripts')
<script>
    // TODO: fetch async
    const customer = JSON.parse("{{ $customer->toJson() }}".replace(/&quot;/g,'"'));
    const getAddressParams = {
        order_id: "{{ $order->id }}",
        oClass: String.raw`{{ get_class($order) }}` 
    }

    function defaultAddressSelect(selector, data)
    {
        return new Promise(function(resolve) {
            if (data) {
                // TODO: clean accent from server like FE
                data = cleanAccents(data.toLowerCase());
                selector.filter(function() {
                    var text = cleanAccents($(this).text().toLowerCase());
                    if (data.indexOf(text) !== -1) {
                        value = $(this).val();
                        $(this).parent().val(value).change();
                        setTimeout(() => {
                            resolve();
                        }, 2000);
                    }
                })
            }
        });
    }

    function applyCustomerAddress() {
        if (!$('#viettelpost-form .partner-province, #ghn-form .partner-province, #ghn-5-form .partner-province, #ghtk_province').val()) {
            defaultAddressSelect($('#viettelpost-form .partner-province option, #ghn-form .partner-province option, #ghn-5-form .partner-province option, #ghtk_province option'), customer.province)
                .then(() => {
                    return defaultAddressSelect($('#viettelpost-form .partner-district option, #ghn-form .partner-district option, #ghn-5-form .partner-district option, #ghtk_district option'), customer.district);
                }).then(() => {
                return defaultAddressSelect($('#viettelpost-form .partner-ward option, #ghn-form .partner-ward option, #ghn-5-form .partner-ward option, #ghtk_ward option'), customer.ward);
            });
        }
    }
    
    $(function() {
        applyCustomerAddress();

        $(document).on('change', '#viettelpost-form .partner-province, #ghn-form .partner-province, #ghn-5-form .partner-province, #vnpost-form .partner-province', async function() {
            const form = $(this).parents('form');
            const partner = form.find('input[name="cod_partner"]').val();
            const provinceId = $(this).val();
            const getAddressUrl = codGetAddressUrl.replace(':partner', partner);
            form.find('select.partner-district, select.partner-ward').find('option').not('.selected').remove();
            const data = await getShippingPartnerAddress(getAddressUrl, provinceId, 'district', partner === 'ghn' ? getAddressParams : {});

            for ([id, value] of Object.entries(data)) {
                form.find('select.partner-district').append(`<option value="${id}">${value}</option>`);
            }
        });

        $(document).on('change', '#viettelpost-form .partner-district, #ghn-form .partner-district, #ghn-5-form .partner-district, #vnpost-form .partner-district', async function() {
            const form = $(this).parents('form');
            const partner = form.find('input[name="cod_partner"]').val();
            const districtId = $(this).val();
            const getAddressUrl = codGetAddressUrl.replace(':partner', partner);
            form.find('select.partner-ward').find('option').not('.selected').remove();
            const data = await getShippingPartnerAddress(getAddressUrl, districtId, 'ward', partner === 'ghn' ? getAddressParams : {});

            for ([id, value] of Object.entries(data)) {
                form.find('select.partner-ward').append(`<option value="${id}">${value}</option>`);
            }
        });

        $(document).on('submit', '#viettelpost-form, #ghn-form, #ghn-5-form, #ghtk-form, #vnpost-form', function(e) {
            if ({{ request('d', 0) }}) {
                $(this).append('<input type="hidden" name="d" value="1" />');
            }
            !checkRequiredInputs($(this).attr('id')) && e.preventDefault();
        });

        $(document).on('change', '#viettelpost-form input[name="charge_method"], #ghn-form input[name="charge_method"], #ghn-5-form input[name="charge_method"], #ghtk-form input[name="charge_method"], #vnpost-form input[name="charge_method"]', function() {
            let codAmount = "{{ isset($codAmount) ? $codAmount : 0 }}";
            if ($(this).val() == 2) {
                codAmount = 0;
            }
            $(this).parents('form').find('.cod_amount').val(codAmount.toLocaleString() + ' đ');
        });
        $(document).on('change', '.item-weight', function(){
            var form = $(this).parents('form');
            var total = 0;
            $(form).find('.item-weight').each(function() {
                total += parseInt($(this).val());
            });
            $(form).find('.total_weight').val(total).change();
        });

        $(document).on('change', '.item-length, .item-width, .item-height', function(){
            var form = $(this).parents('form');
            var size = 0;
            var result = {
                length: 0,
                height: 0,
                width: 0,
            };
            $(form).find('.item-size').each(function () {
                var length = parseInt($(this).find('.item-length').val());
                var height = parseInt($(this).find('.item-height').val());
                var width = parseInt($(this).find('.item-width').val());
                var tmp = length * height * width;
                if (size < tmp) {
                    size = tmp;
                    result = {
                        length: length,
                        height: height,
                        width: width,
                    };
                }
                if (typeof weightUnit === 'undefined') {
                    weightUnit = 6;
                }
                var weightBySize =  Math.ceil(length * height * width / weightUnit) + (weightUnit === 6 ? 'g' : 'kg');
                $(this).find('.weight-by-size').html(weightBySize);
            });
            $(form).find('.total_length').val(result.length);
            $(form).find('.total_height').val(result.height);
            $(form).find('.total-width').val(result.width);
        });
        $('.cod-form').submit(function () {
           $(this).find('.btn-success').prop('disabled', true);
        });
    });
</script>
@endpush
