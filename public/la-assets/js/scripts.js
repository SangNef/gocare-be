// jQuery(document).ready(function( $ ) {
//     $('input.currency').autoNumeric('init', {aSign:' đ', pSign:'s' });
// });
function GetURLParameter(sParam, sPageURL) {
    if (!sPageURL) {
        sPageURL = window.location.search.substring(1);
    } else {
        var url = sPageURL.split("?");
        sPageURL = url.length >= 2 ? url[1] : "";
    }
    var sURLVariables = sPageURL.split("&");
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split("=");
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        }
    }

    return "";
}

function initTinyMce() {
    tinymce.init({
        selector: "textarea.tinymce",
        encoding: "xml",
        image_caption: true,
        plugins:
            "print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons",
        menubar: "file edit view insert format tools table help",
        toolbar:
            "undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl",
        quickbars_selection_toolbar:
            "bold italic | quicklink h2 h3 blockquote quickimage quicktable",
        relative_urls: false,
        remove_script_host: false,
        image_title: true,
        images_upload_handler: function (blobInfo, success, failure) {
            var xhr, formData;
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '/admin/upload_files');
            var token = $('meta[name="_token"]').attr('content');
            xhr.setRequestHeader("X-CSRF-Token", token);
            xhr.onload = function () {
                var json;
                if (xhr.status != 200) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }
                json = JSON.parse(xhr.responseText);

                if (!json || typeof json.upload.hash != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }
                var location = window.location.protocol + "//" + window.location.host+'/files/'+json.upload.hash+'/'+json.upload.name;
                success(location);
            };
            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        }
    });
}

function getShippingPartnerAddress(url, id, type, otherParams = {}) {
    return $.ajax({
        type: "GET",
        url: url,
        data: {
            id: id,
            type: type,
            ...otherParams,
        },
        dataType: "JSON",
    });
}

function checkRequiredInputs(formId) {
    let isValid = true;
    $(`#${formId} .submit-required`).each(function () {
        if (!$(this).val()) {
            isValid = false;
            $(this).closest(`.form-group`).addClass("has-error");
        } else {
            $(this).closest(`.form-group`).removeClass("has-error");
        }
    });
    return isValid;
}

function initNumberInput(selector) {
    if (selector) {
        AutoNumeric.multiple(selector, {
            currencySymbol: " đ",
            currencySymbolPlacement: "s",
            allowDecimalPadding: false,
        });
        return;
    }

    AutoNumeric.multiple("input.currency", {
        currencySymbol: " đ",
        currencySymbolPlacement: "s",
        allowDecimalPadding: false,
    });
    AutoNumeric.multiple("input.integer", { allowDecimalPadding: false });
}

function updateFormValue(form) {
    $(form)
        .find("input,select,textarea")
        .each(function () {
            if (
                $(this).is("[type='checkbox']") ||
                $(this).is("[type='checkbox']")
            ) {
                if ($(this).prop("checked")) {
                    $(this).attr("checked", "checked");
                } else {
                    $(this).removeAttr("checked");
                }
            } else {
                $(this).attr("value", $(this).val());
            }
        });
}

function getCheckedValue(target) {
    var value = [];
    $(target).each(function () {
        if ($(this).prop("checked")) {
            value.push($(this).val());
        }
    });

    return value;
}

function getAddress(id, model, selector, url) {
    $.ajax({
        type: "GET",
        url: url,
        data: {
            id: id,
            model: model,
        },
        dataType: "JSON",
        success: function (res) {
            for (id in res) {
                $(selector).append(
                    "<option value='" + id + "'>" + res[id] + "</option>"
                );
            }
        },
    });
}

function convertNumberInputValue(value) {
    value =
        typeof value !== "undefined"
            ? parseFloat(value.replace(/[^0-9\.]+/g, ""))
            : 0;

    return isNaN(value) ? 0 : value;
}

