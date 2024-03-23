@push('scripts')
    <script type="text/javascript" src="/la-assets/js/scanner.js"></script>
    <script>
        var series = [];
        var selectedSeries = "{{ isset($selectedSeries) ? json_encode($selectedSeries) : '[]' }}";
        selectedSeries = JSON.parse(selectedSeries.replace(/&quot;/g, '"'));
        series = [...series,...selectedSeries];
        var groupDiscount = {};

        function addSeriToOrder(seri)
        {
            $('input[name="order_series_type"]').prop('checked', false);
            $('input[name="order_series_type"]').map((index3, input) => {
                if ($(input).val() == 1) {
                    $(input).prop('checked', true);
                }
            });
            if (!series.includes(seri)) {
                var url = '{{ route("products.seri.get") }}?seri=' + seri;
                $.ajax({
                    url: url,
                    dataType: "JSON",
                    success: function (data) {
                        var productId = data.product_id;
                        var exists = false;
                        var attr_ids  = data.attr_ids;
                        var productSeries = [];
                        var productIndex = '';
                        productSeries.push(seri);
                        $('.order-selected-product[data-id="' + productId + '"]').map((index, el) => {
                            var selected = [];
                            $(el).find('select.product_attribute').map((index2, el2) => {
                                selected.push($(el2).val());
                            })
                            if (selected.join(',') == attr_ids) {
                                var nQuantity = $(el).find('.n_quantity');
                                nQuantity.val(Number(nQuantity.val()) + 1).change();
                                exists = true;
                                productIndex = $(el).attr('data-index');
                            }

                        });
                        if (!exists) {
                            addProducts(productId, $(`.product-item[data-id="${productId}"]`), [], attr_ids, productSeries);
                        } else {
                            // var option = new Option(seri, data.seri_id);
                            // $('.selected-product-seri[data-index="'+ productIndex +'"] select.choose-seri').append(option);
                            // var current = $('.selected-product-seri[data-index="'+ productIndex +'"] select.choose-seri').val();
                            // var value = current ? [...current,...[data.seri_id]] : [data.seri_id];
                            // $('.selected-product-seri[data-index="'+ productIndex +'"] select.choose-seri').val(value);
                            // $('.selected-product-seri[data-index="'+ productIndex +'"] select.choose-seri').trigger('change');
                        }
                        series.push(seri);
                    }
                });
            } else {
                console.log(`Seri ${seri} đã quét`);
            } 
        }

        function updateOrderForm()
        {
            var combos = 0;
            if ($('.order-selected-product.has-combo').length > 0)
            {
                $('.order-selected-product.has-combo').each(function () {
                    var quantity = parseInt($(this).find('input.n_quantity').val());
                    var comboDiscount = parseInt($(this).find('input.combo_discount').val());
                    combos += quantity*comboDiscount;
                });
            }
            $('#order_combo_discount_total input').val(combos.toLocaleString() + ' đ');
            if (combos > 0) {
                $('#order_combo_discount_total').show();
            } else {
                $('#order_combo_discount_total').hide();
            }
            var products = $('#order-add-form').serializeObject();
            var numberOfProductsByCate = {};
            var numberOfProducts = 0;
            var subtotal = 0;
            var quantityCol = parseInt(products.sub_type) == 2 ? 'w_quantity' : 'n_quantity';
            if (typeof products['products'] !== 'undefined') {
                Object.values(products.products).forEach(function (product) {
                    var quantity = convertNumberInputValue(product[quantityCol]);
                    numberOfProducts += quantity;
                    var productTotalPrice = (convertNumberInputValue(product['price']) * quantity);
                    productTotalPrice *= ((100 - convertNumberInputValue(product['discount_percent'])) / 100);
                    subtotal += productTotalPrice;
                    if (product?.cate_id) {
                        if (typeof numberOfProductsByCate[product['cate_id']] === 'undefined') {
                            numberOfProductsByCate[product['cate_id']] = {
                                'quantity': 0,
                                'amount': 0,
                                'discount': 0,
                                'discount_1': 0,
                                'total_discount' : 0,
                            };
                        }
                        numberOfProductsByCate[product['cate_id']]['quantity'] += quantity;
                        numberOfProductsByCate[product['cate_id']]['amount'] += productTotalPrice;
                    }
                });
            }
            var cateIds = Object.keys(numberOfProductsByCate);
            for (var index in Object.values(numberOfProductsByCate)) {
                var cateId = cateIds[index];
                var quantity = numberOfProductsByCate[cateId]['quantity'];
                if (typeof groupDiscount[cateId] !== 'undefined') {
                    for (var index2 in Object.values(groupDiscount[cateId])) {
                        var quantityKey = Object.keys(groupDiscount[cateId]);
                        var gdiscount = groupDiscount[cateId][quantityKey[index2]];

                        if (quantity >= gdiscount['quantity']) {
                            numberOfProductsByCate[cateId]['total_discount'] = numberOfProductsByCate[cateId]['amount'] * parseInt(gdiscount['discount_1']) / 100 + gdiscount['discount'];
                            numberOfProductsByCate[cateId]['discount'] = gdiscount['discount'];
                            numberOfProductsByCate[cateId]['discount_1'] = gdiscount['discount_1'];
                        }
                    }
                }
            }
            var cateDiscount = 0;
            for (var kkk in Object.values(numberOfProductsByCate)) {
                var cateId = cateIds[kkk];
                cateDiscount += numberOfProductsByCate[cateId]['total_discount'];
                var cate = numberOfProductsByCate[cateId];
                var td = $('#cate_' + cateId);
                td.find("td.cate_quantity").html(cate?.quantity.toLocaleString());
                td.find("td.cate_total").html(cate?.amount.toLocaleString() + ' đ');
                td.find("td.cate_discount").html(cate?.discount.toLocaleString() + ' đ');
                td.find("td.cate_discount_1").html(cate?.discount_1.toLocaleString() + ' %');
                td.find("td.cate_total_discount").html(cate?.total_discount.toLocaleString());
            }
            $('#order_group_total input').val(cateDiscount.toLocaleString() + 'đ')
            $('#order_subtotal').val(subtotal.toLocaleString() + 'đ');
            var fee = $('#order_fee').val();
            var discount = $('#order_discount').val();
            var discountPercent = $('#order_discount_percent').val();
            subtotal *= ((100 - discountPercent) / 100);
            var total = subtotal + convertNumberInputValue(fee) - convertNumberInputValue(discount);
            if ($('input[name="fee_bearer"]:checked').val() == 2) {
                total = subtotal - convertNumberInputValue(fee) - convertNumberInputValue(discount);
            }
            if ($('#modify_total_price').val()) {
                var modifyTotalPrice = $('#modify_total_price').val();
                total += convertNumberInputValue(modifyTotalPrice);
            }
            $('#order_total').val(total.toLocaleString() + ' đ');
            if (products.payment_method == 1) {
                total = 0;
            }

            $('#cod_amount').val(total.toLocaleString() + ' đ');
            $('#automated-bank').trigger('change');
        }
        function updatePaidAmount()
        {
            var amount = 0;
            $('.paid-amount').each(function () {
                amount += convertNumberInputValue($(this).val());
            });

            $('.total-paid-amount').val(amount.toLocaleString() + ' đ');
        }
        function updateSeriesForm()
        {
            let orderData = $('#order-add-form').serializeObject();
            let seriType = orderData.order_series_type;
            let type = orderData.type;
            let subType = orderData.sub_type;
            if (seriType == 2) {
                $('#selected_products_series .choose-seri').val(null).attr('disabled', true).trigger('change');
            } else {
                $('#selected_products_series .choose-seri').attr('disabled', false);
            }
            if (typeof orderData['products'] !== 'undefined') {
                Object.values(orderData.products).forEach(function (product) {
                    if (product['has_series'] == 0) {
                        $(`.selected-product-seri[data-product-id="${product['product_id']}"] .choose-seri`).val(null).attr('disabled', true).trigger('change');
                    }       
                });
            }
            
            if (type == 1) {
                $('input[name="order_series_type"][value="2"]').attr('disabled', false);
            } else {
                $('input[name="order_series_type"][value="2"]').attr('disabled', true);
            }
            orderSeriSelectionIni(subType, type, seriType);
        }
        function removeSeriForm(index)
        {
            var seriDom = $('#selected_products_series .selected-product-seri[data-index="'+index+'"]');
            if (seriDom.length > 0) {
                seriDom.remove();
            }
        }
        function orderSeriSelectionIni(orderSubType, orderType, orderSeriType)
        {
        }
        function productSelectedSeri(productId, seri = [], attr_ids = [], index = '')
        {
            var url = `{{ route('orders.get.seri') }}?product_id=${productId}&attr_ids=${attr_ids.join(',')}&index=` + index;
            if (seri.length > 0) url += `&seri=${seri.join(',')}`;
            $.ajax({
                url: url,
                async: false,
                success: function (data) {
                    if ($(`.selected-product-seri[data-product-id=${productId}]`).length === 0) {
                        $('#selected_products_series tbody').append(data);
                    } else {
                        $(`.selected-product-seri[data-product-id=${productId}]`).replaceWith(data);
                    }
                    updateSeriesForm();
                }
            });
        }
        function scanSeri() { 
            if (!onScan.isAttachedTo(document)) {
                onScan.attachTo(document, {
                    suffixKeyCodes: [13],
                    ignoreIfFocusOn: ['input', 'textarea', 'select'],
                    onScan: function(sCode, iQty) {
                        var parseCode = new URL(sCode);
                        var productSeri = parseCode.pathname.substring(parseCode.pathname.lastIndexOf('/') + 1);
                        addSeriToOrder(productSeri);
                    },
                });
            }
        }
        function fetchDraftOrder(draftId, isRemove = false)
        {
            var url = `{{ url(config('laraadmin.adminRoute')) . '/orders/fetch-draft-order/' }}${draftId}`;
            $.ajax({
                url: url,
                beforeSend: function() {
                    $('#order-add-form .errors').hide().find('ul').empty();
                    $('#draft_order_input small').show();
                    $('#load-draft-order').attr('disabled', true);
                },
                success: function (data) {
                    data.map(function(item) {
                        var selectedProduct = $('.selected-product[data-id="'+item.product_id+'"]');
                        var nQuantity = $('.order-selected-product[data-id="'+item.product_id+'"] .n_quantity');
                        if (!isRemove) {
                            if (!series.includes(item.product_seri)) {
                                series.push(item.product_seri);
                                if (selectedProduct.length > 0) {
                                    nQuantity.val(Number(nQuantity.val()) + 1).change();
                                } else {
                                    addProducts(item.product_id, $(`.product-item[data-id="${item.product_id}"]`));
                                }
                                productSelectedSeri(item.product_id, series);
                            }
                        } else {
                            var index = series.indexOf(item.product_seri);
                            if (index != -1) {
                                series.splice(index, 1);
                                if (nQuantity.val() > 1) {
                                    nQuantity.val(Number(nQuantity.val()) - 1).change();
                                    productSelectedSeri(item.product_id, series);
                                } else {
                                    selectedProduct.find('.remove-selected-product').click();
                                }
                            }
                        }
                    });
                },
                error: function (res) {
                    series = [];
                    $('#selected_products').empty();
                    $('#order-add-form .errors').show().find('ul').append(`<li>${res}</li>`);
                },
                complete: function() {
                    $('#draft_order_input small').hide();
                    if (!isRemove) {
                        $('#load-draft-order').attr('disabled', false);
                    }
                }
            });
        }
        function switchQuantityInput()
        {
            let orderData = $('#order-add-form').serializeObject();
            if (orderData.sub_type == 2) {
                $('.w_quantity').show();
                $('.n_quantity').val(0).hide();
                // $('.price-saving').hide();
            } else {
                $('.w_quantity').val(0).hide();
                $('.n_quantity').show();
                // $('.price-saving').show();
            }
        }
        function productSelectedTransport(productId, attrs = [], index = '')
        {
            let url = '{{ route('products.get.id') }}?product_id=' + productId
                + '&attr_ids=' + attrs.join(',')
                + '&index=' + index;
            $.ajax({
                url: url,
                data: {
                    view: 'la.products_selecting.order_selected_product_transport'
                },
                success: function (data) {
                    $('#tops_table tbody').append(data);
                    $(`.selected-product-transport[data-product-id=${productId}] .currency`).each(function(i, element) {
                        new AutoNumeric(element, {
                            currencySymbol: ' đ',
                            currencySymbolPlacement: 's',
                            allowDecimalPadding: false
                        })
                    });
                    calculateTransport();
                }
            });
        }
        function removeSelectedProductTransport(index)
        {
            let dom = $('#tops_table .selected-product-transport[data-index="'+index+'"]');
            if (dom.length > 0) {
                dom.remove();
            }
        }
        function calculateTransport()
        {
            let orderData = $('#order-add-form').serializeObject();
            let { unit } = orderData.transport;
            let total = 0, totalPackages = 0, totalQuantity = 0, totalCubicMeter = 0, totalKilo = 0;
            if (typeof orderData.transport.products !== 'undefined') {
                if (typeof orderData.transport.products !== 'undefined') {
                    Object.values(orderData.transport.products).map(function(product, id) {
                        index = Object.keys(orderData.transport.products)[id];
                        let length = convertNumberInputValue(product['length']),
                            packages = convertNumberInputValue(product['packages']),
                            width = convertNumberInputValue(product['width']),
                            height = convertNumberInputValue(product['height']),
                            weight = convertNumberInputValue(product['weight']),
                            quantity = convertNumberInputValue(product['quantity']),
                            price = convertNumberInputValue(product['price']),
                            productTotalCubicMetre = ((length * width * height) * packages).round(3)
                        productTotalWeight = (weight * packages).round(3);
                        switch (unit) {
                            case "khối":
                                productTotal = productTotalCubicMetre * price;
                                break;
                            case "kg":
                                productTotal = productTotalWeight * price;
                                break;
                            case "cái":
                                productTotal = packages * price;
                                break;
                            default:
                                productTotal = 0;
                        }
                        $(`.selected-product-transport[data-index=${index}] .total_block`).val(productTotalCubicMetre);
                        $(`.selected-product-transport[data-index=${index}] .total_weight`).val(productTotalWeight);
                        AutoNumeric.set($(`.selected-product-transport[data-index=${index}] .total`).get(0), productTotal);
                        total += productTotal;
                        totalPackages += packages;
                        totalQuantity += quantity;
                        totalCubicMeter += productTotalCubicMetre;
                        totalKilo += productTotalWeight;
                    });
                }
            }
            $('#tops_table tfoot .total-quantity').text(totalQuantity);
            $('#tops_table tfoot .total-packages').text(totalPackages);
            $('#tops_table tfoot .total-cubicmeter').text(totalCubicMeter.round(3));
            $('#tops_table tfoot .total-kilo').text(totalKilo.round(3));
            AutoNumeric.set($('#tops_table tfoot input[name="transport[total]"]').get(0), total);
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
                            $('.order-banks').val('').change();
                        } else {
                            $('#order_store').val(null).trigger('change');
                        }
                        setStoreFromCustomer = false;
                    }
                })
                @endif
            }
        }

        function setGroupDiscount(customerId)
        {
            if (customerId) {
                $.ajax({
                    url: '{{ url(config('laraadmin.adminRoute') . '/customers') }}/' + customerId + '/group-discount',
                    dataType: 'json',
                    success: function (data) {
                        groupDiscount = data;
                        updateOrderForm()
                    }
                })
            }
        }

        function loadSelectedProductIndex() {
            $('#selected_products .order-selected-product').each(function (index) {
                $(this).find('.index').empty().append(`${++index}. `); 
            });
        }

        var selectingSeriTable;
        var selectedSeriTable;

        $(function () {
            var count = $('.payment-detail').length + 1;
            $('#order-add-form select[name="customer_id"]').change(function () {
                addingProductParams['customer_id'] = $(this).val();
                checkStoreForCustomer($(this).val());
                setGroupDiscount($(this).val());
            }).change();
            $('#selected_products').on('selecting_product.changed', function () {
                updateOrderForm();
                switchQuantityInput();
            });
            $('#order_fee, #order_discount, #order_discount_percent, #ship_amount, #modify_total_price').change(function () {
                updateOrderForm();
            });
            $('#order_customer').change(function () {
                $('#selected_products').trigger('selected_products.reload');
                loadProduct();
            });
            $(document).on('click', '.add-payment-detail', function () {
                count++;
                var html = '<div class="row payment-detail">\n' +
                    '<div class="col-sm-3 col-xs-6">\n' +
                    '<div class="form-group">\n' +
                    '<label for="status">Ngân hàng :</label>\n' +
                    '<select class="form-control ajax-select order-banks" model="banks" name="payment[__index__][bank_id]">\n' +
                    '</select>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '<div class="col-sm-3 col-xs-6">\n' +
                    '<div class="form-group">\n' +
                    '<label for="status">Mã giao dịch :</label>\n' +
                    '<input class="form-control" name="payment[__index__][code]" value=""/>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '<div class="col-sm-2 col-xs-4">\n' +
                    '<div class="form-group">\n' +
                    '<label for="status">Số tiền :</label>\n' +
                    '<input class="form-control paid-amount currency" name="payment[__index__][amount]" value="0 đ"/>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '<div class="col-sm-2 col-xs-3">\n' +
                    '<div class="form-group">\n' +
                    '<label for="status">Phí giao dịch :</label>\n' +
                    '<input class="form-control currency" name="payment[__index__][fee]" value="0"/>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '<div class="col-sm-1 col-xs-3 p0">\n' +
                    '<div class="form-group">\n' +
                    '<label for="status">Ngày GD:</label>\n' +
                    '<input class="form-control datepicker" name="payment[__index__][paid_date]" value="{{ \Carbon\Carbon::today()->format('Y/m/d') }}"/>\n' +
                    '</div>\n' +
                    '</div>' +
                    '<div class="col-sm-1 col-xs-2">\n' +
                    '<div class="form-group">\n' +
                    '<label for="status">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>\n' +
                    '<button type="button" class="btn btn-success btn-xs add-payment-detail"><i class="fa fa-plus"></i></button>\n' +
                    '<button type="button" class="btn btn-danger btn-xs remove-payment-detail" disabled><i class="fa fa-minus"></i></button>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '</div>';
                $('.payment-info').append(html.replace(/__index__/g, count));
                $('.remove-payment-detail').removeAttr('disabled');
                initAjaxSelect();
                initDatapicker();
            });
            $(document).on('click', '.remove-payment-detail', function () {
                if ($('.payment-detail').length > 1) {
                    $(this).parents('.payment-detail').remove();
                }
                if ($('.payment-detail').length == 1) {
                    $('.remove-payment-detail').prop('disabled', true);
                }
            });
            $(document).on('change', '.paid-amount', function () {
                updatePaidAmount();
            });
            $('#add-user-button').click(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: '{{ route('customer.create-user-from-order') }}',
                    data: {
                        username: $('#add-new-user input[name="customer_username"]').val(),
                        name: $('#add-new-user input[name="customer_name"]').val(),
                        email: $('#add-new-user input[name="customer_email"]').val(),
                        group_id: $('#add-new-user select[name="group_id"]').val(),
                        debt_in_advance: $('#add-new-user input[name="debt_in_advance"]').val(),
                        phone: $('#add-new-user input[name="customer_phone"]').val(),
                        address: $('#add-new-user input[name="customer_address"]').val(),
                        province: $('#add-new-user select[name="customer_province"]').val(),
                        district: $('#add-new-user select[name="customer_district"]').val(),
                        ward: $('#add-new-user select[name="customer_ward"]').val(),
                    },
                    dataType: 'JSON',
                    beforeSend: function() {
                        $('#customer-name-error').empty();
                        $('#customer-email-error').empty();
                        $('#group-id-error').empty();
                        $('#customer-phone-error').empty();
                    },
                    success: function(res) {
                        var newOption = new Option(res.name, res.id, true, true);
                        $('#order_customer').append(newOption).trigger('change');
                        $('#add-new-user').collapse('hide');
                    },
                    error: function(xhr) {
                        var res = xhr.responseJSON;
                        if (res.username) {
                            $('#customer-username-error').append(res.username[0]);
                        }
                        if (res.name) {
                            $('#customer-name-error').append(res.name[0]);
                        }
                        if (res.group_id) {
                            $('#group-id-error').append(res.group_id[0]);
                        }
                        if (res.email) {
                            $('#customer-email-error').append(res.email[0]);
                        }
                        if (res.phone) {
                            $('#customer-phone-error').append(res.phone[0]);
                        }
                    }
                })
            });

            $('#selected_products').on('selecting_product.added', function (event, data) {
                var item = $('.order-selected-product').last();
                var index = item.find('.item-key').val();
                var productId = item.find('.item-product-id').val();
                var attrs = [];
                item.find('.product_attribute').each(function (v) {
                    attrs.push($(this).val());
                });
                loadSelectedProductIndex();
                productSelectedSeri(productId, data, attrs, index);
                productSelectedTransport(productId, attrs, index);
            });
            $(document).on('change', '.order-selected-product .product_attribute', function () {
                var productEl = $(this).parents('.order-selected-product');
                var index = productEl.find('.item-key').val();
                var productId = productEl.find('.item-product-id').val();
                var seriEls = $('.selected-product-seri').filter(function (item) {
                    return $(this).attr('data-index') == index;
                });
                var attrs = [];
                productEl.find('.product_attribute').each(function (v) {
                    attrs.push($(this).val());
                });
                if (seriEls.length > 0) {
                    $(seriEls[0]).find('.attribute-value').each(function () {
                        $(this).addClass('hidden');
                        if (attrs.indexOf($(this).attr('data-id')) !== -1) {
                            $(this).removeClass('hidden');
                        }
                    });
                    var seri = $(seriEls[0]).find('.choose-seri')[0];
                    if (seri) {
                        $(seri).val('').change();
                        var params = JSON.stringify({"product_id": productId, "attr_ids": attrs});
                        $(seri).val(params);
                    }
                }

                var transportEls = $('.selected-product-transport').filter(function (item) {
                    return $(this).attr('data-index') == index;
                });
                if (transportEls.length > 0) {
                    $(transportEls[0]).find('.attribute-value').each(function () {
                        $(this).addClass('hidden');
                        if (attrs.indexOf($(this).attr('data-id')) !== -1) {
                            $(this).removeClass('hidden');
                        }
                    });
                }
            });
            $(document).on('click', '.remove-selected-product', function () {
                let index = $(this).attr('data-index');
                removeSeriForm(index);
                removeSelectedProductTransport(index);
                calculateTransport();
            });
            $('input[name="type"], input[name="sub_type"], input[name="order_series_type"]').change(function() {
                updateSeriesForm();
            });
            $(document).on('change', '.order-selected-product input', function () {
                var parent = $(this).parents('.order-selected-product');
                var productId = parent.data('id');
                var nQuantity = convertNumberInputValue(parent.find('.n_quantity').val());
                var wQuantity = convertNumberInputValue(parent.find('.w_quantity').val());
                var lastestPrice = convertNumberInputValue(parent.find('.lastest_price').val());
                var discountPercent = convertNumberInputValue(parent.find('.discount_percent').val());
                var quantity = nQuantity + wQuantity;
                var total = lastestPrice * (quantity);
                total *= ((100 - discountPercent) / 100);
                var savePrice = parent.find('.price-saving > input[type="checkbox"]');

                parent.find('.order-product-total').val(total.toLocaleString() + ' đ');
                $(`.selected-product-transport[data-product-id=${productId}] .quantity, .selected-product-transport[data-product-id=${productId}] .packages`).val(quantity).change();
                $('#selected_products').trigger('selecting_product.changed', {target: parent});
            });
            var timer = null;
            $('#draft_order').on('select2:unselecting select2:selecting', function(e) {
                const draftOrderId = e.target.value;
                if (draftOrderId && timer) {
                    fetchDraftOrder(e.target.value, true);
                    clearInterval(timer);
                }
            }).change(function(e) {
                const draftOrderId = e.target.value;
                if (draftOrderId) {
                    fetchDraftOrder(draftOrderId);
                }
            }).on('select2:select', function(e) {
                const draftOrderId = e.target.value;
                if (draftOrderId) {
                    timer = setInterval(function() {
                        fetchDraftOrder(draftOrderId);
                    }, 60000);
                }
            });
            $('#load-draft-order').on('click', function () {
                const draftOrderId = $('#draft_order').val();
                if (draftOrderId) {
                    fetchDraftOrder(draftOrderId);
                }
            })
            $(document).on('clear-draft-order-interval', function() {
                clearInterval(timer);
            });
            $('#tops_table').on('change', '.selected-product-transport input', function() {
                calculateTransport();
            });
            $('#transport_unit').change(function() {
                calculateTransport();
            });
            $('#automated-bank').change(function() {
                var value = $(this).val(),
                    name = $(this).find('option:selected').text(),
                    pay = 0,
                    code = '',
                    date = new Date();
                if (value) {
                    var newOption = new Option(name, value, true, true);
                    time = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true, second: '2-digit' }).replace("AM","").replace("PM","");
                    code = [date.getFullYear(), date.getMonth(), date.getDate(), time].join('-');
                    pay = $('#order_total').val();
                    $('#tab-payment .payment-detail .bank_id:first').append(newOption).trigger("change");
                } else {
                    $('#tab-payment .payment-detail .bank_id:first').val(value).trigger("change");
                }
                $('#tab-payment .payment-detail .code:first').val(code);
                var paymentInput = $('#tab-payment .payment-detail .paid-amount:first');
                AutoNumeric.set(paymentInput.get(0), pay);
                paymentInput.trigger('change');

            });
            $('#print-transport').click(function() {
                const orderData = $('#order-add-form').serializeObject();
                const { store_id, transport, customer_id } = orderData;
                const button = $(this);
                let products = typeof transport.products !== 'undefined' 
                    ? Object.values(transport.products).filter(function() { return true })
                    : [];
                let iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.id = 'pdfIframe';
                document.body.appendChild(iframe);
                iframe = iframe.contentWindow || ( iframe.contentDocument.document || iframe.contentDocument);
                $.ajax({
                    type: "POST",
                    url: "{{ route('order.print-transport') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        store_id: store_id,
                        total: transport.total,
                        transport_price: transport.transport_price,
                        unit: transport.unit,
                        partner_id: transport.customer_id,
                        receiver_id: customer_id,
                        products: products
                    },
                    beforeSend: function() {
                        $('#tab-transport .error').empty().hide();
                    },
                    success: function(res) {
                        iframe.document.open();
                        iframe.document.write(res.html);
                        iframe.document.close();
                        iframe.onload = function () {
                            let node = document.getElementById('pdfIframe');
                            document.body.removeChild(node)
                        };
                    },
                    error: function(res) {
                        let errors = res.responseJSON;
                        let messages = Object.keys(errors)
                            .map(function(key) {
                                return `<li>${errors[key]}</li>`;
                            });
                        $('#tab-transport .error').append(messages).show();
                    },
                    complete: function() {
                        button.removeAttr('disabled');
                        button.html('In vận chuyển');
                    }
                });
            });
            $(document).on('change', '.order-selected-product .price-saving input[type="checkbox"]', function (e) {
                let self = $(this);
                let productId = self.parents('.order-selected-product').data('id');
                let {customer_id} = $('#order-add-form').serializeObject();
                if (self.data('default-checked')) {
                    this.checked = !this.checked ? !confirm('Bỏ lưu sẽ xoá giá của sản phẩm này cho khách hàng này, tiếp tục?') : true;
                    if (!this.checked) {
                        $(this).data('default-checked', false);
                        $.ajax({
                            method: "GET",
                            url: "{{ route('products.delete.savedprice') }}",
                            data: {
                                product_id: productId,
                                customer_id: customer_id
                            },
                            beforeSend: function() {
                                $('.alert-danger').hide().find('ul').empty();
                            },
                            error: function(res) {
                                let errors = res.responseJSON;
                                let messages = Object.keys(errors)
                                    .map(function(key) {
                                        return `<li>${errors[key]}</li>`;
                                    });
                                $('.alert-danger').show().find('ul').append(messages);
                            },
                            complete: function() {
                                $('#selected_products').trigger('selected_products.reload');
                                $('#selected_products').trigger('selecting_product.changed', {target: parent});
                            },
                        })
                    }
                }                    
            });
            $(document).on('change', '.product_attrs select', function () {
                var container = $(this).parents('.product_attrs');
                var text = [];
                container.find('select').map(function () {
                    var attrName = $(this).attr('attr_name');
                    var attrValue = $(this).find("option:selected").text();
                    text.push(attrName + ':' + attrValue)
                })
                container.find('.product_attrs_text').html(text.join(', '));
            })

            $('.product_attribute').change();
            $(document).on('click', '.edit-product-attrs', function (event) {
                var container = $(this).parents('.product_attrs');
                $('#ProductAttrSelecting #data-index').val($(this).attr('data-index'))
                var hidden = container.find('.product_attrs_value');
                var body = $('#ProductAttrSelecting .modal-body');
                body.html(hidden.html());
                hidden.find('select').map(function () {
                    var name = $(this).attr('attr_name')
                    body.find('select[attr_name="'+ name +'"]').val($(this).val())
                })
                $('#ProductAttrSelecting').modal('show')
            })
            $('#ProductAttrSelecting .btn-success').click(function () {
                var selected = $('.order-selected-product[data-index="'+ $('#ProductAttrSelecting #data-index').val() +'"] .product_attrs_value');
                var body = $('#ProductAttrSelecting .modal-body');
                body.find('select').map(function () {
                    var name = $(this).attr('attr_name')
                    selected.find('select[attr_name="'+ name +'"]').val($(this).val())
                })
                selected.find('select').change();
                $('#ProductAttrSelecting').modal('hide')
            })


            $('#AddSeri').on('shown.bs.modal', function (e) {
                var button = e.relatedTarget;
                var params = $(button).val();
                var selectedSeris = $(button).parent().find('textarea.selected-series').html();
                console.log("selectedSeris:", selectedSeris);
                $('#AddSeri input[name="seri_selecting_params"]').val(params);
                $('#AddSeri input[name="data-index"]').val($(button).attr('data-index'));
                $('#AddSeri textarea.seri_selected').html(selectedSeris);
                selectingSeriTable && selectingSeriTable.draw();
                selectedSeriTable && selectedSeriTable.draw();
            });


            selectingSeriTable = $("#seri-selecting").DataTable({
                destroy: true,
                pageLength: 25,
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: '{{ route('orders.get.list.seri') }}',
                    beforeSend: function (jqXHR, settings) {
                        $('#AddSeri .select-seri-button').prop('disabled', true);
                        // abortAll();
                        // xhrPool.push(jqXHR);
                        let rawParams = $('#AddSeri input[name="seri_selecting_params"]').val();
                        let params = rawParams ? JSON.parse(rawParams) : {};

                        params = {...params,...{
                            view: 'datatable',
                            excludes: $('#AddSeri textarea.seri_selected').html(),
                            qr_code_status: 1,
                            status: 0,
                            type: 2,
                            sub_type: 1,
                            stock_status: 0
                        }};
                        if ($('#order_store').length > 0 && $('#order_store').val()) {
                            params['store_id'] = $('#order_store').val();
                        }

                        settings.url = settings.url + '&' + $.param(params);
                    },
                    complete: function (data) {
                        $('#AddSeri .select-seri-button').prop('disabled', false);
                        if (typeof data.responseJSON !== 'undefined'
                            && typeof data.responseJSON.total !== 'undefined') {
                            for (id in data.responseJSON.total) {
                                $('#' + id).html(data.responseJSON.total[id]);
                            }
                        }
                    },
                },
                language: {
                    lengthMenu: "_MENU_",
                    search: "_INPUT_",
                    searchPlaceholder: "Search"
                },
                columnDefs: [ { orderable: false }],
            });
            selectedSeriTable = $("#seri-selected").DataTable({
                destroy: true,
                pageLength: 25,
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: '{{ route('orders.get.list.seri') }}',
                    beforeSend: function (jqXHR, settings) {
                        $('#AddSeri .unselect-seri-button').prop('disabled', true);
                         
                        $('#AddSeri .selected-seri').prop('disabled', true);
                        // abortAll();
                        // xhrPool.push(jqXHR);
                        let rawParams = $('#AddSeri input[name="seri_selecting_params"]').val();
                        let params = rawParams ? JSON.parse(rawParams) : {};

                        params = {...params,...{view: 'datatable',includes: $('#AddSeri textarea.seri_selected').html()}};
                        console.log(params.includes)
                        settings.url = settings.url + '&' + $.param(params);
                    },
                    complete: function (data) {
                        $('#AddSeri .unselect-seri-button').prop('disabled', false);
                        $('#AddSeri .selected-seri').prop('disabled', false);
                        if (typeof data.responseJSON !== 'undefined'
                            && typeof data.responseJSON.total !== 'undefined') {
                            for (id in data.responseJSON.total) {
                                $('#' + id).html(data.responseJSON.total[id]);
                            }
                        }
                    },
                },
                language: {
                    lengthMenu: "_MENU_",
                    search: "_INPUT_",
                    searchPlaceholder: "Search"
                },
                columnDefs: [ { orderable: false }],
            });
            $('#AddSeri .selecting-all').click(function () {
                if ($(this).prop('checked')) {
                    $('#AddSeri .selecting input[type="checkbox"]').prop('checked', true);
                } else {
                    $('#AddSeri .selecting input[type="checkbox"]').prop('checked', false);
                }
             })
            $('#AddSeri .selected-all').click(function () {
                if ($(this).prop('checked')) {
                    $('#AddSeri .selected input[type="checkbox"]').prop('checked', true);
                } else {
                    $('#AddSeri .selected input[type="checkbox"]').prop('checked', false);
                }
            })
            $('#AddSeri .select-seri-button').click(function () {
                var selected = $('#AddSeri textarea.seri_selected').html().split(',');
                $('#AddSeri .selecting input[type="checkbox"]').each(function () {
                    if ($(this).prop('checked') && $(this).val() && $(this).val() !== 'on') {
                        selected.push($(this).val());
                    }
                })
                $('#AddSeri textarea.seri_selected').html(selected.join(','));
                selectingSeriTable && selectingSeriTable.draw();
                selectedSeriTable && selectedSeriTable.draw();
            })
            $('#AddSeri .unselect-seri-button').click(function () {
                var selected = $('#AddSeri textarea.seri_selected').html().split(',');
                var deleting = [];
                $('#AddSeri .selected input[type="checkbox"]').each(function () {
                    if ($(this).prop('checked')) {
                        var rowIndex = $(this).closest('tr').index();
                        selected.splice(rowIndex, 1);
                    }
                });
                $('#AddSeri textarea.seri_selected').html(selected.join(','));

                selectingSeriTable && selectingSeriTable.draw();
                selectedSeriTable && selectedSeriTable.draw();
            });
            $('#AddSeri .selected-seri').click(function () {
                var selected = $('#AddSeri textarea.seri_selected').html()
                var index = $('#AddSeri input[name="data-index"]').val();
                $('.selected-series-' + index).html(selected);
                selected = selected.split(',').filter( v => v.trim());
                var count = selected.length;
                if (count <= 3) {
                    var text = [];
                    $('#seri-selected tbody tr td:nth-child(2)').each(function() {
                        text.push($(this).html())
                    })
                    $('.selected-seri-plain-text-' + index).html(text.join(','));
                } else {
                    var text = [];
                    $('#seri-selected tbody tr td:nth-child(2)').each(function () {
                        if (text.length <= 3) {
                            text.push($(this).html())
                        }
                    })
                    $('.selected-seri-plain-text-' + index).html(text.slice(0, 3).join(',') + ', + thêm ' + (count-3) + ' seri');
                }

                $('#AddSeri').modal('hide');
            });
            $(document).on('click','#AddSeri tbody tr',  function () {
                $(this).find("input[type='checkbox']").prop('checked', !$(this).find("input[type='checkbox']").prop('checked'));
            });
            $(document).on('click','#AddSeri tbody tr input[type="checkbox"]',  function (event) {
                event.stopPropagation();
            })
        });
    </script>
@endpush
