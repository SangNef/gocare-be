<div class="row bg-primary clearfix">
    <div class="col-md-6">
        @if (isset($totals) && !empty($totals))
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
            @if (isset($extraFilter) && $extraFilter != '')
                @include($extraFilter)
            @endif
        </form>
    </div>
</div>
<div class="box box-success">
    <!--<div class="box-header"></div>-->
    <div class="box-body" id="{{ isset($id) ? $id : '' }}">
        <table id="example1" class="table table-bordered">
            <thead>
                <tr class="success">
                    @foreach ($cols as $k => $col)
                        <th>
                            {{ $col['title'] }}
                        </th>
                    @endforeach
                    @if($show_actions)
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <thead id="filter_bar">
                <tr class="success">
                    @foreach ($cols as $k => $col)
                        <th colname="{{ $col['field'] }}">
                            {{ $col['title'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

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
$(function () {
    var baseUrl = "{!! url(config('laraadmin.adminRoute') . '/' . $path) !!}";
    
    $('#example1 #filter_bar tr').insertBefore($('#myDataTable thead tr'));
	var table = $("#example1").DataTable({
		destroy: true,
        pageLength: 50,
        processing: true,
        serverSide: true,
        searchDelay: 500,
        ajax: {
            url: baseUrl,
            beforeSend: function (jqXHR, settings) {
                abortAll();
                xhrPool.push(jqXHR);
                settings.url = settings.url + '&' + $.param($('#admin-filter-form').serializeArray());
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
		columnDefs: [ { orderable: false, targets: [-1] }],
		@endif
	});
	
	var filters = $('#example1 #filter_bar th'), count = filters.length;
    filters.each( function () {
        var title = $('#example1 thead th').eq( $(this).index() ).text();
        var colname = $(this).attr('colname');
        if (colname === 'backlogs') {
            $(this).empty();
        } else if (colname === 'email') {
            addAjaxSelectFilter($(this), 'customer-email');
        } else if (colname === 'group_id') {
            addAjaxSelectFilter($(this), 'group');
        } else if (colname === 'customer_id') {
            addAjaxSelectFilter($(this), 'customer');
        } else if (colname === 'customer_name') {
                addAjaxSelectFilter($(this), 'customer');
        } else if (colname === 'store_id') {
            addAjaxSelectFilter($(this), 'stores');
        } else if (colname === 'parent_id') {
            addAjaxSelectFilter($(this), 'user');
        } else if (title) {
            $(this).html('<input class="filter-item" style="width: 100px;" type="text" placeholder="" id ="filter' + $(this).index() + '"/>');
        }
        
        if (!--count) {
            initAjaxSelect2();
        }
    } );
    
	var filterColumns = JSON.parse("{{json_encode(array_keys($cols))}}");
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
                table.draw();
            }
        });
    });
    
    $('#admin-filter-form input, #admin-filter-form select').change(function () {
        abortAll();
        table.draw();
    });

    function addAjaxSelectFilter(el, model) {
        el.html('<select class="ajax-select filter-item" model="'+ model +'" style="width: 100px;" id ="filter' + el.index() + '"></select>');
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
});
</script>
@endpush
