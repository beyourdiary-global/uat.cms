var price_curr_chk = 0;
//autocomplete
$(document).ready(function () {
    document.getElementById('trackOrderBtn').addEventListener('click', function() {
        // Copy tracking number to clipboard
        var trackingNumber = this.getAttribute('data-tracking-id');
        navigator.clipboard.writeText(trackingNumber);    
    });
    //package
    if (!($("#sor_pkg").attr('disabled'))) {
        $("#sor_pkg").keyup(function () {
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
    //shopee buyer username
    if (!($("#sor_user").attr('disabled'))) {
        $("#sor_user").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'buyer_username', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= SHOPEE_CUST_INFO ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
    //pic
    if (!($("#sor_pic").attr('disabled'))) {
        $("#sor_pic").keyup(function () {
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
    if (!($("#sor_brand").attr('disabled'))) {
        $("#sor_brand").keyup(function () {
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

    $("#sor_acc").change(getAccountCurrency);
    $("#sor_pkg").change(getPkgBrand);
    $("#sor_acc, #sor_pkg").change(calculatePrice);
    $("#sor_serv, #sor_trans, #sor_ams").on('keyup', calculateFinalAmount);
    $("#sor_price, #sor_voucher, #sor_shipping, #sor_serv, #sor_trans, #sor_ams").change(calculateFinalAmount);
    $("#sor_serv, #sor_trans, #sor_ams").on('keyup', calculateFees);
    $("#sor_price, #sor_user_hidden, #sor_curr_hidden").change(calculateComm);

})

function getAccountCurrency() {
    var paramAcc = {
        search: $("#sor_acc").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= SHOPEE_ACC ?>',
        isFin: 1,
    };

    retrieveDBData(paramAcc, '<?= $SITEURL ?>', function (result) {
        if (result && result.length > 0) {
            var acc_curr = result[0]['currency_unit'];
            console.log('curr', acc_curr);
            $("#sor_curr_hidden").val(acc_curr);
            var paramCurr = {
                search: $("#sor_curr_hidden").val(),
                searchCol: 'id',
                searchType: '*',
                dbTable: '<?= CUR_UNIT ?>',
                isFin: 0,
            };
            retrieveDBData(paramCurr, '<?= $SITEURL ?>', function (result) {
                if (result && result.length > 0) {
                    $("#sor_curr").val(result[0]['unit']);
                }
            });
        } else {
            console.error('Error retrieving Shopee Account data');
        }
    });
}

function getPkgBrand() {
    var paramPkg = {
        search: $("#sor_pkg_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= PKG ?>',
        isFin: 0,
    };

    retrieveDBData(paramPkg, '<?= $SITEURL ?>', function (result) {
        if (result && result.length > 0) {
            var pkg_brand = result[0]['brand'];
            console.log('brand', pkg_brand);
            $("#sor_brand_hidden").val(pkg_brand);
            var paramBrand = {
                search: $("#sor_brand_hidden").val(),
                searchCol: 'id',
                searchType: '*',
                dbTable: '<?= BRAND ?>',
                isFin: 0,
            };
            retrieveDBData(paramBrand, '<?= $SITEURL ?>', function (result) {
                if (result && result.length > 0) {
                    $("#sor_brand").val(result[0]['name']);
                }
            });
        } else {
            console.error('Error retrieving Package data');
        }
    });
}

function calculateFees() {
    // Retrieve the values of each fee input field
    var serviceFee = parseFloat($("#sor_serv").val()) || 0;
    var transactionFee = parseFloat($("#sor_trans").val()) || 0;
    var amsCommissionFee = parseFloat($("#sor_ams").val()) || 0;

    // Calculate the total fees by summing up the individual fees
    var totalFees = serviceFee + transactionFee + amsCommissionFee;

    // Set the total fees value to the output field
    $("#sor_fees").val(totalFees.toFixed(2));
    $("#sor_fees").trigger("change");
}

function calculateFinalAmount() {
    // Retrieve the values of each input field
    var price = parseFloat($("#sor_price").val()) || 0;
    var voucher = parseFloat($("#sor_voucher").val()) || 0;
    var actualShipping = parseFloat($("#sor_shipping").val()) || 0;
    var fees = parseFloat($("#sor_fees").val()) || 0; // Total fees
    console.log("fees: ", fees);
    // Calculate the final amount
    var finalAmount = price - voucher - actualShipping - fees;

    // Set the final amount value to the output field
    $("#sor_final").val(finalAmount.toFixed(2));
    $("#sor_final").trigger("change");

}


function calculatePrice() {
    var paramPkg = {
        search: $("#sor_pkg_hidden").val(),
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
            $("#sor_price").val(pkg_price.toFixed(2));
            $("#sor_price").trigger("change");
            // Retrieve account currency
            var acc_curr = $("#sor_curr_hidden").val();
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
                                $("#sor_price").val(0);
                                $("#sor_price").after(
                                    '<span class="error-message sor-pricecurr-err">Currency rate not found! (Package Price: ' + pkgCurrName + ' ' + pkg_price + ')</span>');
                            }
                        })
                        // If data is not found, show an error message
                        console.error('No exchange rate found for the specified currencies.');
                    }
                })
            } else {
                console.log('Same Package Shopee Acc currency.');
                price_curr_chk = 1;

            }
        } else {
            console.error('Error retrieving Package data');
        }
    });
}
function calculateComm() {
    var price = parseFloat($("#sor_price").val()) || 0;
    var trans_rate = 0;
    var service_rate = 0;
    var tax_rate = 0;

    console.log("Calculating transaction fee...");

    // Retrieve transaction fee rate
    var paramSCR = {
        search: $("#sor_curr_hidden").val(),
        searchCol: 'currency_unit',
        searchType: '*',
        dbTable: '<?= SHOPEE_SCR_SETT ?>',
        isFin: 1,
    };

    // Fetch transaction fee rate
    retrieveDBData(paramSCR, '<?= $SITEURL ?>', function (result) {
        if (result && result.length > 0) {
            trans_rate = parseFloat(result[0]['transaction']);
            service_rate = parseFloat(result[0]['service']);
            console.log("Transaction fee rate:", trans_rate);

            // Retrieve tax percentage
            var paramBuyer = {
                search: $("#sor_user_hidden").val(),
                searchCol: 'id',
                searchType: '*',
                dbTable: '<?= SHOPEE_CUST_INFO ?>',
                isFin: 1,
            };

            // Fetch tax rate
            retrieveDBData(paramBuyer, '<?= $SITEURL ?>', function (result) {
                if (result && result.length > 0) {
                    var country = result[0]['country'];
                    console.log("Buyer country:", country);

                    var paramTax = {
                        search: country,
                        searchCol: 'country',
                        searchType: '*',
                        dbTable: '<?= TAX_SETT ?>',
                        isFin: 1,
                    };

                    // Fetch tax rate based on country
                    retrieveDBData(paramTax, '<?= $SITEURL ?>', function (result) {
                        if (result && result.length > 0) {
                            tax_rate = parseFloat(result[0]['percentage']);
                            console.log("Tax rate:", tax_rate);

                            // Calculate transaction fee
                            var transactionFee = price * (trans_rate / 100) * (tax_rate / 100);
                            var serviceFee = price * (service_rate / 100) * (tax_rate / 100);
                            console.log("Calculated transaction fee:", transactionFee);
                            console.log("Calculated service fee:", serviceFee);

                            $("#sor_trans").val(transactionFee.toFixed(2));
                            $("#sor_serv").val(serviceFee.toFixed(2));
                            $("#sor_trans").trigger("change");
                            $("#sor_serv").trigger("change");
                        }
                    });
                }
            });
        } else {
            console.log("Not found.");
        }
    });
}
//jQuery form validation
$("#sor_acc").on("input", function () {
    $(".sor-acc-err").remove();
});
$("#sor_curr").on("change", function () {
    $(".sor-curr-err").remove();
});

$("#sor_order").on("input", function () {
    $(".sor-order-err").remove();
});

$("#sor_pic").on("input", function () {
    $(".sor-pic-err").remove();
});

$("#sor_brand").on("keyup", function () {
    $(".sor-brand-err").remove();
});

$("#sor_pkg").on("input", function () {
    $(".sor-pkg-err").remove();
});

$("#sor_price").on("change", function () {
    $(".sor-price-err").remove();
});
$("#sor_price").on("change", function () {
    $(".sor-pricecurr-err").remove();
    price_curr_chk = 1;
});

$("#sor_pay").on("input", function () {
    $(".sor-pay-err").remove();
});

$("#sor_user").on("input", function () {
    $(".sor-user-err").remove();
});

$("#sor_final").on("input", function () {
    $(".sor-final-err").remove();
});



$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    var acc_chk = 0;
    var curr_chk = 0;
    var order_chk = 0;
    var date_chk = 0;
    var time_chk = 0;
    var pkg_chk = 0;
    var brand_chk = 0;
    var user_chk = 0;
    var pay_chk = 0;
    var pic_chk = 0;
    var price_chk = 0;
    var final_chk = 0;

    if ($('#sor_acc').val() === '' || $('#sor_acc').val() === null || $('#sor_acc')
        .val() === undefined) {
        acc_chk = 0;
        $("#sor_acc").after(
            '<span class="error-message sor-acc-err">Shopee Account is required!</span>');
    } else {
        $(".sor-acc-err").remove();
        acc_chk = 1;
    }

    if (($('#sor_curr').val() === '' || $('#sor_curr').val() === null || $('#sor_curr')
        .val() === undefined)) {
        curr_chk = 0;
        $("#sor_curr").after(
            '<span class="error-message sor-curr-err">Currency is required!</span>');
    } else {
        $(".sor-curr-err").remove();
        curr_chk = 1;
    }

    if (($('#sor_order').val() === '' || $('#sor_order').val() === null || $('#sor_order')
        .val() === undefined)) {
        order_chk = 0;
        $("#sor_order").after(
            '<span class="error-message sor-order-err">Order ID is required!</span>');
    } else {
        $(".sor-order-err").remove();
        order_chk = 1;
    }

    if (($('#sor_date').val() === '' || $('#sor_date').val() === null || $('#sor_date')
        .val() === undefined)) {
        date_chk = 0;
        $("#sor_date").after(
            '<span class="error-message sor-date-err">Date is required!</span>');
    } else {
        $(".sor-date-err").remove();
        date_chk = 1;
    }

    if (($('#sor_time').val() === '' || $('#sor_time').val() === null || $('#sor_time')
        .val() === undefined)) {
        time_chk = 0;
        $("#sor_time").after(
            '<span class="error-message sor-time-err">Time is required!</span>');
    } else {
        $(".sor-time-err").remove();
        time_chk = 1;
    }

    if (($('#sor_pic_hidden').val() === '' || $('#sor_pic_hidden').val() == '0' || $('#sor_pic_hidden').val() === null || $('#sor_pic_hidden')
        .val() === undefined)) {
        pic_chk = 0;
        $("#sor_pic").after(
            '<span class="error-message sor-pic-err">Sales Person-In-Charge is required!</span>');
    } else {
        $(".sor-pic-err").remove();
        pic_chk = 1;
    }

    if (($('#sor_user_hidden').val() === '' || $('#sor_user_hidden').val() == '0' || $('#sor_pic_hidden').val() === null || $('#sor_user_hidden')
        .val() === undefined)) {
        user_chk = 0;
        $("#sor_user").after(
            '<span class="error-message sor-user-err">Buyer Username is required!</span>');
    } else {
        $(".sor-user-err").remove();
        user_chk = 1;
    }

    if (($('#sor_brand_hidden').val() == '' || $('#sor_brand_hidden').val() == '0' || $('#sor_brand_hidden').val() === null || $('#sor_brand_hidden')
        .val() === undefined)) {
        brand_chk = 0;
        $("#sor_brand").after(
            '<span class="error-message sor-brand-err">Brand is required!</span>');
    } else {
        $(".sor-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#sor_pkg_hidden').val() == '' || $('#sor_pkg_hidden').val() == '0' || $('#sor_pkg_hidden').val() === null || $('#sor_pkg_hidden')
        .val() === undefined)) {
        pkg_chk = 0;
        $("#sor_pkg").after(
            '<span class="error-message sor-pkg-err">Package is required!</span>');
    } else {
        $(".sor-pkg-err").remove();
        pkg_chk = 1;
    }

    if (($('#sor_price').val() == '' || $('#sor_price').val() == '0' || $('#sor_price').val() === null || $('#sor_price')
        .val() === undefined)) {
        price_chk = 0;
        $("#sor_price").after(
            '<span class="error-message sor-price-err">Price is required!</span>');
    } else {
        $(".sor-price-err").remove();
        price_chk = 1;
    }

    if (($('#sor_pay').val() == '' || $('#sor_pay').val() == '0' || $('#sor_pay').val() === null || $('#sor_pay')
        .val() === undefined)) {
        pay_chk = 0;
        $("#sor_pay").after(
            '<span class="error-message sor-pay-err">Buyer Payment Method is required!</span>');
    } else {
        $(".sor-pay-err").remove();
        pay_chk = 1;
    }
    if (($('#sor_final').val() == '' || $('#sor_final').val() == '0' || $('#sor_final').val() === null || $('#sor_final')
        .val() === undefined)) {
        final_chk = 0;
        $("#sor_final").after(
            '<span class="error-message sor-final-err">Final Amount is required!</span>');
    } else {
        $(".sor-final-err").remove();
        final_chk = 1;
    }

    if (acc_chk == 1 && price_curr_chk == 1 && curr_chk == 1 && order_chk == 1 && date_chk == 1 && time_chk == 1 && pkg_chk == 1 && brand_chk == 1 && user_chk == 1 && pay_chk == 1 && pic_chk == 1 && price_chk == 1 && final_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})