//show text field when "create new merchant" is selected from dropdown list
document.getElementById('ocr_creditor').addEventListener('change', function() {
    var create_mrcht_sect = document.getElementById('SDT_CreateMerchant');
    create_mrcht_sect.hidden = this.value !== 'Create New Merchant';
})

// Trigger the check on page load
window.onload = function() {
    var create_mrcht_sect = document.getElementById('SDT_CreateMerchant');
    var ocr_mrcht_value = document.getElementById('ocr_creditor').value;
    create_mrcht_sect.hidden = ocr_mrcht_value !== 'Create New Merchant';
};

//autocomplete
$(document).ready(function() {
    if (!($("#ocr_creditor").attr('readonly'))) {
        var selectedValue = '';
        $("#ocr_creditor").keyup(function() {
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

        $("#ocr_creditor").on('change', function() {
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

$('#ocr_attach').on('change', function() {
    previewImage(this, 'ocr_attach_preview')
})

//jQuery form validation
$("#ocr_type").on("input", function() {
    $(".ocr-type-err").remove();
});

$("#ocr_date").on("input", function() {
    $(".ocr-date-err").remove();
});

$("#ocr_creditor").on("input", function() {
    $(".ocr-mrcht-err").remove();
});

$("#creditor_other").on("input", function() {
    $(".creditor-other-err").remove();
});

$("#ocr_amt").on("input", function() {
    $(".ocr-amt-err").remove();
});

$("#ocr_desc").on("input", function() {
    $(".ocr-desc-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var type_chk = 0;
    var date_chk = 0;
    var amt_chk = 0;
    var desc_chk = 0;
    var cred_chk = 0;
    var cred_other_chk = 0;

    if (($('#ocr_type').val() === '' || $('#ocr_type').val() === null || $('#ocr_type')
            .val() === undefined)) {
        type_chk = 0;
        $("#ocr_type").after(
            '<span class="error-message ocr-type-err">Type is required!</span>');
    } else {
        $(".ocr-type-err").remove();
        type_chk = 1;
    }

    if (($('#ocr_date').val() === '' || $('#ocr_date').val() === null || $('#ocr_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#ocr_date").after(
            '<span class="error-message ocr-date-err">Date is required!</span>');
    } else {
        $(".ocr-date-err").remove();
        date_chk = 1;
    }

    if (($('#ocr_creditor').val() === '' || $('#ocr_creditor').val() === null || $('#ocr_creditor')
            .val() === undefined)) {
        cred_chk = 0;
        $("#ocr_creditor").after(
            '<span class="error-message ocr-cred-err">Debtor is required!</span>');
    } else {
        $(".ocr-cred-err").remove();
        cred_chk = 1;
    }

    if ($('#ocr_creditor').val() === 'Create New Merchant') {
        if ($('#creditor_other').val() === '' || $('#creditor_other').val() === null || $('#creditor_other').val() === undefined) {
            cred_other_chk = 0;
            $("#creditor_other").after(
                '<span class="error-message creditor-other-err">Other Merchant Name is required!</span>');
        } else {
            $(".creditor-other-err").remove();
            cred_other_chk = 1;
        }
    } else {
        cred_other_chk = 1;
    }

    if ($('#ocr_desc').val() == '' || $('#ocr_desc').val() === null || $('#ocr_desc')
            .val() === undefined) {
        desc_chk = 0;
        $("#ocr_desc").after(
            '<span class="error-message ocr-desc-err">Description is required!</span>');
    } else {
        $(".ocr-desc-err").remove();
        desc_chk = 1;
    }

    if ($('#ocr_amt').val() == '' || $('#ocr_amt').val() === null || $('#ocr_amt')
            .val() === undefined) {
        amt_chk = 0;
        $("#ocr_amt").after(
            '<span class="error-message ocr-amt-err">Amount is required!</span>');
    } else {
        $(".ocr-amt-err").remove();
        amt_chk = 1;
    }

    if (type_chk == 1 && date_chk == 1 && cred_chk == 1 && amt_chk == 1 && desc_chk == 1 && cred_other_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})