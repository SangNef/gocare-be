@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('la-assets/plugins/datepicker/datepicker3.css') }}">
@endpush
@if(isset($useExtraFilter) && $useExtraFilter)
<div class="row bg-primary clearfix">
    <div class="col-md-6">
        @if(isset($totals))
            @foreach ($totals as $id => $field)
                <div class="row">
                    <div class="dollar-icon text-primary"><i class="fa fa-dollar"></i></div>
                    <h4 class="name">{{ $field }}: <span id="{{ $id }}">0</span>đ</h4>
                </div>
            @endforeach
        @endif
    </div>
    <div class="col-md-6">
        <form method="get" id="admin-filter-form">
            @if(isset($filterCreatedDate) && $filterCreatedDate)
            <div class="form-group">
                <label>
                    Ngày tạo:
                </label>
                <div class="row">
                    <div class="col-sm-4">
                    <input type="text" id="filter-0-value" name="filter[0][value]" @if(isset($filterDate) && $filterDate) value="{{ \Carbon\Carbon::now()->subDays(7)->format('Y/m/d') }}" @endif placeholder="Từ ngày" class="form-control input-sm datepicker"/>
                        <input type="hidden" name="filter[0][field]" value="created_at"/>
                        <input type="hidden" name="filter[0][operation]" value="gte"/>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" name="filter[1][value]" @if(isset($filterDate) && $filterDate) value="{{ \Carbon\Carbon::now()->addDay()->format('Y/m/d') }}" @endif placeholder="Đến ngày" class="form-control input-sm datepicker"/>
                        <input type="hidden" name="filter[1][field]" value="created_at"/>
                        <input type="hidden" name="filter[1][operation]" value="lte"/>
                    </div>
                </div>
            </div>
            @endif
            @if (isset($extraForm))
                @include($extraForm)
            @endif
        </form>
    </div>
