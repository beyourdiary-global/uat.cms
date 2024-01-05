//show text field when "create new merchant" is selected from dropdown list
document.getElementById('sdt_debtors').addEventListener('change', function() {
    var create_mrcht_sect = document.getElementById('SDT_CreateMerchant');
    create_mrcht_sect.hidden = this.value !== 'Create New Merchant';
})

// Trigger the check on page load
window.onload = function() {
    var create_mrcht_sect = document.getElementById('SDT_CreateMerchant');
    var sdt_mrcht_value = document.getElementById('sdt_debtors').value;
    create_mrcht_sect.hidden = sdt_mrcht_value !== 'Create New Merchant';
};

//autocomplete
$(document).ready(function() {

    if (!($("#sdt_debtors").attr('disabled'))) {
        var selectedValue = '';
        $("#sdt_debtors").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= MERCHANT ?>', // json filename (generated when login)
                addSelection: 'Create New Merchant'
            }
            console.log(param["elementID"]);
            searchInput(param, '<?= $SITEURL ?>');
        });

        $("#sdt_debtors").on('change', function() {
        selectedValue = $(this).val();
        var create_mrcht_sect = document.getElementById('SDT_CreateMerchant');

        // Check if the selected value is 'Create New Merchant'
        if (selectedValue == 'Create New Merchant') {
            create_mrcht_sect.hidden = false; // Show INVTR_CreateMerchant
        } else {
            create_mrcht_sect.hidden = true; // Hide INVTR_CreateMerchant
        }
        });
    }
})

$('#sdt_attach').on('change', function() {
    previewImage(this, 'sdt_attach_preview')
})

//jQuery form validation
$("#sdt_type").on("input", function() {
    $(".sdt-type-err").remove();
});

$("#sdt_date").on("input", function() {
    $(".sdt-date-err").remove();
});

$("#sdt_debtors").on("input", function() {
    $(".sdt-mrcht-err").remove();
});

$("#debtors_other").on("input", function() {
    $(".debtors-other-err").remove();
});

$("#sdt_amt").on("input", function() {
    $(".sdt-amt-err").remove();
});

$("#sdt_desc").on("input", function() {
    $(".sdt-desc-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var type_chk = 0;
    var date_chk = 0;
    var amt_chk = 0;
    var desc_chk = 0;
    var debt_chk = 0;
    var debt_other_chk = 0;

    if (($('#sdt_type').val() === '' || $('#sdt_type').val() === null || $('#sdt_type')
            .val() === undefined)) {
        type_chk = 0;
        $("#sdt_type").after(
            '<span class="error-message sdt-type-err">Type is required!</span>');
    } else {
        $(".sdt-type-err").remove();
        type_chk = 1;
    }

    if (($('#sdt_date').val() === '' || $('#sdt_date').val() === null || $('#sdt_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#sdt_date").after(
            '<span class="error-message sdt-date-err">Date is required!</span>');
    } else {
        $(".sdt-date-err").remove();
        date_chk = 1;
    }

    if (($('#sdt_debtors').val() === '' || $('#sdt_debtors').val() === null || $('#sdt_debtors')
            .val() === undefined)) {
        debt_chk = 0;
        $("#sdt_debtors").after(
            '<span class="error-message sdt-debt-err">Debtor is required!</span>');
    } else {
        $(".sdt-debt-err").remove();
        debt_chk = 1;
    }

    if ($('#sdt_debtors').val() === 'Create New Merchant') {
        if ($('#debtors_other').val() === '' || $('#debtors_other').val() === null || $('#debtors_other').val() === undefined) {
            debt_other_chk = 0;
            $("#debtors_other").after(
                '<span class="error-message debtors-other-err">Other Merchant Name is required!</span>');
        } else {
            $(".debtors-other-err").remove();
            debt_other_chk = 1;
        }
    } else {
        debt_other_chk = 1;
    }

    if ($('#sdt_desc').val() == '' || $('#sdt_desc').val() === null || $('#sdt_desc')
            .val() === undefined) {
        desc_chk = 0;
        $("#sdt_desc").after(
            '<span class="error-message sdt-desc-err">Description is required!</span>');
    } else {
        $(".sdt-desc-err").remove();
        desc_chk = 1;
    }

    if ($('#sdt_amt').val() == '' || $('#sdt_amt').val() === null || $('#sdt_amt')
            .val() === undefined) {
        amt_chk = 0;
        $("#sdt_amt").after(
            '<span class="error-message sdt-amt-err">Amount is required!</span>');
    } else {
        $(".sdt-amt-err").remove();
        amt_chk = 1;
    }

    if (type_chk == 1 && date_chk == 1 && debt_chk == 1 && amt_chk == 1 && desc_chk == 1 && debt_other_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})