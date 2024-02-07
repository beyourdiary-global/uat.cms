var page = "<?= $pageTitle ?>";
var action = "<?php echo isset($act) ? $act : ''; ?>";

checkCurrentPage(page, action);
setButtonColor();
setAutofocus(action);
preloader(300, action);

//autocomplete
$(document).ready(function () {
    if (!($("#sat_shopee_acc").attr('disabled'))) {
        $("#sat_shopee_acc").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= SHOPEE_ACC ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    if (!($("#sat_curr").attr('disabled'))) {
        $("#sat_curr").keyup(function () {
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
    if (!($("#sat_pay").attr('disabled'))) {
        $("#sat_pay").keyup(function () {
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
    $("#sat_shopee_acc").change(calculateTax);
    $("#sat_amt").on('keyup', calculateTax);
})

//jQuery form validation
$("#sat_shopee_acc").on("input", function () {
    $(".sat-acc-err").remove();
});

$("#sat_order_id").on("input", function () {
    $(".sat-id-err").remove();
});

$("#sat_date").on("input", function () {
    $(".sat-date-err").remove();
});

$("#sat_curr").on("input", function () {
    $(".sat-curr-err").remove();
});

$("#sat_amt").on("input", function () {
    $(".sat-amt-err").remove();
});

$("#sat_subtotal").on("input", function () {
    $(".sat-subtotal-err").remove();
});

$("#sat_gst").on("input", function () {
    $(".sat-gst-err").remove();
});

$("#sat_pay").on("input", function () {
    $(".sat-pay-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var acc_chk = 0;
    var id_chk = 0;
    var date_chk = 0;
    var amt_chk = 0;
    var subtotal_chk = 0;
    var gst_chk = 0;
    var pay_chk = 0;

    if ($('#sat_shopee_acc').val() === '' || $('#sat_shopee_acc').val() === null || $('#sat_shopee_acc')
        .val() === undefined) {
        acc_chk = 0;
        $("#sat_shopee_acc").after(
            '<span class="error-message sat-acc-err">Account is required!</span>');
    } else {
        $(".sat-acc-err").remove();
        acc_chk = 1;
    }

    if (($('#sat_order_id').val() === '' || $('#sat_order_id').val() === null || $('#sat_order_id')
        .val() === undefined)) {
        id_chk = 0;
        $("#sat_order_id").after(
            '<span class="error-message sat-id-err">Order ID is required!</span>');
    } else {
        $(".sat-id-err").remove();
        id_chk = 1;
    }

    if (($('#sat_curr_hidden').val() === '' || $('#sat_curr_hidden').val() === null || $('#sat_curr_hidden')
        .val() === undefined)) {
        curr_chk = 0;
        $("#sat_curr").after(
            '<span class="error-message sat-curr-err">Currency is required!</span>');
    } else {
        $(".sat-curr-err").remove();
        curr_chk = 1;
    }

    if (($('#sat_date').val() === '' || $('#sat_date').val() === null || $('#sat_date')
        .val() === undefined)) {
        date_chk = 0;
        $("#sat_date").after(
            '<span class="error-message sat-date-err">Date is required!</span>');
    } else {
        $(".sat-date-err").remove();
        date_chk = 1;
    }

    if (($('#sat_amt').val() == '' || $('#sat_amt').val() == '0' || $('#sat_amt').val() === null || $('#sat_amt')
        .val() === undefined)) {
        amt_chk = 0;
        $("#sat_amt").after(
            '<span class="error-message sat-amt-err">Amount is required!</span>');
    } else {
        $(".sat-amt-err").remove();
        amt_chk = 1;
    }

    if (($('#sat_subtotal').val() == '' || $('#sat_subtotal').val() == '0' || $('#sat_subtotal').val() === null || $('#sat_subtotal')
        .val() === undefined)) {
        subtotal_chk = 0;
        $("#sat_subtotal").after(
            '<span class="error-message sat-subtotal-err">Subtotal is required!</span>');
    } else {
        $(".sat-subtotal-err").remove();
        subtotal_chk = 1;
    }

    if (($('#sat_gst').val() == '' || $('#sat_gst').val() == '0' || $('#sat_gst').val() === null || $('#sat_gst')
        .val() === undefined)) {
        gst_chk = 0;
        $("#sat_gst").after(
            '<span class="error-message sat-gst-err">GST is required!</span>');
    } else {
        $(".sat-amt-err").remove();
        gst_chk = 1;
    }

    if (($('#sat_pay').val() == '' || $('#sat_pay').val() == '0' || $('#sat_pay').val() === null || $('#sat_pay')
        .val() === undefined)) {
        pay_chk = 0;
        $("#sat_pay").after(
            '<span class="error-message sat-pay-err">Payment Method is required!</span>');
    } else {
        $(".sat-pay-err").remove();
        pay_chk = 1;
    }


    if (acc_chk == 1 && id_chk == 1 && date_chk == 1 && curr_chk == 1 && amt_chk == 1 && subtotal_chk == 1 && gst_chk == 1 && pay_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})

function calculateTax() {
    var country = '';
    var amtInput = $("#sat_amt");
    var subtotalInput = $("#sat_subtotal");
    var GSTInput = $("#sat_gst");
    var tax = 0.00;
    var tax_perc = 0.00;

    var paramAcc = {
        search: $("#sat_shopee_acc_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= SHOPEE_ACC ?>',
        isFin: 1,
    };
    retrieveDBData(paramAcc, '<?= $SITEURL ?>', function (result) {
        getTaxPercentage(result);
        getCurrency(result);
        $("#sat_curr_hidden").val(result[0]['currency_unit']);
    });

    function getCurrency(result) {
        if (result && result.length > 0) {
            curr = result[0]['currency_unit'];
            console.log(result[0]);
            var paramCurr = {
                search: curr,
                searchCol: 'id',
                searchType: '*',
                dbTable: '<?= CUR_UNIT ?>',
                isFin: 0,
            };

            retrieveDBData(paramCurr, '<?= $SITEURL ?>', function (result) {
                $("#sat_curr").val(result[0]['unit']);
                console.log('currency successful');
            });

        } else {
            console.error('Error retrieving Courier data');
        }
        
    }

    function getTaxPercentage(result) {
        if (result && result.length > 0) {
            country = result[0]['country'];

            var paramTaxSetting = {
                search: country,
                searchCol: 'country',
                searchType: '*',
                dbTable: '<?= TAX_SETT ?>',
                isFin: 1,
            };

            retrieveDBData(paramTaxSetting, '<?= $SITEURL ?>', function (result) {
                console.log(result);
                if (result && result.length > 0) {
                    if (result[0]['percentage'] !== undefined ) {
                        tax_perc = parseFloat(result[0]['percentage']);
                    }
                }
                GSTInput.val(tax_perc.toFixed(2)); //set  GST to tax percentage from tax settings table based on country
                handleTaxSettingData(result);
            });

        } else {
            console.error('Error retrieving Courier data');
        }
    }
    function handleTaxSettingData(result) {
        if (result && result.length > 0) {
            console.log('tax: ', tax_perc, '%');
        }

        var amount = parseFloat(amtInput.val()) || 0;

        if (amtInput.is(':focus')) {
            // User is editing Subtotal, calculate Total and update Tax
            var calculatedTotal = amount + (amount * tax_perc) / 100;
            subtotalInput.val(calculatedTotal.toFixed(2));
        }
    }
}