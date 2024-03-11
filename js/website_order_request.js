//save new customer id
function toggleNewCustomerSection() {
    var newCustomerSection = document.getElementById("new_customer_section");
    if (newCustomerSection.style.display === "none") {
        newCustomerSection.style.display = "block";
    } else {
        newCustomerSection.style.display = "none";
    }
}

//total
document.getElementById("wor_price").addEventListener("input", calculateTotal);
document.getElementById("wor_shipping").addEventListener("input", calculateTotal);
document.getElementById("wor_discount").addEventListener("input", calculateTotal);

function calculateTotal() {
    var price = parseFloat(document.getElementById("wor_price").value) || 0;
    var shipping = parseFloat(document.getElementById("wor_shipping").value) || 0;
    var discount = parseFloat(document.getElementById("wor_discount").value) || 0;

    var total = price - shipping - discount;

    document.getElementById("wor_total").value = total.toFixed(2);
}

//show text field when "create new customer id" is selected from dropdown list
document.getElementById('wor_cust_id').addEventListener('change', function() {
    var create_cust_id_sect = document.getElementById('WOR_CreateCustID');
    create_cust_id_sect.hidden = this.value !== 'Create New Customer ID';
})

// Trigger the check on page load
window.onload = function() {
    var create_cust_id_sect = document.getElementById('WOR_CreateCustID');
    var wor_cust_id_value = document.getElementById('wor_cust_id').value;
    create_cust_id_sect.hidden = wor_cust_id_value !== 'Create New Customer ID';
};


