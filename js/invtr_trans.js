//show text field when "create new merchant" is selected from dropdown list
document.getElementById('invtr_mrcht').addEventListener('change', function() {
    var create_mrcht_sect = document.getElementById('INVTR_CreateMerchant');
    create_mrcht_sect.hidden = this.value !== 'Create New Merchant';
})

// Trigger the check on page load
window.onload = function() {
    var create_mrcht_sect = document.getElementById('INVTR_CreateMerchant');
    var invtr_mrcht_value = document.getElementById('invtr_mrcht').value;
    create_mrcht_sect.hidden = invtr_mrcht_value !== 'Create New Merchant';
};

//autocomplete
$(document).ready(function() {

    if (!($("#invtr_mrcht").attr('readonly'))) {
        var selectedValue = '';
        $("#invtr_mrcht").keyup(function() {
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

        $("#invtr_mrcht").on('change', function() {
        selectedValue = $(this).val();
        var create_mrcht_sect = document.getElementById('INVTR_CreateMerchant');

        // Check if the selected value is 'Create New Merchant'
        if (selectedValue == 'Create New Merchant') {
            create_mrcht_sect.hidden = false; // Show INVTR_CreateMerchant
        } else {
            create_mrcht_sect.hidden = true; // Hide INVTR_CreateMerchant
        }
        });
    }
})

$('#invtr_attach').on('change', function() {
    previewImage(this, 'invtr_attach_preview')
})

//jQuery form validation
$("#invtr_date").on("input", function() {
    $(".invtr-date-err").remove();
});

$("#invtr_mrcht").on("input", function() {
    $(".invtr-mrcht-err").remove();
});

$("#mrcht_other").on("input", function() {
    $(".mrcht-other-err").remove();
});

$("#invtr_item").on("input", function() {
    $(".invtr-item-err").remove();
});

$("#invtr_amt").on("input", function() {
    $(".invtr-amt-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var date_chk = 0;
    var amt_chk = 0;
    var item_chk = 0;
    var mrcht_chk = 0;
    var mrcht_other_chk = 0;

    if (($('#invtr_date').val() === '' || $('#invtr_date').val() === null || $('#invtr_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#invtr_date").after(
            '<span class="error-message invtr-date-err">Date is required!</span>');
    } else {
        $(".invtr-date-err").remove();
        date_chk = 1;
    }

    if (($('#invtr_mrcht').val() === '' || $('#invtr_mrcht').val() === null || $('#invtr_mrcht')
            .val() === undefined)) {
        mrcht_chk = 0;
        $("#invtr_mrcht").after(
            '<span class="error-message invtr-mrcht-err">Merchant is required!</span>');
    } else {
        $(".invtr-mrcht-err").remove();
        mrcht_chk = 1;
    }

    if ($('#invtr_mrcht').val() === 'Create New Merchant') {
        if ($('#invtr_mrcht_other').val() === '' || $('#invtr_mrcht_other').val() === null || $('#invtr_mrcht_other').val() === undefined) {
            mrcht_other_chk = 0;
            $("#invtr_mrcht_other").after(
                '<span class="error-message mrcht-other-err">Other Merchant Name is required!</span>');
        } else {
            $(".mrcht-other-err").remove();
            mrcht_other_chk = 1;
        }
    } else {
        mrcht_other_chk = 1;
    }

    if ($('#invtr_item').val() == '' || $('#invtr_item').val() === null || $('#invtr_item')
            .val() === undefined) {
        item_chk = 0;
        $("#invtr_item").after(
            '<span class="error-message invtr-item-err">Item is required!</span>');
    } else {
        $(".invtr-item-err").remove();
        item_chk = 1;
    }

    if ($('#invtr_amt').val() == '' ||  $('#invtr_amt').val() == '0' || $('#invtr_amt').val() === null || $('#invtr_amt')
            .val() === undefined) {
        amt_chk = 0;
        $("#invtr_amt").after(
            '<span class="error-message invtr-amt-err">Amount is required!</span>');
    } else {
        $(".invtr-amt-err").remove();
        amt_chk = 1;
    }

    if (date_chk == 1 && mrcht_chk == 1 && amt_chk == 1 && item_chk == 1 && mrcht_other_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})