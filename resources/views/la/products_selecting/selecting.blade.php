@include('la.products_selecting.layout.' . (isset($layout) ? $layout : 'inline'))
@push('scripts')
    <script>
        var xhrPoolProduct = [];
        function abortAllProduct() {
            xhrPoolProduct.map(function(jqXHR) {
                jqXHR.abort();
            });
            xhrPoolProduct = [];
        }
        var excludeFilter = {!! json_encode(isset($excludeFilter) ? $excludeFilter : []) !!}
        var excludeSeleceted = {!! json_encode(isset($selectedProducts)
                ? $selectedProducts->map(function ($item) {return $item['products'][0]->id;})->toArray()
                : [] ) !!};
        var addingProductParams = {
            'view': '{{ isset($selectedView) ? $selectedView : '' }}',
            'type': '{{ isset($productType) ? $productType : \App\Models\Product::TYPE_SIMPLE_PRODUCT }}',
            'customer_id': '{{ isset($customerId) ? $customerId : '' }}',
        };
        var currencyType = '{{ isset($currencyType) ? $currencyType : 0 }}';
        var orderSubType = '{{ isset($sub_type) ? $sub_type : 0 }}';
        var combos = [];

        function loadProduct(page = 1)
        {
            var url = '{{ route('products.get') }}?'
                + '&' + $.param($('#product-filter :input').serializeArray())
                + '&' + $.param(excludeFilter);
            if ($('#order_store').length && $('#order_store').val()) {
                url += '&store_id=' + $('#order_store').val();
            }
            if ($('#order_customer').length && $('#order_customer').val()) {
                url += '&order_customer=' + $('#order_customer').val();
            }
            $.ajax({
                url: url,
                beforeSend: function (jqXHR, settings) {
                    abortAllProduct();
                    xhrPoolProduct.push(jqXHR);
                },
                data: {
                    page: page
                },
                success: function (data) {
                    $('#product-list').html(data);
                }
            })
        }

        function loadSelectedProducts() {
            var nQuantity = [];
            $('input.n_quantity').each(function () {
                nQuantity.push($(this).val());
            });
            var wQuantity = [];
            $('input.w_quantity').each(function () {
                wQuantity.push($(this).val());
            });
            $('#selected_products').css({'opacity': '0.7'});
            $('#selected_products button, #selected_products input').prop('disabled', true);
            
            var currency = parseInt(currencyType) ? currencyType : $('#order-add-form input[name="currency_type"]:checked').val();
            var url = '{{ route('products.get.id') }}?product_id=' + excludeSeleceted.join(',') + '&currency_type=' + currency;
            var selectAttrs = [];
            var index = [];
            $('.order-selected-product').each(function (item) {
                var values = [];
                $(this).find('.product_attribute').each(function () {
                    values.push($(this).val());
                })
                selectAttrs.push(values.join(','));
                index.push($(this).find('.item-key').val());
            });

            url += '&attr_ids=' + selectAttrs.join('|') + '&existed_index=' + index.join('|');

            $.ajax({
                url: url,
                data: addingProductParams,
                success: function (data) {
                    $('#selected_products').html(data);
                    $('#selected_products').css({'opacity': '1'});
                    $('input.n_quantity').each(function (index) {
                        $(this).val(nQuantity[index]);
                    });
                    $('input.w_quantity').each(function (index) {
                        $(this).val(wQuantity[index]).change();
                    });
                    if (typeof loadSelectedProductIndex !== 'undefined') {
                        loadSelectedProductIndex();
                    }
                }
            });
        }

        function addProducts(productId, el, combos = [], attrIds = '', seris = [])
        {
            var currency = parseInt(currencyType) ? currencyType : $('#order-add-form input[name="currency_type"]:checked').val();
            var url = '{{ route('products.get.id') }}?product_id=' + productId + '&currency_type=' + currency;
            if (parseInt(orderSubType)) url += `&sub_type=${orderSubType}`;
            if (combos.length > 0) {
                url += `&combos=${combos.join(',')}`;
            }
            if ($('#order_customer').length && $('#order_customer').val()) {
                url += '&order_customer=' + $('#order_customer').val();
            }
            if (attrIds) {
                url += '&attr_ids=' + (attrIds + '').split(',').join('|');
            }
            $.ajax({
                url: url,
                data: addingProductParams,
                async: false,
                success: function (data) {
                    $('#selected_products').append(data);
                    el.find('.product-info').removeClass('adding');
                    excludeSeleceted.push(productId);
                    $('#selected_products').trigger('selecting_product.changed');
                    $('#selected_products').trigger('selecting_product.added', [seris]);
                    $('.order-selected-product:last-child .product_attrs select:last-child').change();
                    $(`.order-selected-product:last-child .currency`).each(function(i, element) {
                        new AutoNumeric(element, {
                            currencySymbol: ' đ',
                            currencySymbolPlacement: 's',
                            allowDecimalPadding: false
                        })
                    });
                }
            });
        }

        $(function () {
            loadProduct();

            $(document).on('change', '#selected_products input, #selected_products select',function () {
                $('#selected_products').trigger('selecting_product.changed');
            });
            $(document).on('click', '#product-list .pagination a', function (event) {
                event.preventDefault();
                loadProduct(GetURLParameter('page', $(this).attr('href')));
            });
            $('#product-filter input').keyup(function () {
                loadProduct();
            });
            $('#product-filter input').change(function () {
                loadProduct();
            });
            $('#product-filter select').change(function () {
                loadProduct();
            });

            $(document).on('selected_products.reload', '#selected_products', function () {
                loadSelectedProducts();
            });

            $(document).on('click', '.product-item .product-info', function (event) {
                event.preventDefault();
                if ($(this).attr('class').indexOf('adding') === -1) {
                    $(this).addClass('adding');
                    var combo = [];
                    var comboEls = $(this).parents('.product-item').find('.product-combo input');
                    if (comboEls.length > 0) {
                        comboEls.each(function () {
                            if ($(this).prop('checked')) {
                                combo.push($(this).val());
                            }
                        });
                    }

                    addProducts($(this).parents('.product-item').attr('data-id'), $(this).parents('.product-item'), combo);
                }
            });
            $(document).on('click', '.remove-selected-product', function () {
                var productId = $(this).attr('data-id');
                excludeSeleceted.splice(excludeSeleceted.indexOf(productId), 1);
                var product = $(this).parents('.selected-product');
                if (product.hasClass('has-combo')) {
                    var next = product;
                    while(true)
                    {
                        next = next.next();
                        if (next.hasClass('in-combo')) {
                            next.remove();
                        } else {
                            break;
                        }
                    };
                }
                product.remove();
                $('#selected_products').trigger('selecting_product.changed');
            });
            $(document).on('change', '.has-combo input.n_quantity', function () {
                var quantity = $(this).val();
                var oldQuantity = parseInt($(this).attr('old'));
                var next = $(this).parents('.order-selected-product');

                while(true)
                {
                    next = next.next();
                    if (next.hasClass('in-combo')) {
                        var qInput = next.find('input.n_quantity');
                        var qInputValue = qInput.val();
                        var ratio = qInputValue / oldQuantity;
                        qInput.val(quantity*ratio).change();
                    } else {
                        break;
                    }
                };
            }).change();

            setTimeout(function () {
                $('.has-combo input.n_quantity').each(function () {
                    if (!$(this).is('[readonly]')) {
                        $(this).change();
                    }
                });
            }, 2000)

            $(document).on('focus', '.has-combo input.n_quantity', function () {
                var quantity = $(this).val();
                $(this).attr('old', quantity);
            });
        });
    </script>
@endpush
