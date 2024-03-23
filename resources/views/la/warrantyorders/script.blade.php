@push('scripts')
<script type="text/javascript" src="/la-assets/js/scanner.js"></script>
<script>
    const orderId = "{{ isset($orderId) ? $orderId : null }}"
    let code = "";
    let series = [...getSelectedCodes()];
    const billLadingModal = $('#bill_ladding_modal');
    let xhrPools = [];

    function abortRequest(orderId) {
        const index = xhrPools.findIndex(function (request) {
            return request.order_id == orderId;
        });
        if (index >= 0) {
            xhrPools[index].requests.map(function (jqXHR) {
                jqXHR.abort();
            })
            xhrPools.splice(index, 1);
        }
    }


    function getSelectedCodes()
    {
        let selectedSeries = "{{ isset($selectedSeries) ? json_encode($selectedSeries) : '[]' }}";
        return JSON.parse(selectedSeries.replace(/&quot;/g, '"'));
    } 

    function getFormData()
    {
        return $('#warrantyorders-add-form').serializeObject();
    }

    function isCodeScanned(code)
    {
        return series.includes(code);
    }

    function scanSeri() { 
        if (!onScan.isAttachedTo(document)) {
            onScan.attachTo(document, {
                suffixKeyCodes: [13],
                ignoreIfFocusOn: ['input', 'textarea', 'select'],
                onScan: function(sCode, iQty) {
                    let parseCode = new URL(sCode);
                    let productSeri = parseCode.pathname.substring(parseCode.pathname.lastIndexOf('/') + 1);
                    let productId = productSeri.slice(6, -5);

                    if (!isCodeScanned(productSeri)) {
                        series.push(productSeri);
                        if ($('.selected-product[data-id="'+productId+'"]').length > 0) {
                            let quantity = $('.order-selected-product[data-id="'+productId+'"]').find('.quantity');
                            let currentVal = Number(quantity.val());
                            quantity.val(currentVal + 1).trigger('change');
                        } else {
                            addProducts(productId, $(`.product-item[data-id="${productId}"]`));
                        }

                        createSeriRow(productId);
                    } else {
                        alert(`Seri ${productSeri} đã quét`);
                    }
                },
            });
        }
    }

    function createSeriRow(productId)
    {
        let url = "{{ route('orders.get.seri') }}";
        $.ajax({
            method: "GET",
            url: url,
            data: {
                product_id: productId,
                view: "la.products_selecting.warrantyorder_selected_product_seri"
            },
            success: function (data) {
                $('#selected_products_series tbody').append(data);
                orderSeriSelectionIni();
                initDatetimePicker();
            }
        });
    }

    function removeSeriRow(productId, removeAll = false)
    {
        let dom = $('#selected_products_series .selected-product-seri[data-product-id="'+productId+'"]');
        return removeAll
            ? dom.remove()
            : dom.last().remove();
    }

    function orderSeriSelectionIni()
    {
        $('.choose-seri').each(function () {

            $(this).select2({
                allowClear: true,
                placeholder: "Chọn",
                cache: true,
                ajax: {
                    url: function () {
                        let params = JSON.parse($(this).attr('params'));
                        if ($('#order_store').length > 0 && $('#order_store').val()) {
                            params['store_id'] = $('#order_store').val();
                        }
                        let url = objectToQueryString("{{ route('orders.get.list.seri') }}", params);
                        return url;
                    },
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
        });
    }
    var setStoreFromCustomer = false;
    function checkStoreForCustomer(customerId)
    {
        setStoreFromCustomer = true;
        if ($('#order_store').length > 0 && customerId) {
            @if (!auth()->user()->store_id)
            $.ajax({
                url: '{{ url(config('laraadmin.adminRoute') . '/customers') }}/' + customerId + '/store',
                dataType: 'json',
                success: function (data) {
                    if (typeof data.id !== 'undefined') {
                        var newOption = new Option(data.text, data.id, false, false);
                        $('#order_store').append(newOption).trigger('change');
                    } else {
                        $('#order_store').val(null).trigger('change');
                    }
                    setStoreFromCustomer = false;
                }
            })
            @endif
        }
    }

    function handlePrint(data, callback)
    {
        let url = objectToQueryString("{{ route('warrantyorders.print') }}", data);
        let iframe = document.createElement('iframe');
        iframe.className='pdfIframe'
        document.body.appendChild(iframe);
        iframe.style.display = 'none';
        iframe.onload = function () {
            setTimeout(function () {
                iframe.focus();
                URL.revokeObjectURL(url);
                document.body.removeChild(iframe);
                callback();
            }, 1);
        };
        iframe.src = url;
    }

    function renderBillLadingModal(type, ids = [])
    {
        const modal = $('#bill_ladding_modal');
        return $.ajax({
            type: "GET",
            url: `{{ url(config('laraadmin.adminRoute')) . '/warrantyorders/' }}${orderId}/bill-lading`, 
            data: {type, ids: ids.join(',')},
            beforeSend: function () {
                billLadingModal.find('.modal-content').empty();
                $('.alert-danger').hide().find('ul').empty();
            },
            success: function (res) {
                billLadingModal.find('.modal-content').html(res.html);
                applyCustomerAddress();
                initDatetimePicker();
                billLadingModal.find('.select2').select2();
                billLadingModal.find('form').each(function () {
                    const partner = $(this).parent().attr('id');
                    $(this).attr('action', `{{ url(config('laraadmin.adminRoute')) . '/cod-orders/create-bill-warranty/' }}${orderId}/${partner}`);
                    if (ids.length > 0) {
                        for (const id of ids) {
                            $(this).append(`<input type="hidden" name="wops_ids[]" value=${id}>`);
                        }
                    }
                    $(this).append(`<input type="hidden" name="type" value="${type}">`);
                    $(this).append(`<input type="hidden" id="warranty-order" value="1">`);
                });
                billLadingModal.modal('show');
            },
            error: function (error) {
                const messages = error.responseJSON;
                let html = '';
                Object.values(messages).forEach(function (message) {
                    html += '<li>' + message.join('<br>') + '</li>';
                });
                $('.alert-danger').show().find('ul').append(html);
            }
        });
    }

    async function getCODPrice(orderId = null)
    {
        const storage = JSON.parse(localStorage.getItem('warrantyorder-selected-orders')) || [];
        const items = orderId
            ? storage.filter(function (item) {
                    return item.order_id == orderId;
                })
            : storage;
        const data = [];
        for (let i = 0; i < items.length; ++i) {
            const item = items[i];
            data.push({
                func: function (params) {
                    return $.ajax({
                        url: `{{ url(config('laraadmin.adminRoute')) . '/warrantyorders/get-cod-services/' }}${item.cod_partner}`,
                        method: 'GET',
                        data: params,
                        beforeSend: function (jqXHR) {
                            abortRequest(item.order_id);
                            xhrPools.push({order_id: item.order_id, requests: [jqXHR]});
                            $(`#MultipleBillLadingModal .order-row[data-id="${item.order_id}"] .service_id`).empty();
                        }
                    });
                },
                arg: item
            });
        }
        const response = await Promise.allSettled(data.map(function (item) {
            return item.func(item.arg);
        }));
        const rejected = response.filter(function(item) {
            return item.status === "rejected";
        });
        const fulfilled = response.filter(function(item) {
            return item.status === "fulfilled";
        });
        if (rejected.length) {
            let errors = "";
            for (let i = 0; i < rejected.length; ++i) {
                const messages = rejected[i].reason.responseJSON;
                if (typeof messages !== 'undefined') {
                    Object.values(messages).forEach(function (message) {
                        errors += '<li>' + message.join('<br>') + '</li>';
                    });
                }
            }
            if (errors !== "") {
                $('#MultipleBillLadingModal .errors').show().find('ul').html(errors);
            }
        }
        if (fulfilled.length) {
            for (let i = 0; i < fulfilled.length; ++i) {
                const value = fulfilled[i].value;
                let options = "";
                for (id in value.services) {
                    options += `<option value="${id}">${value.services[id]['name']}</option>`;
                }
                $(`#MultipleBillLadingModal .order-row[data-id="${value.order_id}"] .service_id`).html(options).trigger('change');
            }        
        }
    }

    $(function() {
        $('#warrantyorders-add-form select[name="customer_id"]').change(function () {
            addingProductParams['customer_id'] = $(this).val();
            checkStoreForCustomer($(this).val());
        }).change();
        
        $(document).on('change', '.order-selected-product input', function() {
            let productId = $(this).closest('.selected-product').data('id');
            let value = $(this).val();
            let prevVal = $(this).attr('data-prev-val');
            let step = value - prevVal;
            let isIncrease = true;
            if (value < prevVal) {
                isIncrease = false;
                step = prevVal - value;
            }

            for (let i = 0; i < step; i++) {
                if (isIncrease) {
                    createSeriRow(productId);
                } else {
                    removeSeriRow(productId);
                }
            }

            $(this).attr('data-prev-val', value);
        });

        $(document).on('click', '.product-item', function() {
            let productId = $(this).data('id');
            createSeriRow(productId);
        });

        $(document).on('click', '.remove-selected-product', function() {
            let productId = $(this).attr('data-id');
            removeSeriRow(productId, true);
        });

        $('#order_store').change(function() {
            if (typeof setStoreFromCustomer === 'undefined' || !setStoreFromCustomer) {
                $('#order_customer').val('').change();
            }
        });

        $(document).on('change', '.seri-status', function() {
            let returnAt = $(this).parent().find('.return-at');
            if ($(this).val() == 5) {
                returnAt.show();
                returnAt.find('input.datetime-picker').attr('disabled', false); 
            } else {
                returnAt.hide();
                returnAt.find('input.datetime-picker').attr('disabled', true); 
            }
        });

        $(document).on('click', '#bill-lading-some', function () {
            const ids = getCheckedValue('.selected-product-seri .row');
            const button = $(this);
            const buttonText = 'Vận đơn sản phẩm đã chọn';
            if (ids.length == 0) {
                alert('Chọn seri');
                setTimeout(function() {
                    button.removeAttr('disabled');
                    button.html(buttonText);
                });
                return;
            }
            renderBillLadingModal("some", ids).complete(function () {
                button.removeAttr('disabled');
                button.html(buttonText);
            });
        })

        $(document).on('click', '#bill-lading-all', function () {
            const button = $(this);
            renderBillLadingModal("all").complete(function () {
                button.removeAttr('disabled');
                button.html('Vận đơn tất cả sản phẩm');
            });
        });

        $('#bill-lading-multiple').click(function () {
            const ids = getCheckedValue('.worder-id');
            if (ids.length == 0) {
                alert('Chọn đơn');
                return;
            }
            $('#MultipleBillLadingModal').modal('show');
        });

        $('#MultipleBillLadingModal').on('shown.bs.modal', async function () {
            const modal = $(this);
            const ids = getCheckedValue('.worder-id');
            const data = [];
            $('#loading-overlay').css('display', 'block');
            $('#MultipleBillLadingModal #cod-submit').attr('disabled', true);
            if (localStorage.getItem('warrantyorder-selected-orders')) {
                localStorage.removeItem('warrantyorder-selected-orders');
            }

            for (let i = 0; i < ids.length; ++i) {
                data.push({
                    func: function (id) {
                        return $.ajax({
                            url: `{{ url(config('laraadmin.adminRoute')) . '/warrantyorders/get-data-for-bill-lading/' }}${id}`,
                            method: 'GET',
                            data: {
                                partner: "vtp" // default VTP
                            }
                        });
                    },
                    arg: ids[i]
                })
            }

            const response = await Promise.allSettled(data.map(function (item) {
                return item.func(item.arg);
            }));
            $('#loading-overlay').css('display', 'none');
            $('#MultipleBillLadingModal #cod-submit').attr('disabled', false);
            const rejected = response.filter(function(item) {
                return item.status === "rejected";
            });
            const fulfilled = response.filter(function(item) {
                return item.status === "fulfilled";
            });
            if (rejected.length) {
                let errors = "";
                for (let i = 0; i < rejected.length; ++i) {
                    const messages = rejected[i].reason.responseJSON;
                    Object.values(messages).forEach(function (message) {
                        errors += '<li>' + message.join('<br>') + '</li>';
                    });
                }
                modal.find('.errors').show().find('ul').html(errors);
            }
            if (fulfilled.length) {
                const data = [];
                let html = "";
                for (let i = 0; i < fulfilled.length; ++i) {
                    const value = fulfilled[i].value;
                    data.push(value.data);
                    html += value.html;
                }
                modal.find('tbody').html(html);
                const storage = data.map(function (item) {
                    $(`#MultipleBillLadingModal .order-row[data-id="${item.order_id}"]`).find('input:not(:radio), select').each(function () {
                        const name = $(this).data('name');
                        if ($(this).is('select')) {
                            item[name] = $(this).find('option:selected').val()
                        } else {
                            item[name] = $(this).val();
                        }
                    });
                    return item;
                });
                localStorage.setItem('warrantyorder-selected-orders', JSON.stringify(storage));
                getCODPrice();
            }
        }).on('hidden.bs.modal', function () {
            $(this).find('.errors').hide().find('ul').empty();
            $(this).find('tbody').empty();
            localStorage.removeItem('warrantyorder-selected-orders');
        });
        
        $(document).on('change', '#MultipleBillLadingModal .partner', function () {
            const row = $(this).closest('tr.order-row');
            const partner = $(this).val();
            const orderId = row.data('id');
            $.ajax({
                url: `{{ url(config('laraadmin.adminRoute')) . '/warrantyorders/get-data-for-bill-lading/' }}${orderId}`,
                method: 'GET',
                data: {
                    partner: partner,
                },
                beforeSend: function (jqXHR) {
                    abortRequest(orderId);
                    xhrPools.push({order_id: orderId, requests: [jqXHR]});
                    $('#loading-overlay').css('display', 'block');
                    $('#MultipleBillLadingModal #cod-submit').attr('disabled', true);
                    row.find('input, select').attr('disabled', true);
                },
                success: function (res) {
                    row.replaceWith(res.html);
                    let newData = res.data;
                    let storage = JSON.parse(localStorage.getItem('warrantyorder-selected-orders')) || [];
                    const index = storage.findIndex(function (data) {
                        return data.order_id == orderId;
                    });
                    if (index >= 0) {
                        storage.splice(index, 1);
                    }
                    $(`#MultipleBillLadingModal .order-row[data-id="${orderId}"]`).find('input:not(:radio), select').each(function() {
                        const name = $(this).data('name');
                        if ($(this).is('select')) {
                            newData[name] = $(this).find('option:selected').val();
                        } else {
                            newData[name] = $(this).val();
                        }
                    });
                    storage.push(newData);
                    localStorage.setItem('warrantyorder-selected-orders', JSON.stringify(storage));
                    getCODPrice(orderId);
                },
                error: function (res) {
                    const messages = error.responseJSON;
                    let html = '';
                    Object.values(messages).forEach(function (message) {
                        html += '<li>' + message.join('<br>') + '</li>';
                    });
                    $('.alert-danger').show().find('ul').append(html);
                },
                complete: function (res) {
                    $('#loading-overlay').css('display', 'none');
                    $('#MultipleBillLadingModal #cod-submit').attr('disabled', false);
                }
            })
        });

        $(document).on('change', '#MultipleBillLadingModal .getprice-required', function () {
            const row = $(this).closest('tr.order-row');
            const orderId = row.data('id');
            const name = $(this).data('name');
            const storage = JSON.parse(localStorage.getItem('warrantyorder-selected-orders')) || [];
            const index = storage.findIndex(function (data) {
                return data.order_id == orderId;
            });
            if (index >= 0) {
                storage[index][name] = $(this).val();
            }
            localStorage.setItem('warrantyorder-selected-orders', JSON.stringify(storage));
            if (!$(this).hasClass('service_id')) {
                getCODPrice(orderId);
            }
        });

        $('#MultipleBillLadingModal #cod-submit').click(async function(e) {
            $('#MultipleBillLadingModal #cod-submit').attr('disabled', true);
            const storage = JSON.parse(localStorage.getItem('warrantyorder-selected-orders')) || [];
            const data = [];

            for (let i = 0; i < storage.length; ++i) {
                const item = storage[i];
                data.push({
                    func: function (params) {
                        return $.ajax({
                            url: `{{ url(config('laraadmin.adminRoute')) . '/cod-orders/create-bill-warranty/' }}${item.order_id}/${item.cod_partner}`,
                            method: 'POST',
                            data: params,
                            headers: {
                                'X-CSRF-Token': '{{ csrf_token() }}'
                            },
                            beforeSend: function (jqXHR) {
                                abortRequest(item.order_id);
                                xhrPools.push({order_id: item.order_id, requests: [jqXHR]});
                                $(`#MultipleBillLadingModal .order-row[data-id="${item.order_id}"] .cod-status`).empty().html('<i class="fa fa-spinner fa-spin"></i>');
                            }
                        });
                    },
                    arg: item
                });
            }

            const response = await Promise.allSettled(data.map(function (item) {
                return item.func(item.arg);
            }));
            $('#MultipleBillLadingModal #cod-submit').attr('disabled', false);
            const rejected = response.filter(function(item) {
                return item.status === "rejected";
            });
            const fulfilled = response.filter(function(item) {
                return item.status === "fulfilled";
            });

            if (rejected.length) {
                let errors = "";
                for (let i = 0; i < rejected.length; ++i) {
                    const rejectedOrder = rejected[i].reason.responseJSON;
                    if (typeof rejectedOrder.error !== 'undefined') {
                        errors += `<li>Đơn hàng: ${rejectedOrder.order_id} - ${rejectedOrder.error.join('<br>')}</li>`;
                    }
                    $(`#MultipleBillLadingModal .order-row[data-id="${rejectedOrder.order_id}"] .cod-status`).empty().html('<i class="fa fa-times" style="color: red"></i>');
                }
                if (errors !== "") {
                    $('#MultipleBillLadingModal .errors').show().find('ul').html(errors);
                }
            }
            if (fulfilled.length) {
                for (let i = 0; i < fulfilled.length; ++i) {
                    const value = fulfilled[i].value;
                    $(`#MultipleBillLadingModal .order-row[data-id="${value.order_id}"] .cod-status`).empty().html('<i class="fa fa-check" style="color: green"></i>');
                    $(`#MultipleBillLadingModal .order-row[data-id="${value.order_id}"]`).find('input, select').attr('disabled', true);
                    const index = storage.findIndex(function (data) {
                        return data.order_id == value.order_id;
                    });
                    if (index >= 0) {
                        storage.splice(index, 1);
                    }
                }
                localStorage.setItem('warrantyorder-selected-orders', JSON.stringify(storage));
            }
        });
    });
</script>
@endpush
