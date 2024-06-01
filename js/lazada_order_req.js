//save new customer id
function toggleNewCustomerSection() {
    var newCustomerSection = document.getElementById("new_customer_section");
    if (newCustomerSection.style.display === "none") {
        newCustomerSection.style.display = "block";
    } else {
        newCustomerSection.style.display = "none";
    }
}

//autocomplete
$(document).ready(function() {
    function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    var act = getParameterByName('act');
    var trackOrderBtn = document.getElementById('trackOrderBtn');
    if (act !== 'I' && trackOrderBtn) {
        trackOrderBtn.addEventListener('click', function() {
            // Copy tracking number to clipboard
            var trackingNumber = this.getAttribute('data-tracking-id');
            navigator.clipboard.writeText(trackingNumber);
        });
    }
     //lazada_acc
     if (!($("#lor_lazada_acc").attr('disabled'))) {
        $("#lor_lazada_acc").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= LAZADA_ACC ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    } 
    if (!($("#lor_sales_pic").attr('disabled'))) {
        $("#lor_sales_pic").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= USR_USER ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
     //Currency unit
     if (!($("#lor_curr_unit").attr('disabled'))) {
        $("#lor_curr_unit").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'unit', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= CUR_UNIT ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    
    //lzd country
    if (!($("#lor_lzd_country").attr('disabled'))) {
        $("#lor_lzd_country").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    //customer id
    if (!($("#lor_cust_id").attr('disabled'))) {
        $("#lor_cust_id").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'lcr_id', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= LAZADA_CUST_RCD ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    //country
    if (!($("#lor_country").attr('disabled'))) {
        $("#lor_country").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    //brand
    if (!($("#lor_brand").attr('disabled'))) {
        $("#lor_brand").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= BRAND ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

     //series
     if (!($("#lor_series").attr('disabled'))) {
        $("#lor_series").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= BRD_SERIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

     //package
     if (!($("#lor_pkg").attr('disabled'))) {
        $("#lor_pkg").keyup(function () {
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
    $("#lor_lazada_acc").change(calculateCurrUnit); 
    $("#lor_lazada_acc").change(calculateCountry);
    $("#lor_lazada_acc, #lor_pkg").change(calculatePrice);
})

//jQuery fcbm validation
$("#lor_lazada_acc").on("input", function() {
    $(".lor-lazada-acc-err").remove();
});

$("#lor_curr_unit").on("input", function() {
    $(".lor-curr-unit-err").remove();
});

$("#lor_lzd_country").on("input", function() {
    $(".lor-lzd_country-err").remove();
});

$("#lor_cust_id").on("input", function() {
    $(".lor-cust-id-err").remove();
});

$("#lor_cust_name").on("input", function() {
    $(".lor-cust-name-err").remove();
});

$("#lor_cust_email").on("input", function() {
    $(".lor-cust-email-err").remove();
});

$("#lor_cust_phone").on("input", function() {
    $(".lor-cust-phone-err").remove();
});

$("#lor_country").on("input", function() {
    $(".lor-country-err").remove();
});

$("#lor_oder_number").on("input", function() {
    $(".lor-oder-number-err").remove();
});

$("#lor_sales_pic").on("input", function() {
    $(".lor-sales-pic-err").remove();
});

$("#lor_ship_rec_name").on("input", function() {
    $(".lor-ship-rec-name-err").remove();
});

$("#lor_ship_rec_address").on("input", function() {
    $(".lor-ship-rec-address-err").remove();
});

$("#lor_ship_rec_contact").on("input", function() {
    $(".lor-ship-rec-contact-err").remove();
});

$("#lor_brand").on("input", function() {
    $(".lor-brand-err").remove();
});

$("#lor_brand").on("input", function() {
    $(".lor-brand-err").remove();
});

$("#lor_series").on("input", function() {
    $(".lor-series-err").remove();
});

$("#lor_pkg").on("input", function() {
    $(".lor-pkg-err").remove();
});

$("#lor_brand").on("input", function() {
    $(".lor-item-price-credit-err").remove();
});

$("#lor_commision").on("input", function() {
    $(".lor-commision-err").remove();
});

$("#lor_other_discount").on("input", function() {
    $(".lor-other-discount-err").remove();
});

$("#lor_pay_fee").on("input", function() {
    $(".lor-pay-fee-err").remove();
});

$("#lor_final_income").on("input", function() {
    $(".lor-final-income-err").remove();
});

$("#lor_pay_meth").on("input", function() {
    $(".lor-pay-meth-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    var lazada_acc_chk = 0;
    var curr_unit_chk = 0;
    var lzd_country_chk = 0;
    var cust_id_chk = 0;
    var cust_name_chk = 0;
    var cust_email_chk = 0;
    var cust_phone_chk = 0;
    var oder_number_chk = 0;
    var sales_pic_chk = 0;
    var ship_rec_name_chk = 0;
    var ship_rec_address_chk = 0;
    var ship_rec_contact_chk = 0;
    var brand_chk = 0;
    var series_chk = 0;
    var pkg_chk = 0;
    var item_price_credit_chk = 0;
    var commision_chk = 0;
    var other_discount_chk = 0;
    var pay_fee_chk = 0;
    var final_income_chk = 0;
    var pay_meth_chk = 0;


    if (($('#lor_lazada_acc_hidden').val() === ''  || $('#lor_lazada_acc_hidden').val() == '0' || $('#lor_lazada_acc_hidden').val() === null || $('#lor_lazada_acc_hidden')
            .val() === undefined)) {
        lazada_acc_chk = 0;
        $("#lor_lazada_acc").after(
            '<span class="error-message lor-lazada-acc-err">Lazada Account is required!</span>');
    } else {
        $(".lor-lazada-acc-err").remove();
        lazada_acc_chk = 1;
    }

     if (($('#lor_curr_unit_hidden').val() == '' || $('#lor_curr_unit_hidden').val() == '0' || $('#lor_curr_unit_hidden').val() === null || $('#lor_curr_unit_hidden')
            .val() === undefined)) {
        curr_unit_chk = 0;
        $("#lor_curr_unit").after(
            '<span class="error-message lor-curr-unit-err">Currency Unit is required!</span>');
    } else {
        $(".lor-curr-unit-err").remove();
        curr_unit_chk = 1;
    }

     if (($('#lor_lzd_country_hidden').val() == '' || $('#lor_lzd_country_hidden').val() == '0' || $('#lor_lzd_country_hidden').val() === null || $('#lor_lzd_country_hidden')
            .val() === undefined)) {
        lzd_country_chk = 0;
        $("#lor_lzd_country").after(
            '<span class="error-message lor-lzd-country-err">Country is required!</span>');
    } else {
        $(".lor-lzd-country-name-err").remove();
        lzd_country_chk = 1;
    }
    
    if (($('#lor_cust_id_hidden').val() == '' || $('#lor_cust_id_hidden').val() == '0' || $('#lor_cust_id_hidden').val() === null || $('#lor_cust_id_hidden')
            .val() === undefined)) {
        cust_id_chk = 0;
        $("#lor_cust_id").after(
            '<span class="error-message lor-cust-id-err">Customer ID is required!</span>');
    } else {
        $(".lor-cust-id-err").remove();
        cust_id_chk = 1;
    }

    if (($('#lor_cust_name').val() == '' || $('#lor_cust_name').val() == '0' || $('#lor_cust_name').val() === null || $('#lor_cust_name')
            .val() === undefined)) {
        cust_name_chk = 0;
        $("#lor_cust_name").after(
            '<span class="error-message lor-cust-name-err">Customer Name is required!</span>');
    } else {
        $(".lor-cust-name-err").remove();
        cust_name_chk = 1;
    }

    if (($('#lor_cust_email').val() == '' || $('#lor_cust_email').val() == '0' || $('#lor_cust_email').val() === null || $('#lor_cust_email')
            .val() === undefined)) {
        cust_email_chk = 0;
        $("#lor_cust_email").after(
            '<span class="error-message lor-cust-email-err">Customer Email is required!</span>');
    } else {
        $(".lor-cust-email-err").remove();
        cust_email_chk = 1;
    }

    if (($('#lor_cust_phone').val() == '' || $('#lor_cust_phone').val() == '0' || $('#lor_cust_phone').val() === null || $('#lor_cust_phone')
            .val() === undefined)) {
        cust_phone_chk = 0;
        $("#lor_cust_phone").after(
            '<span class="error-message lor-cust-phone-err">Customer Phone is required!</span>');
    } else {
        $(".lor-cust-phone-err").remove();
        cust_phone_chk = 1;
    }

    if (($('#lor_country_hidden').val() == '' || $('#lor_country_hidden').val() == '0' || $('#lor_country_hidden').val() === null || $('#lor_country_hidden')
            .val() === undefined)) {
        country_chk = 0;
        $("#lor_country").after(
            '<span class="error-message lor-country-err">Country is required!</span>');
    } else {
        $(".lor-country-err").remove();
        country_chk = 1;
    }

    if (($('#lor_oder_number').val() == '' || $('#lor_oder_number').val() == '0' || $('#lor_oder_number').val() === null || $('#lor_oder_number')
            .val() === undefined)) {
        oder_number_chk = 0;
        $("#lor_oder_number").after(
            '<span class="error-message lor-oder-number-err">Order Number is required!</span>');
    } else {
        $(".lor-oder-number-err").remove();
        oder_number_chk = 1;
    }

    if (($('#lor_sales_pic').val() == '' || $('#lor_sales_pic').val() == '0' || $('#lor_sales_pic').val() === null || $('#lor_sales_pic')
            .val() === undefined)) {
        sales_pic_chk = 0;
        $("#lor_sales_pic").after(
            '<span class="error-message lor-sales-pic-err">Sales Person In Chargeis required!</span>');
    } else {
        $(".lor-sales-pic-err").remove();
        sales_pic_chk = 1;
    }

    if (($('#lor_ship_rec_name').val() == '' || $('#lor_ship_rec_name').val() == '0' || $('#lor_ship_rec_name').val() === null || $('#lor_ship_rec_name')
            .val() === undefined)) {
        ship_rec_name_chk = 0;
        $("#lor_ship_rec_name").after(
            '<span class="error-message lor-ship-rec-name-err">Shipping Receiver Name is required!</span>');
    } else {
        $(".lor-ship-rec-name-err").remove();
        ship_rec_name_chk = 1;
    }

    if (($('#lor_ship_rec_address').val() == '' || $('#lor_ship_rec_address').val() == '0' || $('#lor_ship_rec_address').val() === null || $('#lor_ship_rec_address')
            .val() === undefined)) {
        ship_rec_address_chk = 0;
        $("#lor_ship_rec_address").after(
            '<span class="error-message lor-ship-rec-address-err">Shipping Receiver Address is required!</span>');
    } else {
        $(".lor-ship-rec-address-err").remove();
        ship_rec_address_chk = 1;
    }

    if (($('#lor_ship_rec_contact').val() == '' || $('#lor_ship_rec_contact').val() == '0' || $('#lor_ship_rec_contact').val() === null || $('#lor_ship_rec_contact')
            .val() === undefined)) {
        ship_rec_contact_chk = 0;
        $("#lor_ship_rec_contact").after(
            '<span class="error-message lor-ship-rec-contact-err">Shipping Receiver Contact is required!</span>');
    } else {
        $(".lor-ship-rec-contacterr").remove();
        ship_rec_contact_chk = 1;
    }

    if (($('#lor_brand_hidden').val() == '' || $('#lor_brand_hidden').val() == '0' || $('#lor_brand_hidden').val() === null || $('#lor_brand_hidden')
            .val() === undefined)) {
        brand_chk = 0;
        $("#lor_brand").after(
            '<span class="error-message lor-brand-err">Brand is required!</span>');
    } else {
        $(".lor-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#lor_series_hidden').val() == '' || $('#lor_series_hidden').val() == '0' || $('#lor_series_hidden').val() === null || $('#lor_series_hidden')
            .val() === undefined)) {
        series_chk = 0;
        $("#lor_series").after(
            '<span class="error-message lor-series-err">Series is required!</span>');
    } else {
        $(".lor-series-err").remove();
        series_chk = 1;
    }

    if (($('#lor_pkg_hidden').val() == '' || $('#lor_pkg_hidden').val() == '0' || $('#lor_pkg_hidden').val() === null || $('#lor_pkg_hidden')
            .val() === undefined)) {
        pkg_chk = 0;
        $("#lor_pkg").after(
            '<span class="error-message lor-pkg-err">Package is required!</span>');
    } else {
        $(".lor-pkg-err").remove();
        pkg_chk = 1;
    }

     if (($('#lor_item_price_credit').val() == '' || $('#lor_item_price_credit').val() == '0' || $('#lor_item_price_credit').val() === null || $('#lor_item_price_credit')
            .val() === undefined)) {
        item_price_credit_chk = 0;
        $("#lor_item_price_credit").after(
            '<span class="error-message lor-item-price-credit-err">Item Price Credit is required!</span>');
    } else {
        $(".lor-item-price-credit-err").remove();
        item_price_credit_chk = 1;
    }

     if (($('#lor_commision').val() == '' || $('#lor_commision').val() == '0' || $('#lor_commision').val() === null || $('#lor_commision')
            .val() === undefined)) {
        commision_chk = 0;
        $("#lor_commision").after(
            '<span class="error-message lor-commision-err">Commission is required!</span>');
    } else {
        $(".lor-commision-err").remove();
        commision_chk = 1;
    }

     if (($('#lor_other_discount').val() == '' || $('#lor_other_discount').val() == '0' || $('#lor_other_discount').val() === null || $('#lor_other_discount')
            .val() === undefined)) {
        other_discount_chk = 0;
        $("#lor_other_discount").after(
            '<span class="error-message lor-other-discount-err">Other Discount is required!</span>');
    } else {
        $(".lor-other-discount-err").remove();
        other_discount_chk = 1;
    }

     if (($('#lor_pay_fee').val() == '' || $('#lor_pay_fee').val() == '0' || $('#lor_pay_fee').val() === null || $('#lor_pay_fee')
            .val() === undefined)) {
        pay_fee_chk = 0;
        $("#lor_pay_fee").after(
            '<span class="error-message lor-pay_fee-err">Payement Fee is required!</span>');
    } else {
        $(".lor-pay-fee-err").remove();
        pay_fee_chk = 1;
    }

     if (($('#lor_final_income').val() == '' || $('#lor_final_income').val() == '0' || $('#lor_final_income').val() === null || $('#lor_final_income')
            .val() === undefined)) {
                final_income_chk = 0;
        $("#lor_final_income").after(
            '<span class="error-message lor-final-income-err">Final Income is required!</span>');
    } else {
        $(".lor-final_income-err").remove();
        final_income_chk = 1;
    }

     if (($('#lor_pay_meth').val() == '' || $('#lor_pay_meth').val() == '0' || $('#lor_pay_meth').val() === null || $('#lor_pay_meth')
            .val() === undefined)) {
        pay_meth_chk = 0;
        $("#lor_pay_meth").after(
            '<span class="error-message lor-pay-meth-err">Payment Method is required!</span>');
    } else {
        $(".lor-pay-meth-contact-err").remove();
        pay_meth_chk = 1;
    }

    if (lazada_acc_chk == 1 && curr_unit_chk == 1 && lzd_country_chk == 1 && cust_id_chk == 1 && cust_name_chk == 1 && cust_email_chk == 1 && cust_phone_chk == 1 && country_chk == 1 && oder_number_chk == 1 && sales_pic_chk == 1 && ship_rec_name_chk == 1 && ship_rec_address_chk == 1 &&  ship_rec_contact_chk == 1 && brand_chk == 1 && series_chk == 1 && pkg_chk == 1 && item_price_credit_chk == 1 && commision_chk == 1 && other_discount_chk == 1 && pay_fee_chk == 1 && final_income_chk == 1 && pay_meth_chk == 1 )
        $(this).closest('fcbm').submit();
    else
        return false;

})

    //currency_unit
function calculateCurrUnit() {

    var paramLazada_acc = {
        search: $("#lor_lazada_acc_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= LAZADA_ACC ?>',
        isFin: 1,
    };

    retrieveDBData(paramLazada_acc, '<?= $SITEURL ?>', function (result) {
        getCurrUnit(result);
        $("#lor_curr_unit_hidden").val(result[0]['currency_unit']);
    });

    function getCurrUnit(result) {
        if (result && result.length > 0) {
            currency_unit = result[0]['currency_unit'];
            
                var paramCurrUnit = {
                    search: currency_unit,
                    searchCol: 'id',
                    searchType: '*',
                    dbTable: '<?= CUR_UNIT ?>',
                    isFin: 0,
                };

                retrieveDBData(paramCurrUnit, '<?= $SITEURL ?>', function (result) {
                    $("#lor_curr_unit").val(result[0]['unit']);
                });
            } else {
                console.error('Error retrieving agent data');
            }
        }
    }

       // country
function calculateCountry() {

    var paramLazada_acc = {
        search: $("#lor_lazada_acc_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= LAZADA_ACC ?>',
        isFin: 1,
    };

    retrieveDBData(paramLazada_acc , '<?= $SITEURL ?>', function (result) {
        getCountry(result);
        $("#lor_lzd_country_hidden").val(result[0]['country']);
    });

    function getCountry(result) {
        if (result && result.length > 0) {
            country = result[0]['country'];
            
                var paramCountry = {
                    search: country,
                    searchCol: 'id',
                    searchType: '*',
                    dbTable: '<?= COUNTRIES ?>',
                    isFin: 0,
                };

                retrieveDBData(paramCountry, '<?= $SITEURL ?>', function (result) {
                    $("#lor_lzd_country").val(result[0]['name']);
                });
            } else {
                console.error('Error retrieving data');
            }
        }
    }
    
    function calculatePrice() {
        var paramPkg = {
            search: $("#lor_pkg_hidden").val(),
            searchCol: 'id',
            searchType: '*',
            dbTable: '<?= PKG ?>',
            isFin: 0,
        };
    
        retrieveDBData(paramPkg, '<?= $SITEURL ?>', function (result) {
            if (result && result.length > 0) {
                var pkg_price = parseFloat(result[0]['price']);
                var pkg_curr = result[0]['currency_unit'];
                console.log('curr', pkg_curr);
                $("#lor_item_price_credit").val(pkg_price.toFixed(2));
                $("#lor_item_price_credit").trigger("change");
                // Retrieve account currency
                var acc_curr = $("#lor_curr_unit_hidden").val();
                console.log('Account Currency:', acc_curr);
                var pkgCurrName = '';
    
                // Compare account currency with package currency
                if (acc_curr !== pkg_curr) {
                    console.log('Currency mismatch: Account currency is different from package currency.');
                    var paramCurrencies = {
                        search: acc_curr,
                        searchCol: 'default_currency_unit` = ' + pkg_curr + ' AND `exchange_currency_unit',
                        searchType: '*',
                        dbTable: '<?= CURRENCIES ?>',
                        isFin: 0,
                    };
                    retrieveDBData(paramCurrencies, '<?= $SITEURL ?>', function (result) {
                        if (result && result.length > 0) {
                            var exchangeRate = parseFloat(result[0]['exchange_currency_rate']);
                            console.log(result);
                            var priceInAccountCurrency = pkg_price * exchangeRate;
                            console.log(pkg_price);
                            console.log('Price in account currency:', priceInAccountCurrency);
                            $("#sor_price").val(priceInAccountCurrency.toFixed(2));
                            $("#sor_price").trigger("change");
                        } else {
                            var paramPkgCurr = {
                                search: pkg_curr,
                                searchCol: 'id',
                                searchType: '*',
                                dbTable: '<?= CUR_UNIT ?>',
                                isFin: 0,
                            };
                            retrieveDBData(paramPkgCurr, '<?= $SITEURL ?>', function (result) {
                                if (result && result.length > 0) {
                                    pkgCurrName = result[0]['unit'];
                                    price_curr_chk = 0;
                                    $("#lor_item_price_credit").val(0);
                                    $("#lor_item_price_credit").after(
                                        '<span class="error-message sor-pricecurr-err">Currency rate not found! (Package Price: ' + pkgCurrName + ' ' + pkg_price + ')</span>');
                                }
                            })
                            // If data is not found, show an error message
                            console.error('No exchange rate found for the specified currencies.');
                        }
                    })
                } else {
                    console.log('Same Package Lazada Acc currency.');
                    price_curr_chk = 1;
    
                }
            } else {
                console.error('Error retrieving Package data');
            }
        });
    }