$.fn.serializeObject = function () {
    var self = this,
        json = {},
        push_counters = {},
        patterns = {
            validate: /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
            key: /[a-zA-Z0-9_]+|(?=\[\])/g,
            push: /^$/,
            fixed: /^\d+$/,
            named: /^[a-zA-Z0-9_]+$/,
        };

    this.build = function (base, key, value) {
        base[key] = value;
        return base;
    };

    this.push_counter = function (key) {
        if (push_counters[key] === undefined) {
            push_counters[key] = 0;
        }
        return push_counters[key]++;
    };

    $.each($(this).serializeArray(), function () {
        // Skip invalid keys
        if (!patterns.validate.test(this.name)) {
            return;
        }

        var k,
            keys = this.name.match(patterns.key),
            merge = this.value,
            reverse_key = this.name;

        while ((k = keys.pop()) !== undefined) {
            // Adjust reverse_key
            reverse_key = reverse_key.replace(
                new RegExp("\\[" + k + "\\]$"),
                ""
            );

            // Push
            if (k.match(patterns.push)) {
                merge = self.build([], self.push_counter(reverse_key), merge);
            }

            // Fixed
            else if (k.match(patterns.fixed)) {
                merge = self.build([], k, merge);
            }

            // Named
            else if (k.match(patterns.named)) {
                merge = self.build({}, k, merge);
            }
        }

        json = $.extend(true, json, merge);
    });

    return json;
};
function initAjaxSelect() {
    $(".ajax-select").each(function () {
        $(this).select2({
            allowClear: true,
            placeholder: "Chọn",
            cache: true,
            ajax: {
                url: function (params) {
                    var url = ajax_url + "?model=" + $(this).attr("model");
                    if ($(this).attr("extra_param")) {
                        url += "&extra_param=" + $(this).attr("extra_param");
                    }
                    var lookups = $(this).attr("lookup")
                        ? $(this).attr("lookup").split(",")
                        : [];
                    for (i = 0; i < lookups.length; i++) {
                        var el = $("#" + lookups[i]);
                        if (el.length > 0 && el.attr("name") && el.val()) {
                            url += "&" + el.attr("name") + "=" + el.val();
                        }
                    }
                    return url;
                },
                dataType: "json",
                cache: true,
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.data,
                        pagination: {
                            more: params.page * 20 < data.count_filtered,
                        },
                    };
                },
            },
        });
    });
}
function initDatetimePicker() {
    $(".datetime-picker").datetimepicker({
        defaultDate: new Date(),
        format: "DD/MM/YYYY HH:mm:ss",
    });
}
function initDatapicker() {
    $(".datepicker").datepicker({
        format: "yyyy/mm/dd",
        orientation: "top left",
    });
}

function cleanAccents(str) {
    return str
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/-|\s/g, "")
        .replace(/&#039;/g, "")
        .replace(/đ|ð/g, "d")
        .replace(/Đ/g, "D");
}

function formatShippingBillIds(bills) {
    return bills.replace(/[\n\r\t ,]+/g, ", ");
}

function objectToQueryString(baseUri, params) {
    const queryString = Object.keys(params)
        .map((key) => `${key}=${params[key]}`)
        .join("&");
    return `${baseUri}?${queryString}`;
}

Number.prototype.round = function (places) {
    return +(Math.round(this + "e+" + places) + "e-" + places);
};

$(function () {
    initDatetimePicker();
    initDatapicker();
    $(document).on("click", ".onetime-click", function () {
        $(this).attr("disabled", true);
        $(this).html('<i class="fa fa-spinner fa-spin"></i>');
        if ($(this).attr("type") === "submit") {
            $(this).parents("form").submit();
        }
    });

    initTinyMce();
    initNumberInput();
    initAjaxSelect();
    $(document).on("change", ".ck_all", function () {
        var target = "." + $(this).attr("data-target");
        if ($(this).prop("checked")) {
            $(target).prop("checked", true);
        } else {
            $(target).prop("checked", false);
        }
    });
    $(document).on("click", ".form-confirmation", function (e) {
        if (
            !confirm(
                "Xoá đơn hàng sẽ xoá các giao dịch liên quan. Cập nhập các giao dịch liên quan đến ngân hàng." +
                    "\n Bạn có chắc muốn xoá không?"
            )
        ) {
            e.preventDefault();
        }
    });
});
