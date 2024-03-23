@include('la.produces.style')
@push('scripts')
<script>
    var itemIndex = {{ (int)@$itemIndex }};
    var includeNote = {{ isset($includeNote) ? $includeNote : 0 }};
    function addProduct()
    {   
        var el = '<div class="col-sm-12 p-product-item">' + 
            '<div class="col-sm-6">' +
                '<select class="ajax-select p-product-id p-product-' + itemIndex + '" required="1" extra_param="1" model="product" name="products['+ itemIndex +'][product_id]"></select>' + 
            '</div>' + 
            '<div class="col-sm-3 p-product-attrs">' +
            '</div>' + 
            '<div class="col-sm-2">' +
                '<input class="form-control" required="1" name="products['+ itemIndex +'][quantity]" />' + 
            '</div>' + 
            '<div class="col-sm-1">' + 
                '<button type="button" class="btn btn-danger btn-sm p-product-remove"><i class="fa fa-times"></i></button>' + 
            '</div>';
        if (includeNote) {
            el += '<div class="col-sm-12" style="margin-top: 5px;">' +
                '<input class="form-control" name="products['+ itemIndex +'][note]" placeholder="Ghi chú" />' +
            '</div>';
        }
        el += '</div>';
        $('.p-product-items').append(el);
        initAjaxSelect('.p-product-' + itemIndex);
        itemIndex++;
    }



    $(function () {
        $('.p-product-add').click(function(){
            addProduct();    
        });
        $(document).on('change', '.product-id', function() {
            var el = $(this);
            $.ajax({
                url: '{{ route("produce.product.attribute") }}?p_id=' + $(this).val(),
                dataType: "JSON",
                success: function(result) {
                    var html = '';
                    if (typeof result == 'object') {
                        html = '<div class="row">';
                        result.map(attr => {
                            html += '<div class="col-xs-12 form-group">' + 
                                    '<label>'+ attr.text +'</label>' + 
                                    '<select class="form-control" name="attrs_value[]">' + 
                                    attr.values.map(value => {
                                        return '<option value="'+ value.id +'">' + value.value + '</option>';
                                    }) + 
                                    '</select>' + 
                                '</div>';

                        });
                        html += '</div>';
                    }
                    el.parents('form').find('.product-attrs').html(html);
                }
            })
        });
        $(document).on('change', '.p-product-id', function() {
            var el = $(this);
            var name = $(this).attr('name').replace('[product_id]', '[attrs_value][]');
            $.ajax({
                url: '{{ route("produce.product.attribute") }}?p_id=' + $(this).val(),
                dataType: "JSON",
                success: function(result) {
                    var html = '';
                    if (typeof result == 'object') {
                        result.map(attr => {
                            html += '<div class="col-xs-12 form-group">' + 
                                    '<select class="form-control" name="'+ name +'">' + 
                                    attr.values.map(value => {
                                        return '<option value="'+ value.id +'">' + value.value + '</option>'
                                    }) + 
                                    '</select>' + 
                                '</div>';

                        });
                    }
                    el.parents('.p-product-item').find('.p-product-attrs').html(html);
                }
            })
        });
        $(document).on('click', '.p-product-remove', function() {
            if ($('.p-product-remove').length > 1) {
                $(this).parents('.p-product-item').remove();
            }
        });
    });
</script>
@endpush