</div>
@endif
@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>    
    var xhrPool = [];
    function abortAll() {
        xhrPool.map(function(jqXHR) {
            jqXHR.abort();
        });
        xhrPool = [];
    }
    var table;
    $(function () {
        const saveTableFilter = saveFilter(window.location.href);
        $('#example1 #filter_bar tr').insertBefore($('#myDataTable thead tr'));
        table = $("#example1").DataTable({
            destroy: true,
            pageLength: 50,
            processing: true,
            serverSide: true,
            searchDelay: 500,
            ajax: {
                url: url,
                beforeSend: function (jqXHR, settings) {
                    abortAll();
                    xhrPool.push(jqXHR);
                    settings.url = settings.url + '&' + $.param($('#admin-filter-form').serializeArray());
                    if (typeof excludes !== 'undefined' && excludes.length > 0) {
                        settings.url = settings.url + '&exclude=' + excludes.join(',')
                    }
                },
                complete: function(data) {
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
            @if($show_actions)
            columnDefs: [ { orderable: false, targets: [0] }],
            @endif
        });

        var filterOptions = {!! json_encode(isset($filterOptions) ? $filterOptions : []) !!};
        var filters = $('#example1 #filter_bar th'), count = filters.length;
        filters.each( function () {
            var title = $('#example1 thead th').eq( $(this).index() ).text();
            var colname = $(this).attr('colname');
            if (colname === 'creator_id' || colname === 'approve_id' || colname === 'user_id' || colname === 'approver_id') {
                addAjaxSelectFilter($(this), 'user');
            } else if (colname === 'customer_id') {
                addAjaxSelectFilter($(this), 'customer');
            } else if (colname === 'sender_id' || colname === 'receiver_id') {
                addAjaxSelectFilter($(this), 'user');
            } else if (colname === 'category_ids') {
                addAjaxSelectFilter($(this), 'productcategory');
            } else if (colname === 'customer') {
                addAjaxSelectFilter($(this), 'customer');
            } else if (colname === 'bank_id') {
                addAjaxSelectFilter($(this), 'banks');
            } else if (colname === 'product_id') {
                addAjaxSelectFilter($(this), 'product');
            } else if (colname === 'store_id') {
                addAjaxSelectFilter($(this), 'stores');
            } else if (colname === 'group_id') {
                addAjaxSelectFilter($(this), 'group');
            } else if (title) {
                $(this).html('<input class="filter-item" style="width: 100px;" type="text" placeholder="" id ="filter' + $(this).index() + '"/>');
            }
            
            if (['status', 'phone_type', 'apart', 'pc_id', 'type', 'sub_type', 'approve', 'cod_compare_status', 'from'].indexOf(colname) !== -1) {
                addSelectFilter($(this), getOptions(colname));
            }
            if (!--count) {
                initAjaxSelect2();
            }
            $(this).addClass('col-' + colname);
        } );

        if("{{!isset($filterColumns) || empty($filterColumns)}}") {
            filterColumns = JSON.parse("{{json_encode(array_keys($listing_cols))}}");
        } else {
            filterColumns = JSON.parse("{{json_encode($filterColumns)}}");
        }
        table.columns(filterColumns).every(function () {
            var that = this;
            $('input.filter-item, select.filter-item', this.footer()).on('keyup change', function (e) {
                if (that.search() != this.value) {
                    filterColumns.map(function (key, value) {
                        if ($("#filter" + key).val() !== null) {
                            table.column(key).search($("#filter" + key).val());
                        } else {
                            table.column(key).search('');
                        }
                    })
                    saveTableFilter.store('columns', this);
                    table.draw();
                }
            });
        });

        $('#admin-filter-form input, #admin-filter-form select').change(function () {
            abortAll();
            saveTableFilter.store('extra', this);
            table.draw();
        });

        function addAjaxSelectFilter(el, model) {
            el.html('<select class="ajax-select filter-item" model="'+ model +'" style="width: 100px;" id ="filter' + el.index() + '"></select>');
        }

        function getOptions(col) {
            var options = []
            if (typeof filterOptions[col] !== 'undefined') {
                options = filterOptions[col];
            }

            return options;
        }

        function addSelectFilter(el, options) {
            var html = '<select class="filter-item" style="width: 120px;" id ="filter' + el.index() + '">';
            html += '<option value="">Tất cả</option>';
            for (var value in options) {
                html += '<option value="'+ value +'">' + options[value] + '</option>';
            }
            html += '</select>';
            el.html(html);
        }

        function initAjaxSelect2() {
            $('.ajax-select').each(function () {
                var url = '{{ route('ajax-select') }}?model=' + $(this).attr('model');
                $(this).select2({
                    allowClear: true,
                    placeholder: "Chọn",
                    cache: true,
                    ajax: {
                        url: function (params) {
                            var url = ajax_url + '?model=' + $(this).attr('model');
                            if ($(this).attr('extra_param')) {
                                url += '&extra_param=' + $(this).attr('extra_param');
                            }
                            var lookups = $(this).attr('lookup') ? $(this).attr('lookup').split(',') : [];
                            for (i = 0; i < lookups.length; i++) {
                                var el = $('#' + lookups[i]);
                                if (el.length > 0 && el.attr('name') && el.val()) {
                                    url += '&' + el.attr('name') + '=' + el.val();
                                }
                            }
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

        function saveFilter(key) {
            const store = JSON.parse(sessionStorage.getItem(key)) || {
                columns: [],
                extra: []
            };

            const isValidType = (type) => type.match(/columns|extra/); 

            return { 
                store(type, el) {
                    if (!isValidType(type)) return;

                    let name = $(el).attr(`${type === 'columns' ? 'id' : 'name'}`);
                    let value = $(el).val();

                    if ($(el).hasClass('ajax-select')) {
                        value = $(el).select2('data').map(function(item) {
                            return {id: item.id, text: item.text};
                        });
                    }
                    const existItem = store[type].find(item => item.name === name);

                    if (existItem) {
                        existItem.value = value;
                    } else {
                        store[type].push({name, value});
                    }
                    return sessionStorage.setItem(key, JSON.stringify(store));
                },
                get(type) {
                    return isValidType(type) ? store[type] : [];
                }
            }
        }

        @if(isset($restoreState) && $restoreState)
            (function() {
                table.columns().eq(0).each(function (index) {
                    const col = $(`#example1 #filter_bar th #filter${index}`);
                    const data = saveTableFilter.get('columns').find(item => item.name === col.attr('id'));
                    if (typeof data !== 'undefined') {
                        if (col.is('select') && col.hasClass('ajax-select')) {
                            for (let i = 0; i < data.value.length; ++i) {
                                const newOption = new Option(data.value[i].text, data.value[i].id, true, true);
                                col.append(newOption);
                                table.column(index).search(data.value[i].id);
                            }
                        } else {
                            col.val(data.value);
                            table.column(index).search(data.value);
                        }
                    }
                });

                $('#admin-filter-form input, #admin-filter-form select').each(function () {
                    const name = $(this).attr('name');
                    const data = saveTableFilter.get('extra').find(item => item.name === name);
                    if (typeof data !== 'undefined') {
                        if ($(this).is('select') && $(this).hasClass('ajax-select')) {
                            for (let i = 0; i < data.value.length; ++i) {
                                const newOption = new Option(data.value[i].text, data.value[i].id, true, true);
                                $(this).append(newOption);
                            }
                        } else {
                            $(this).val(data.value);
                        }
                    }
                });

                table.draw();
            })();
        @endif
    })
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const filterValue = urlParams.get('filter[0][value]');

        if (filterValue !== null) {
            console.log(filterValue)
            document.getElementById('filter-0-value').value = filterValue;
        }
    });
</script>
@endpush
