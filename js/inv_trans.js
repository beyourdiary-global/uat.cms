//show text field when "create new merchant" is selected from dropdown list
document.getElementById('ivs_mrcht').addEventListener('change', function() {
    var create_mrcht_sect = document.getElementById('createMerchant');
    create_mrcht_sect.hidden = this.value !== 'other';
})

// Trigger the check on page load
window.onload = function() {
    var create_mrcht_sect = document.getElementById('createMerchant');
    var ivs_mrcht_value = document.getElementById('ivs_mrcht').value;
    create_mrcht_sect.hidden = ivs_mrcht_value !== 'other';
};

$('#ivs_attach').on('change', function() {
    previewImage(this, 'ivs_attach_preview')
})

//jQuery form validation
$("#ivs_type").on("input", function() {
    $(".ivs-type-err").remove();
});

$("#ivs_date").on("input", function() {
    $(".ivs-date-err").remove();
});

$("#ivs_mrcht").on("input", function() {
    $(".ivs-mrcht-err").remove();
});

$("#mrcht_other").on("input", function() {
    $(".mrcht-other-err").remove();
});

$("#ivs_amt").on("input", function() {
    $(".ivs-amt-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var type_chk = 0;
    var date_chk = 0;
    var amt_chk = 0;
    var mrcht_chk = 0;
    var mrcht_other_chk = 0;

    if ($('#ivs_type').val() === '' || $('#ivs_type').val() === null || $('#ivs_type')
        .val() === undefined) {
        type_chk = 0;
        $("#ivs_type").after(
            '<span class="error-message ivs-type-err">Type is required!</span>');
    } else {
        $(".ivs-type-err").remove();
        type_chk = 1;
    }

    if (($('#ivs_date').val() === '' || $('#ivs_date').val() === null || $('#ivs_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#ivs_date").after(
            '<span class="error-message ivs-date-err">Date is required!</span>');
    } else {
        $(".ivs-date-err").remove();
        date_chk = 1;
    }

    if (($('#ivs_mrcht').val() === '' || $('#ivs_mrcht').val() === null || $('#ivs_mrcht')
            .val() === undefined)) {
        mrcht_chk = 0;
        $("#ivs_mrcht").after(
            '<span class="error-message ivs-mrcht-err">Merchant is required!</span>');
    } else {
        $(".ivs-mrcht-err").remove();
        mrcht_chk = 1;
    }

    if ($('#ivs_mrcht').val() === 'other') {
        if ($('#mrcht_other').val() === '' || $('#ivs_amt').val() === null || $('#ivs_amt').val() === undefined) {
            mrcht_other_chk = 0;
            $("#mrcht_other").after(
                '<span class="error-message mrcht-other-err">Other Merchant Name is required!</span>');
        } else {
            $(".mrcht-other-err").remove();
            mrcht_other_chk = 1;
        }
    } else {
        mrcht_other_chk = 1;
    }

    if ($('#ivs_amt').val() == '' ||  $('#ivs_amt').val() == '0' || $('#ivs_amt').val() === null || $('#ivs_amt')
            .val() === undefined) {
        amt_chk = 0;
        $("#ivs_amt").after(
            '<span class="error-message ivs-amt-err">Amount is required!</span>');
    } else {
        $(".ivs-amt-err").remove();
        amt_chk = 1;
    }

    if (type_chk == 1 && date_chk == 1 && mrcht_chk == 1 && amt_chk == 1 && mrcht_other_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})