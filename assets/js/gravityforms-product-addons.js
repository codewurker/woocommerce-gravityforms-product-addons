let ajax_price_req;
let wc_gforms_current_variation;
//See the gravity forms documentation for this function.
function gform_product_total(formId, total) {
    let product_id = jQuery("input[name=product_id]").val();
    if (wc_gravityforms_params.use_ajax[product_id]) {
        return update_dynamic_price_ajax(total, formId);
    } else {
        return update_dynamic_price(total, formId);
    }
}

function get_gravity_forms_price(formId) {

    if (!_gformPriceFields[formId]) {
        return;
    }

    var price = 0;

    _anyProductSelected = false; //Will be used by gformCalculateProductPrice().
    for (var i = 0; i < _gformPriceFields[formId].length; i++) {
        price += gformCalculateProductPrice(formId, _gformPriceFields[formId][i]);
    }

    //add shipping price if a product has been selected
    if (_anyProductSelected) {
        //shipping price
        var shipping = gformGetShippingPrice(formId)
        price += shipping;
    }

    //gform_product_total filter. Allows users to perform custom price calculation
    if (window["gform_product_total"]) {
        price = window["gform_product_total"](formId, price);
    }

    price = gform.applyFilters('gform_product_total', price, formId);
    return price;
}

function update_dynamic_price(gform_total, formId = '') {
    let $form = null;
    if (formId) {
        $form = jQuery('#gform_' + formId);
    } else {
        $form = jQuery('form.cart');
    }

    // Function moved in delay so that variation prices are updated - Vidish - 16-10-2017
    //setTimeout(function () {

        const product_id = $form.find("input[name=product_id]").val();
        const variation_id = $form.find("input[name=variation_id]").val();

        if (product_id || variation_id) {
            let the_id = 0;
            if (variation_id) {
                the_id = variation_id;
            } else {
                the_id = product_id;
            }

            let base_price = wc_gravityforms_params.prices[the_id];
            if ($form.find('.wc-bookings-booking-cost').attr('data-raw-price')) {
                base_price = $form.find('.wc-bookings-booking-cost').attr('data-raw-price');
            }

            if (base_price === 'UNAVAILABLE') {
                $form.find('.formattedBasePrice').html('--');
                $form.find('.formattedVariationTotal').html(accounting.formatMoney(gform_total, {
                        symbol: wc_gravityforms_params.currency_format_symbol,
                        decimal: wc_gravityforms_params.currency_format_decimal_sep,
                        thousand: wc_gravityforms_params.currency_format_thousand_sep,
                        precision: wc_gravityforms_params.currency_format_num_decimals,
                        format: wc_gravityforms_params.currency_format
                    }
                ));
                $form.find('.formattedTotalPrice').html('--');
            } else {

                $form.find('.formattedBasePrice').html(accounting.formatMoney(base_price, {
                        symbol: wc_gravityforms_params.currency_format_symbol,
                        decimal: wc_gravityforms_params.currency_format_decimal_sep,
                        thousand: wc_gravityforms_params.currency_format_thousand_sep,
                        precision: wc_gravityforms_params.currency_format_num_decimals,
                        format: wc_gravityforms_params.currency_format
                    }
                ));

                $form.find('.formattedVariationTotal').html(accounting.formatMoney(gform_total, {
                        symbol: wc_gravityforms_params.currency_format_symbol,
                        decimal: wc_gravityforms_params.currency_format_decimal_sep,
                        thousand: wc_gravityforms_params.currency_format_thousand_sep,
                        precision: wc_gravityforms_params.currency_format_num_decimals,
                        format: wc_gravityforms_params.currency_format
                    }
                ));

                $form.find('.formattedTotalPrice').html(accounting.formatMoney(parseFloat(base_price) + parseFloat(gform_total), {
                        symbol: wc_gravityforms_params.currency_format_symbol,
                        decimal: wc_gravityforms_params.currency_format_decimal_sep,
                        thousand: wc_gravityforms_params.currency_format_thousand_sep,
                        precision: wc_gravityforms_params.currency_format_num_decimals,
                        format: wc_gravityforms_params.currency_format
                    }
                ) + wc_gravityforms_params.price_suffix[product_id]);
            }
        }
    //}, 1000);


    return gform_total;
}

