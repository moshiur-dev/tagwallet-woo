(function($) {
    'use strict';
    let merchantId = $('#woocommerce_wpadtwpg_payinvite_merchant_id');
    let apiKey = $('#woocommerce_wpadtwpg_payinvite_api_key');
    let hostUrl = $('#woocommerce_wpadtwpg_payinvite_host_url');
    let tmerchantId = $('#woocommerce_wpadtwpg_payinvite_test_merchant_id');
    let tapiKey = $('#woocommerce_wpadtwpg_payinvite_test_api_key');
    let thostUrl = $('#woocommerce_wpadtwpg_payinvite_test_host_url');

    var showTestInfo = function() {
        merchantId.closest('tr').hide();
        apiKey.closest('tr').hide();
        hostUrl.closest('tr').hide();
        tmerchantId.closest('tr').show();
        tapiKey.closest('tr').show();
        thostUrl.closest('tr').show();

    }

    var showMainInfo = function() {
        tmerchantId.closest('tr').hide();
        tapiKey.closest('tr').hide();
        thostUrl.closest('tr').hide();
        merchantId.closest('tr').show();
        apiKey.closest('tr').show();
        hostUrl.closest('tr').show();
    }

    jQuery(document).ready(function($) {
        if ($('#woocommerce_wpadtwpg_payinvite_testmode').is(":checked")) {
            showTestInfo();
        } else {
            showMainInfo();
        }
        $('#woocommerce_wpadtwpg_payinvite_testmode').on('change', function() {
            if ($(this).is(":checked")) {
                showTestInfo();
            } else {
                showMainInfo();
            }
        });
    });
})(jQuery);