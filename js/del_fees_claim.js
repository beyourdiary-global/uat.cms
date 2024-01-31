//autocomplete
$(document).ready(function () {

    if (!($("#dfc_courier").attr('disabled'))) {
        $("#dfc_courier").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= COURIER ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });


    }
    if (!($("#dfc_curr").attr('disabled'))) {
        $("#dfc_curr").keyup(function () {
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

    //calculation for delivery fee
    $("#dfc_courier").change(calculateTax);

    $("#dfc_subtotal").on('keyup', calculateTax);
    $("#dfc_total").on('keyup', calculateTax);
})

//jquery form validation

$("#dfc_courier").on("input", function () {
    $(".dfc-courier-err").remove();
});
$("#dfc_curr").on("input", function () {
    $(".dfc-curr-err").remove();
});
$("#dfc_sub").on("input", function () {
    $(".dfc-sub-err").remove();
});
$("#dfc_total").on("input", function () {
    $(".dfc-total-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var courier_chk = 0;
    var currency_chk = 0;
    var subtotal_chk = 0;
    var total_chk = 0;
    var tax_chk = 0;

    if (($('#dfc_courier').val() === '' || $('#dfc_courier').val() === null || $('#dfc_courier')
        .val() === undefined)) {
        courier_chk = 0;
        $("#dfc_courier").after(
            '<span class="error-message dfc-courier-err">Courier is required!</span>');
    } else {
        $(".dfc-courier-err").remove();
        courier_chk = 1;
    }

    if (($('#dfc_curr').val() === '' || $('#dfc_curr').val() === null || $('#dfc_curr')
        .val() === undefined)) {
        currency_chk = 0;
        $("#dfc_curr").after(
            '<span class="error-message dfc-curr-err">Currency is required!</span>');
    } else {
        $(".dfc-curr-err").remove();
        currency_chk = 1;
    }

    if (($('#dfc_subtotal').val() == '' || $('#dfc_subtotal').val() == '0' || $('#dfc_subtotal').val() === null || $('#dfc_subtotal')
        .val() === undefined)) {
        subtotal_chk = 0;
        $("#dfc_subtotal").after(
            '<span class="error-message dfc-sub-err">Subtotal is required!</span>');
    } else {
        $(".dfc-sub-err").remove();
        subtotal_chk = 1;
    }
    if (($('#dfc_tax').val() == '' || $('#dfc_tax').val() == '0.00' || $('#dfc_subtotal').val() === null || $('#dfc_tax')
        .val() === undefined)) {
        tax_chk = 0;
        $("#dfc_tax").after(
            '<span class="error-message dfc-tax-err">Tax is required!</span>');
    } else {
        $(".dfc-sub-err").remove();
        tax_chk = 1;
    }

    if (($('#dfc_total').val() == '' || $('#dfc_total').val() == '0' || $('#dfc_total').val() === null || $('#dfc_total')
        .val() === undefined)) {
        total_chk = 0;
        $("#dfc_total").after(
            '<span class="error-message dfc-total-err">Total is required!</span>');
    } else {
        $(".dfc-total-err").remove();
        total_chk = 1;
    }

    if (courier_chk == 1 && currency_chk == 1 && subtotal_chk == 1 && tax_chk == 1 && total_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})


function calculateTax() {
    var country = '';
    var $subtotalInput = $("#dfc_subtotal");
    var $totalInput = $("#dfc_total");
    var $taxInput = $("#dfc_tax");

    var paramTaxable = {
        search: $("#dfc_courier_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= COURIER ?>',
        isFin: 0,
    };

    retrieveDBData(paramTaxable, '<?= $SITEURL ?>', function (result) {
        handleCourierData(result);
    });
    function handleCourierData(result) {
        if (result && result.length > 0) {
            var taxable = result[0]['taxable'];
            country = result[0]['country'];

            if (taxable == 'Y') {
                var paramTaxSetting = {
                    search: country,
                    searchCol: 'country',
                    searchType: '*',
                    dbTable: '<?= TAX_SETT ?>',
                    isFin: 1,
                };

                retrieveDBData(paramTaxSetting, '<?= $SITEURL ?>', function (result) {
                    handleTaxSettingData(result);
                });

            }
        } else {
            console.error('Error retrieving Courier data');
        }
    }
    function handleTaxSettingData(result) {
        var tax = 0.00;
        if (result && result.length > 0) {
            tax = result[0]['percentage'];
        }
        console.log('tax: ',tax, '%');
        var taxAmount = 0.00;
        var subtotal = parseFloat($subtotalInput.val()) || 0;
        var total = parseFloat($totalInput.val()) || 0;
        $taxInput.val(taxAmount.toFixed(2));
        if ($totalInput.is(':focus')) {
            // User is editing Total, calculate Subtotal and update Tax
            var calculatedSubtotal = total / (1 + tax / 100);
            $subtotalInput.val(calculatedSubtotal.toFixed(2));

            var taxAmount = total - (total / (1 + tax / 100));
            $taxInput.val(taxAmount.toFixed(2));
        }

        if ($subtotalInput.is(':focus')) {
            taxAmount = (subtotal * tax) / 100;
            // User is editing Subtotal, calculate Total and update Tax
            var calculatedTotal = subtotal + (subtotal * tax) / 100;
            $totalInput.val(calculatedTotal.toFixed(2));

            $taxInput.val(taxAmount.toFixed(2));
        }
    }



}