function update_dynamic_price_ajax(gform_total, formId = '') {
    jQuery('div.product_totals').block({
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        }
    });

    let $form = null;
    if (formId) {
        $form = jQuery('#gform_' + formId);
    } else {
        $form = jQuery('form.cart');
    }

    let product_id = jQuery("input[name=product_id]").val();
    let variation_id = wc_gforms_current_variation && wc_gforms_current_variation !== 'UNAVAILABLE' ? wc_gforms_current_variation : 0;

    let the_id = 0;
    if (variation_id) {
        the_id = variation_id;
    } else {
        the_id = product_id;
    }

    let base_price = wc_gravityforms_params.prices[the_id];
    if ($form.find('.wc-bookings-booking-cost').attr('data-raw-price')) {
        base_price = $form.find('.wc-bookings-booking-cost').attr('data-raw-price');
    }

    if (ajax_price_req) {
        ajax_price_req.abort();
    }

    let opts = "base_price=" + base_price + "&product_id=" + product_id + "&variation_id=" + variation_id;
    opts += '&action=gforms_get_updated_price&gform_total=' + gform_total;

    ajax_price_req = jQuery.ajax({
        type: "POST",
        url: woocommerce_params.ajax_url,
        data: opts,
        dataType: 'json',
        success: function (response) {
            jQuery('.formattedBasePrice').html((response.formattedBasePrice));
            jQuery('.formattedVariationTotal').html(response.formattedVariationTotal);
            jQuery('.formattedTotalPrice').html(response.formattedTotalPrice);

            jQuery('div.product_totals').unblock();
        }
    });
    return gform_total;
}


