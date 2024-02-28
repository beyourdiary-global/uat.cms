//autocomplete
$(document).ready(function () {
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
    $("#sor_price, #sor_voucher, #sor_shipping, #sor_serv, #sor_trans, #sor_ams").on('change', calculateFinalAmount);
    $("#sor_serv, #sor_trans, #sor_ams").change(calculateFees);
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

            // Compare account currency with package currency
            if (acc_curr !== pkg_curr) {
                console.log('Currency mismatch: Account currency is different from package currency.');
                //get exchange rate from currencies table
                //default_currency_unit => pkg curr
                //exchange_currency_unit => shopee acc curr
                //SELECT exchange_currency_rate FROM $tbl WHERE default_currency_unit = '' AND exchange_currency_unit = ''
                // if found: // price = pkg_price * exchange_currency_rate
                // if !found: show error msg, enter own input
            }
        } else {
            console.error('Error retrieving Courier data');
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
// //jQuery form validation
// $("#sor_name").on("input", function() {
//     $(".for-name-err").remove();
// });

// $("#sor_link").on("input", function() {
//     $(".for-link-err").remove();
// });

// $("#sor_contact").on("input", function() {
//     $(".for-contact-err").remove();
// });

// $("#sor_pic").on("input", function() {
//     $(".for-pic-err").remove();
// });

// $("#sor_country").on("input", function() {
//     $(".for-country-err").remove();
// });

// $("#sor_brand").on("input", function() {
//     $(".for-brand-err").remove();
// });

// $("#sor_series").on("input", function() {
//     $(".for-series-err").remove();
// });

// $("#sor_pkg").on("input", function() {
//     $(".for-pkg-err").remove();
// });

// $("#sor_fbpage").on("input", function() {
//     $(".for-fbpage-err").remove();
// });

// $("#sor_channel").on("input", function() {
//     $(".for-channel-err").remove();
// });

// $("#sor_price").on("input", function() {
//     $(".for-price-err").remove();
// });

// $("#sor_pay_meth").on("input", function() {
//     $(".for-pay-err").remove();
// });

// $("#sor_rec_name").on("input", function() {
//     $(".for-rec-name-err").remove();
// });

// $("#sor_rec_ctc").on("input", function() {
//     $(".for-rec-ctc-err").remove();
// });

// $("#sor_rec_add").on("input", function() {
//     $(".for-rec-add-err").remove();
// });

// $("#sor_attach").on("input", function() {
//     $(".for-attach-err").remove();
// });


// $('.submitBtn').on('click', () => {
//     $(".error-message").remove();
//     var name_chk = 0;
//     var link_chk = 0;
//     var ctc_chk = 0;
//     var pic_chk = 0;
//     var country_chk = 0;
//     var brand_chk = 0;
//     var series_chk = 0;
//     var pkg_chk = 0;
//     var fbpage_chk = 0;
//     var channel_chk = 0;
//     var price_chk = 0;
//     var pay_chk = 0;
//     var rec_name_chk = 0;
//     var rec_ctc_chk = 0;
//     var rec_add_chk = 0;
//     var attach_chk = 0;

//     if ($('#sor_name').val() === '' || $('#sor_name').val() === null || $('#sor_name')
//         .val() === undefined) {
//         name_chk = 0;
//         $("#sor_name").after(
//             '<span class="error-message for-name-err">Name is required!</span>');
//     } else {
//         $(".for-name-err").remove();
//         name_chk = 1;
//     }

//     if (($('#sor_link').val() === '' || $('#sor_link').val() === null || $('#sor_link')
//             .val() === undefined)) {
//         link_chk = 0;
//         $("#sor_link").after(
//             '<span class="error-message for-link-err">Facebook Link is required!</span>');
//     } else {
//         $(".for-link-err").remove();
//         link_chk = 1;
//     }

//     if (($('#sor_contact').val() === '' || $('#sor_contact').val() === null || $('#sor_contact')
//             .val() === undefined)) {
//         ctc_chk = 0;
//         $("#sor_contact").after(
//             '<span class="error-message for-contact-err">Contact is required!</span>');
//     } else {
//         $(".for-contact-err").remove();
//         ctc_chk = 1;
//     }

//     if (($('#sor_pic_hidden').val() === ''  || $('#sor_pic_hidden').val() == '0' || $('#sor_pic_hidden').val() === null || $('#sor_pic_hidden')
//             .val() === undefined)) {
//         pic_chk = 0;
//         $("#sor_pic").after(
//             '<span class="error-message for-pic-err">Sales Person-In-Charge is required!</span>');
//     } else {
//         $(".for-pic-err").remove();
//         pic_chk = 1;
//     }


//     if (($('#sor_country_hidden').val() == '' || $('#sor_country_hidden').val() == '0' || $('#sor_country_hidden').val() === null || $('#sor_country_hidden')
//             .val() === undefined)) {
//         country_chk = 0;
//         $("#sor_country").after(
//             '<span class="error-message for-country-err">Country is required!</span>');
//     } else {
//         $(".for-country-err").remove();
//         country_chk = 1;
//     }

//     if (($('#sor_brand_hidden').val() == '' || $('#sor_brand_hidden').val() == '0' || $('#sor_brand_hidden').val() === null || $('#sor_brand_hidden')
//             .val() === undefined)) {
//         brand_chk = 0;
//         $("#sor_brand").after(
//             '<span class="error-message for-brand-err">Brand is required!</span>');
//     } else {
//         $(".for-brand-err").remove();
//         brand_chk = 1;
//     }

//     if (($('#sor_series_hidden').val() == '' || $('#sor_series_hidden').val() == '0' || $('#sor_series_hidden').val() === null || $('#sor_series_hidden')
//             .val() === undefined)) {
//         series_chk = 0;
//         $("#sor_series").after(
//             '<span class="error-message for-series-err">Series is required!</span>');
//     } else {
//         $(".for-series-err").remove();
//         series_chk = 1;
//     }

//     if (($('#sor_pkg_hidden').val() == '' || $('#sor_pkg_hidden').val() == '0' || $('#sor_pkg_hidden').val() === null || $('#sor_pkg_hidden')
//             .val() === undefined)) {
//         pkg_chk = 0;
//         $("#sor_pkg").after(
//             '<span class="error-message for-pkg-err">Package is required!</span>');
//     } else {
//         $(".for-pkg-err").remove();
//         pkg_chk = 1;
//     }

//     if (($('#sor_fbpage_hidden').val() == '' || $('#sor_fbpage_hidden').val() == '0' || $('#sor_fbpage_hidden').val() === null || $('#sor_fbpage_hidden')
//             .val() === undefined)) {
//         fbpage_chk = 0;
//         $("#sor_fbpage").after(
//             '<span class="error-message for-fbpage-err">Facebook Page is required!</span>');
//     } else {
//         $(".for-fbpage-err").remove();
//         fbpage_chk = 1;
//     }

//     if (($('#sor_channel_hidden').val() == '' || $('#sor_channel_hidden').val() == '0' || $('#sor_channel_hidden').val() === null || $('#sor_channel_hidden')
//             .val() === undefined)) {
//         channel_chk = 0;
//         $("#sor_channel").after(
//             '<span class="error-message for-channel-err">Channel is required!</span>');
//     } else {
//         $(".for-channel-err").remove();
//         channel_chk = 1;
//     }

//     if (($('#sor_price').val() == '' || $('#sor_price').val() == '0' || $('#sor_price').val() === null || $('#sor_price')
//             .val() === undefined)) {
//         price_chk = 0;
//         $("#sor_price").after(
//             '<span class="error-message for-price-err">Price is required!</span>');
//     } else {
//         $(".for-price-err").remove();
//         price_chk = 1;
//     }

//     if (($('#sor_pay_meth_hidden').val() == '' || $('#sor_pay_meth_hidden').val() == '0' || $('#sor_pay_meth_hidden').val() === null || $('#sor_pay_meth_hidden')
//             .val() === undefined)) {
//         pay_chk = 0;
//         $("#sor_pay_meth").after(
//             '<span class="error-message for-pay-err">Payment Method is required!</span>');
//     } else {
//         $(".for-pay-err").remove();
//         pay_chk = 1;
//     }

//     if (($('#sor_rec_name').val() == '' || $('#sor_rec_name').val() === null || $('#sor_rec_name')
//             .val() === undefined)) {
//         rec_name_chk = 0;
//         $("#sor_rec_name").after(
//             '<span class="error-message for-rec-name-err">Shipping Receiver Name is required!</span>');
//     } else {
//         $(".for-rec-name-err").remove();
//         rec_name_chk = 1;
//     }

//     if (($('#sor_rec_ctc').val() == '' || $('#sor_rec_ctc').val() === null || $('#sor_rec_ctc')
//             .val() === undefined)) {
//         rec_ctc_chk = 0;
//         $("#sor_rec_ctc").after(
//             '<span class="error-message for-rec-ctc-err">Shipping Receiver Contact is required!</span>');
//     } else {
//         $(".for-rec-ctc-err").remove();
//         rec_ctc_chk = 1;
//     }

//     if (($('#sor_rec_add').val() == '' || $('#sor_rec_add').val() === null || $('#sor_rec_add')
//             .val() === undefined)) {
//         rec_add_chk = 0;
//         $("#sor_rec_add").after(
//             '<span class="error-message for-rec-ctc-err">Shipping Receiver Address is required!</span>');
//     } else {
//         $(".for-rec-add-err").remove();
//         rec_add_chk = 1;
//     }

//     if (name_chk == 1 && link_chk == 1 && ctc_chk == 1 && pic_chk == 1 && country_chk == 1 && brand_chk == 1 && series_chk == 1 && pkg_chk == 1 && fbpage_chk == 1 && channel_chk == 1 && price_chk == 1 && pay_chk == 1 && rec_name_chk == 1 && rec_add_chk == 1 && rec_ctc_chk == 1 && attach_chk == 1)
//         $(this).closest('form').submit();
//     else
//         return false;

// })