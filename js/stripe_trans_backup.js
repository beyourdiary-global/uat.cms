$('#stb_attach').on('change', function() {
    previewImage(this, 'stb_attach_preview')
})

//jQuery form validation
$("#stb_payout_id").on("input", function() {
    $(".stb-payout-id-err").remove();
});

$("#stb_date_paid").on("input", function() {
    $(".stb-date-paid-err").remove();
});

$("#stb_curr_unit").on("input", function() {
    $(".stb-curr-unit-err").remove();
});

$("#stb_amount").on("input", function() {
    $(".stb-amount-err").remove();
});

$("#stb_attach").on("input", function() {
    $(".stb-attach-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var stb_payout_id_chk = 0;
    var stb_date_paid_chk = 0;
    var stb_curr_unit_chk = 0;
    var stb_amount_chk = 0;
    var attach_chk = 0;

    if (($('#stb_payout_id').val() === '' || $('#stb_payout_id').val() === null || $('#stb_payout_id')
            .val() === undefined)) {
    stb_payout_id_chk = 0;
        $("#stb_payout_id").after(
            '<span class="error-message stb-payout-id-err">Stripe Payout ID is required!</span>');
    } else {
        $(".stb-payout-id-err").remove();
        stb_payout_id_chk = 1;
    }

    if (($('#stb_date_paid').val() === '' || $('#stb_date_paid').val() === null || $('#stb_date_paid')
            .val() === undefined)) {
    stb_date_paid_chk = 0;
        $("#stb_date_paid").after(
            '<span class="error-message stb-date-paid-err">Date Paid is required!</span>');
    } else {
        $(".stb-date-paid-err").remove();
        stb_date_paid_chk = 1;
    }

    if (($('#stb_curr_unit').val() === '' || $('#stb_curr_unit').val() === null || $('#stb_curr_unit')
            .val() === undefined)) {
    stb_curr_unit_chk = 0;
        $("#stb_curr_unit").after(
            '<span class="error-message stb-curr-unit-err">Currency Unit is required!</span>');
    } else {
        $(".stb-curr-unit-err").remove();
    stb_curr_unit_chk = 1;
    }

    if (($('#stb_amount').val() === '' || $('#stb_amount').val() === null || $('#stb_amount')
            .val() === undefined)) {
    stb_amount_chk = 0;
        $("#stb_amount").after(
            '<span class="error-message stb-amount-err">Currency Unit is required!</span>');
    } else {
        $(".stb-amount-err").remove();
    stb_amount_chk = 1;
    }

    var fileInput = $('#stb_attach')[0];
    
    // Check if a new file is selected or if there is an existing attachment
    if ((fileInput.files.length === 0) && ($('#stb_attachmentValue').val() == '' || $('#stb_attachmentValue').val() == '0' || $('#stb_attachmentValue').val() === null || $('#stb_attachmentValue')
    .val() === undefined)) {
        // No file selected and no existing attachment
        attach_chk = 0;
        $("#stb_attach").after('<span class="error-message stb-attach-err">Attachment is required!</span>');
    } else {
        // File selected or existing attachment present
        attach_chk = 1;
    }

    if (stb_payout_id_chk == 1 && stb_date_paid_chk == 1 && stb_curr_unit_chk == 1 && stb_amount_chk == 1 && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})