(function ($) {

    $.fn.wc_gravity_form = function () {
        let $form = this;
        if (!$form.hasClass('cart')) {
            return this;
        }


        const form_id = $form.find("input[name=wc_gforms_form_id]").val();
        const product_type = $form.find("input[name=wc_gforms_product_type]").val();
        const product_id = jQuery("input[name=product_id]").val();
        const variation_id = jQuery("input[name=variation_id]").val();
        let the_product_id = 0;

        if (product_id || variation_id) {
            if (variation_id) {
                the_product_id = variation_id;
            } else {
                the_product_id = product_id;
            }
        }

        if (!form_id) {
            return this;
        }

        //Maybe jump to validation error:
        if (wc_gravityforms_params.use_anchors && $('.gform_validation_error', 'form.cart').length) {
            if (!window.location.hash) {
                window.location = window.location + '#gform_' + form_id;
            }
        }

        const next_page = parseInt($form.find("input[name=wc_gforms_next_page]").val(), 10) || 0;
        const previous_page = $form.find("input[name=wc_gforms_previous_page]").val();

        if (product_type !== 'external') {
            $form.attr('action', '');
        }

        $form.attr('id', 'gform_' + form_id);

        $form.on('found_variation', function (e, variation) {
            try {
                wc_gforms_current_variation = variation.variation_id;
                gf_apply_rules(form_id, ["0"]);
            } catch (err) {
                console.log(err);
            }
            gformCalculateTotalPrice(form_id);
        });

        /*
         * Bookings Integration
         * The wc_bookings_calculations_complete is a custom event that we fire in this script.  The event is fired when Bookings completes an AJAX request to calculate the cost of a booking.
         * We listen for this event and update the price accordingly.
         *
         * The following code is a shim for Bookings 2.0.8+.  Hopefully this code can be removed in the future.
         */
        $form.on('wc_bookings_calculations_complete', function (e, result) {
            if (result && result.hasOwnProperty('raw_price')) {
                $form.find('.wc-bookings-booking-cost').attr('data-raw-price', result.raw_price);
            } else {
                $form.find('.wc-bookings-booking-cost').attr('data-raw-price', 'UNAVAILABLE');
            }
            gformCalculateTotalPrice(form_id);
        });


        $('button[type=submit]', $form).attr('id', 'gform_submit_button_' + form_id).addClass('button gform_button');


        if (next_page !== 0) {
            $('button[type=submit]', $form).remove();
            $('div.quantity', $form).remove();
            $('#wl-wrapper', $form).hide();

            const stripe_payment_request_wrapper = $form.find('#wc-stripe-payment-request-wrapper');
            if (stripe_payment_request_wrapper.length) {
                stripe_payment_request_wrapper.remove();
            }

            const wc_stripe_payment_request_button_separator = $form.find('#wc-stripe-payment-request-button-separator');
            if (wc_stripe_payment_request_button_separator.length) {
                wc_stripe_payment_request_button_separator.remove();
            }
        }

        $('.gform_next_button', $form).attr('onclick', '');

        $('.gform_next_button', $form).click(function (event) {
            if (!window.location.hash) {
                window.location.hash = '#_form_' + form_id;
            }

            $form.attr('action', window.location.hash);
            $("#gform_target_page_number_" + form_id, $form).val(next_page);
            $form.trigger("submit", [true]);
        });

        $('.gform_previous_button', $form).click(function (event) {
            $("#gform_target_page_number_" + form_id, $form).val(previous_page);
            if (!window.location.hash) {
                window.location.hash = '#_form_' + form_id;
            }

            $form.attr('action', window.location.hash);
            $form.trigger("submit", [true]);
        });


        if (wc_gravityforms_params.initialize_file_uploader) {
            // Set up the multi-file uploader if for some reason Gravity Forms itself has not done so.
            $("form#gform_" + form_id + " .gform_fileupload_multifile").each(function () {
                if (typeof gfMultiFileUploader.uploaders[this.id] === "undefined") {
                    console.log('Setting up multifile manually');
                    gfMultiFileUploader.setup(this);
                }
            });
        }

        return this;
    };

    $(document).on('wc_variation_form', function (e) {
        var $form = $(this);
        $form.wc_gravity_form();
    });

    $(document).ready(function (e) {
        $('form.cart').each(function (index, form) {
            var $form = $(form);
            $form.wc_gravity_form();
        });
    });


    $(document).on('quick-view-displayed', function () {
        console.log('quick view displayed');
        setTimeout(function () {

            $.globalEval($('.quick-view-content').find('script').text());
            $('.quick-view-content').find('form').each(function (i, form) {
                $(form).wc_gravity_form();
            })

        }, 0);
    });


    /** The following section shims Bookings so that when it completes an AJAX request it fires an event on the form that we can listen for. */
    function handleBookingsAjaxCompletedEvent(event, jqXHR, ajaxOptions, $form) {
        if (ajaxOptions.data && ajaxOptions.data.indexOf('action=wc_bookings_calculate_costs') !== -1) {
            try {
                let code = jqXHR.responseText;
                if (code.charAt(0) !== '{') {
                    // eslint-disable-next-line
                    console.log(code);
                    code = '{' + code.split(/\{(.+)?/)[1];
                }

                let result = JSON.parse(code);
                if (result && result.hasOwnProperty('result')) {
                    $form.trigger('wc_bookings_calculations_complete', [result]);
                }
            } catch (err) {
                // Catch any errors so that we are sure to unbind the AJAX handler.
                console.log(err);
            }
        }
    }

    jQuery(document).ready(function ($) {
        // Listen for the pre-calculate booking cost action, and bind AJAX handlers.
        // The ajax handlers will be unbound after the AJAX request is complete.
        if (window.wc_bookings) {
            window.wc_bookings.hooks.addAction('wc_bookings_pre_calculte_booking_cost', 'wcgfpa', function (data) {
                const $form = $(data.form);

                // Create an event handler here so that we can pass the form to the handler.
                const scopedAjaxCompleteHandler = function (event, jqXHR, ajaxOptions) {
                    handleBookingsAjaxCompletedEvent(event, jqXHR, ajaxOptions, $form);
                    // Unbind AJAX handlers
                    $(document).off('ajaxComplete', scopedAjaxCompleteHandler);
                    $form.data('ajaxCompleteHandler', null);
                };

                // Bind AJAX handlers, but only if they are not already bound on the form.
                if (!$form.data('ajaxCompleteHandler')) {
                    // Bind to our scoped handler.
                    $(document).on('ajaxComplete', scopedAjaxCompleteHandler);
                    $form.data('ajaxCompleteHandler', scopedAjaxCompleteHandler);
                }
            });
        }
    });

})(jQuery);