//autocomplete
$(document).ready(function() {

    //package
    if (!($("#wor_pkg").attr('disabled'))) {
        $("#wor_pkg").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= PKG ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    //country
    if (!($("#wor_country").attr('disabled'))) {
        $("#wor_country").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'nicename', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    //currency
    if (!($("#wor_currency").attr('disabled'))) {
        $("#wor_currency").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'unit', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= CUR_UNIT ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    //brand
    if (!($("#brand").attr('disabled'))) {
        $("#brand").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= BRAND ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    //series
    if (!($("#series").attr('disabled'))) {
        $("#series").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= BRD_SERIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
  
     //payment method
     if (!($("#wor_pay").attr('disabled'))) {
        $("#wor_pay").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= FIN_PAY_METH ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    //cutomer ID
        if (!($("#wor_cust_id").attr('readonly'))) {
            $("#wor_cust_id").keyup(function() {
                var param = {
                    search: $(this).val(),
                    searchType: 'cust_id', // column of the table
                    elementID: $(this).attr('id'), // id of the input
                    hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                    dbTable: '<?= WEB_CUST_RCD ?>', // json filename (generated when login)
                }
                searchInput(param, '<?= $SITEURL ?>');
            });
    
        
        }
    })

//jQuery form validation
$("#wor_order_id").on("input", function() {
    $(".wor-order-id-err").remove();
});

$("#wor_brand").on("input", function() {
    $(".wor-brand-err").remove();
});

$("#wor_series").on("input", function() {
    $(".wor-series-err").remove();
});

$("#wor_pkg").on("input", function() {
    $(".wor-pkg-err").remove();
});

$("#wor_country").on("input", function() {
    $(".wor-country-err").remove();
});

$("#wor_currency").on("input", function() {
    $(".wor-currency-err").remove();
});

$("#wor_price").on("input", function() {
    $(".wor-price-err").remove();
});

$("#wor_shipping").on("input", function() {
    $(".wor-shipping-err").remove();
});

$("#wor_discount").on("input", function() {
    $(".wor-discount-err").remove();
});

$("#wor_pay").on("input", function() {
    $(".wor-pay-err").remove();
});

$("#wor_pic").on("input", function() {
    $(".wor-pic-err").remove();
});

$("#wor_cust_id").on("input", function() {
    $(".wor-cust-id-err").remove();
});

$("#wor_cust_name").on("input", function() {
    $(".wor-cust-name-err").remove();
});

$("#wor_cust_email").on("input", function() {
    $(".wor-cust-email-err").remove();
});

$("#wor_cust_birthday").on("input", function() {
    $(".wor-cust-birthday-err").remove();
});

$("#wor_shipping_name").on("input", function() {
    $(".wor-shipping-name-err").remove();
});

$("#wor_shipping_address").on("input", function() {
    $(".wor-shipping-address-err").remove();
});

$("#wor_shipping_contact").on("input", function() {
    $(".wor-shipping-contact-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    var order_id_chk = 0;
    var brand_chk = 0;
    var series_chk = 0;
    var pkg_chk = 0;
    var country_chk = 0;
    var currency_chk = 0;
    var price_chk = 0;
    var shipping_chk = 0;
    var discount_chk = 0;
    var pay_chk = 0;
    var pic_chk = 0;
    var cust_id_chk = 0;
    var customer_id_chk = 0;
    var cust_brand_chk = 0;
    var cust_series_chk = 0;
    var cust_ship_name_chk = 0;
    var cust_ship_address_chk = 0;
    var cust_ship_contact_chk = 0;
    var cust_name_chk = 0;
    var cust_email_chk = 0;
    var cust_birthday_chk = 0;
    var shipping_name_chk = 0;
    var shipping_address_chk = 0;
    var shipping_contact_chk = 0;

    if ($('#wor_order_id').val() === '' || $('#wor_order_id').val() === null || $('#wor_order_id')
        .val() === undefined) {
        order_id_chk = 0;
        $("#wor_order_id").after(
            '<span class="error-message wor-order-id-err">Order ID is required!</span>');
    } else {
        $(".wor-order-id-err").remove();
        order_id_chk = 1;
    }

    if ($('#wor_brand').val() === '' || $('#wor_brand').val() === null || $('#wor_brand')
        .val() === undefined) {
        brand_chk = 0;
        $("#wor_brand").after(
            '<span class="error-message wor-brand-err">Brand is required!</span>');
    } else {
        $(".wor-brand-err").remove();
        brand_chk = 1;
    }

    if ($('#wor_series').val() === '' || $('#wor_series').val() === null || $('#wor_series')
        .val() === undefined) {
        series_chk = 0;
        $("#wor_series").after(
            '<span class="error-message wor-series-id-err">Series is required!</span>');
    } else {
        $(".wor-series-err").remove();
        series_chk = 1;
    }
 
    if (($('#wor_pkg_hidden').val() == '' || $('#wor_pkg_hidden').val() == '0' || $('#wor_pkg_hidden').val() === null || $('#wor_pkg_hidden')
            .val() === undefined)) {
        pkg_chk = 0;
        $("#wor_pkg").after(
            '<span class="error-message wor-pkg-err">Package is required!</span>');
    } else {
        $(".wor-pkg-err").remove();
        pkg_chk = 1;
    }
    
    if (($('#wor_country_hidden').val() == '' || $('#wor_country_hidden').val() == '0' || $('#wor_country_hidden').val() === null || $('#wor_country_hidden')
            .val() === undefined)) {
        country_chk = 0;
        $("#wor_country").after(
            '<span class="error-message wor-country-err">Country is required!</span>');
    } else {
        $(".wor-country-err").remove();
        country_chk = 1;
    }

     if (($('#wor_currency_hidden').val() == '' || $('#wor_currency_hidden').val() == '0' || $('#wor_currency_hidden').val() === null || $('#wor_currency_hidden')
            .val() === undefined)) {
        currency_chk = 0;
        $("#wor_currency").after(
            '<span class="error-message wor-currency-err">Currency is required!</span>');
    } else {
        $(".wor-currency-err").remove();
        currency_chk = 1;
    }

    if (($('#wor_price').val() == '' || $('#wor_price').val() == '0' || $('#wor_price').val() === null || $('#wor_price')
            .val() === undefined)) {
        price_chk = 0;
        $("#wor_price").after(
            '<span class="error-message wor-price-err">Price is required!</span>');
    } else {
        $(".wor-price-err").remove();
        price_chk = 1;
    }

    if (($('#wor_shipping').val() == '' || $('#wor_price').val() == '0' || $('#wor_shipping').val() === null || $('#wor_shipping')
            .val() === undefined)) {
        shipping_chk = 0;
        $("#wor_shipping").after(
            '<span class="error-message wor-price-err">Shipping is required!</span>');
    } else {
        $(".wor-shipping-err").remove();
        shipping_chk = 1;
    }

    if (($('#wor_discount').val() == '' || $('#wor_discount').val() == '0' || $('#wor_discount').val() === null || $('#wor_discount')
            .val() === undefined)) {
        discount_chk = 0;
        $("#wor_discount").after(
            '<span class="error-message wor-discount-err">Discount is required!</span>');
    } else {
        $(".wor-discount-err").remove();
        discount_chk = 1;
    }

    if (($('#wor_pay_hidden').val() == '' || $('#wor_pay_hidden').val() == '0' || $('#wor_pay_hidden').val() === null || $('#wor_pay_hidden')
            .val() === undefined)) {
        pay_chk = 0;
        $("#wor_pay").after(
            '<span class="error-message wor-pay-err">Payment Method is required!</span>');
    } else {
        $(".wor-pay-err").remove();
        pay_chk = 1;
    }

    if (($('#wor_pic').val() == '' || $('#wor_pic').val() == '0' || $('#wor_pic').val() === null || $('#wor_pic')
            .val() === undefined)) {
        pic_chk = 0;
        $("#wor_pic").after(
            '<span class="error-message wor-pic-err">Person In Charge is required!</span>');
    } else {
        $(".wor-pic-err").remove();
        pic_chk = 1;
    }

    if (($('#wor_cust_id_hidden').val() == '' || $('#wor_cust_id_hidden').val() == '0' || $('#wor_cust_id_hidden').val() === null || $('#wor_cust_id_hidden')
            .val() === undefined)) {
        cust_id_chk = 0;
        $("#wor_cust_id").after(
            '<span class="error-message wor-cust-id-err">Customer ID is required!</span>');
    } else {
        $(".wor-cust-id-err").remove();
        cust_id_chk = 1;
    }

    if ($('#wor_customer_id').val() === 'Create New Customer ID') {
        if ($('#wor_customer_id').val() === '' || $('#wor_customer_id').val() === null || $('#wor_customer_id').val() === undefined) {
            customer_id_chk = 0;
            $("#wor_customer_id").after(
                '<span class="error-message customer-id-err">Customer ID is required!</span>');
        } else {
            $(".customer-id-err").remove();
            customer_id_chk = 1;
        }
    } else {
        customer_id_chk = 1;
    }
    
    if ($('#wor_cust_brand').val() === 'Create New Customer ID') {
        if (($('#wor_cust_brand_hidden').val() == '' || $('#wor_cust_brand_hidden').val() == '0' || $('#wor_cust_brand_hidden').val() === null || $('#wor_cust_brand_hidden')
            .val() === undefined)) {
            cust_brand_chk = 0;
            $("#wor_cust_brand").after(
                '<span class="error-message cust-brand-err">Brand is required!</span>');
        } else {
            $(".cust-brand-err").remove();
            cust_brand_chk = 1;
        }
    } else {
        cust_brand_chk = 1;
    }

    if ($('#wor_cust_series').val() === 'Create New Customer ID') {
        if (($('#wor_cust_series_hidden').val() == '' || $('#wor_cust_series_hidden').val() == '0' || $('#wor_cust_series_hidden').val() === null || $('#wor_cust_series_hidden')
        .val() === undefined)) {
            cust_series_chk = 0;
            $("#wor_cust_series").after(
                '<span class="error-message cust-series-err">Series is required!</span>');
        } else {
            $(".cust-series-err").remove();
            cust_series_chk = 1;
        }
    } else {
        cust_series_chk = 1;
    }

    if ($('#wor_cust_ship_name').val() === 'Create New Customer ID') {
        if ($('#wor_cust_ship_name').val() === '' || $('#wor_cust_ship_name').val() === null || $('#wor_cust_ship_name').val() === undefined) {
            cust_ship_name_chk = 0;
            $("#wor_cust_ship_name").after(
                '<span class="error-message cust-ship-name-err">Customer Shipping Name is required!</span>');
        } else {
            $(".cust-ship-name-err").remove();
            cust_ship_name_chk = 1;
        }
    } else {
        cust_ship_name_chk = 1;
    }

    if ($('#wor_cust_ship_address').val() === 'Create New Customer ID') {
        if ($('#wor_cust_ship_address').val() === '' || $('#wor_cust_ship_address').val() === null || $('#wor_cust_ship_address').val() === undefined) {
            cust_ship_address_chk = 0;
            $("#wor_cust_ship_address").after(
                '<span class="error-message cust-ship-address-err">Customer Shipping Address is required!</span>');
        } else {
            $(".cust-ship-address-err").remove();
            cust_ship_address_chk = 1;
        }
    } else {
        cust_ship_address_chk = 1;
    }

    if ($('#wor_cust_ship_contact').val() === 'Create New Customer ID') {
        if ($('#wor_cust_ship_contact').val() === '' || $('#wor_cust_ship_contact').val() === null || $('#wor_cust_ship_contact').val() === undefined) {
            cust_ship_contact_chk = 0;
            $("#wor_cust_ship_contact").after(
                '<span class="error-message cust-ship-contact-err">Customer Shipping Contact is required!</span>');
        } else {
            $(".cust-ship-contact-err").remove();
            cust_ship_contact_chk = 1;
        }
    } else {
        cust_ship_contact_chk = 1;
    }

    if (($('#wor_cust_name').val() == '' || $('#wor_cust_name').val() == '0' || $('#wor_cust_name').val() === null || $('#wor_cust_name')
            .val() === undefined)) {
        cust_name_chk = 0;
        $("#wor_cust_name").after(
            '<span class="error-message wor-cust-id-err">Customer Name is required!</span>');
    } else {
        $(".wor-cust-name-err").remove();
        cust_name_chk = 1;
    }

    if (($('#wor_cust_email').val() == '' || $('#wor_cust_email').val() == '0' || $('#wor_cust_email').val() === null || $('#wor_cust_email')
            .val() === undefined)) {
        cust_email_chk = 0;
        $("#wor_cust_email").after(
            '<span class="error-message wor-cust-email-err">Customer Email is required!</span>');
    } else {
        $(".wor-cust-email-err").remove();
        cust_email_chk = 1;
    }

     if (($('#wor_cust_birthday').val() == '' || $('#wor_cust_birthday').val() == '0' || $('#wor_cust_birthday').val() === null || $('#wor_cust_birthday')
            .val() === undefined)) {
        cust_birthday_chk = 0;
        $("#wor_cust_birthday").after(
            '<span class="error-message wor-cust-birthday-err">Customer Birthday is required!</span>');
    } else {
        $(".wor-cust-birthday-err").remove();
        cust_birthday_chk = 1;
    }

     if (($('#wor_shipping_name').val() == '' || $('#wor_shipping_name').val() == '0' || $('#wor_shipping_name').val() === null || $('#wor_shipping_name')
            .val() === undefined)) {
        shipping_name_chk = 0;
        $("#wor_shipping_name").after(
            '<span class="error-message wor-shipping-name-err">Shipping Name is required!</span>');
    } else {
        $(".wor-shipping-name-err").remove();
        shipping_name_chk = 1;
    }

     if (($('#wor_shipping_address').val() == '' || $('#wor_shipping_address').val() == '0' || $('#wor_shipping_address').val() === null || $('#wor_shipping_address')
            .val() === undefined)) {
        shipping_address_chk = 0;
        $("#wor_shipping_address").after(
            '<span class="error-message wor-shipping-name-err">Shipping Address is required!</span>');
    } else {
        $(".wor-shipping-address-err").remove();
        shipping_address_chk = 1;
    }

     if (($('#wor_shipping_contact').val() == '' || $('#wor_shipping_contact').val() == '0' || $('#wor_shipping_contact').val() === null || $('#wor_shipping_contact')
            .val() === undefined)) {
        shipping_contact_chk = 0;
        $("#wor_shipping_contact").after(
            '<span class="error-message wor-shipping-contact-err">Shipping Contact is required!</span>');
    } else {
        $(".wor-shipping-contact-err").remove();
        shipping_contact_chk = 1;
    }

    if (order_id_chk == 1 && brand_chk == 1 && series_chk == 1 && country_chk == 1 && currency_chk == 1 && price_chk == 1 && pkg_chk == 1 && pic_chk == 1 && shipping_chk == 1 && discount_chk == 1 && cust_id_chk == 1 && pay_chk == 1 && customer_id_chk == 1 && cust_brand_chk == 1 && cust_series_chk == 1 && cust_ship_name_chk == 1 && cust_ship_address_chk == 1 && cust_ship_contact_chk == 1 && cust_name_chk == 1 && cust_email_chk == 1 && cust_birthday_chk == 1 && shipping_name_chk == 1 && shipping_address_chk == 1 && shipping_contact_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})