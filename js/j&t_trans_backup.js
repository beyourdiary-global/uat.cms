$('#jt_attach').on('change', function() {
    previewImage(this, 'jt_attach_preview')
})

//jQuery form validation
$("#jt_inv_number").on("input", function() {
    $(".jt-inv-number-err").remove();
});

$("#jt_inv_date").on("input", function() {
    $(".jt-inv-date-err").remove();
});

$("#jt_attach").on("input", function() {
    $(".jt-attach-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var jt_inv_number_chk = 0;
    var jt_inv_date_chk = 0;
    var attach_chk = 0;

    if (($('#jt_inv_number').val() === '' || $('#jt_inv_number').val() === null || $('#jt_inv_number')
            .val() === undefined)) {
    jt_inv_number_chk = 0;
        $("#jt_inv_number").after(
            '<span class="error-message jt-inv-number-err">Invoice Number is required!</span>');
    } else {
        $(".jt-inv-number-err").remove();
    jt_inv_number_chk = 1;
    }

    if (($('#jt_inv_date').val() === '' || $('#jt_inv_date').val() === null || $('#jt_inv_date')
            .val() === undefined)) {
    jt_inv_date_chk = 0;
        $("#jt_inv_date").after(
            '<span class="error-message jt-inv-date-err">Invoice Date is required!</span>');
    } else {
        $(".jt-inv-date-err").remove();
    jt_inv_date_chk = 1;
    }

    var fileInput = $('#jt_attach')[0];
    
    // Check if a new file is selected or if there is an existing attachment
    if ((fileInput.files.length === 0) && ($('#jt_attachmentValue').val() == '' || $('#jt_attachmentValue').val() == '0' || $('#jt_attachmentValue').val() === null || $('#jt_attachmentValue')
    .val() === undefined)) {
        // No file selected and no existing attachment
        attach_chk = 0;
        $("#jt_attach").after('<span class="error-message jt-attach-err">Attachment is required!</span>');
    } else {
        // File selected or existing attachment present
        attach_chk = 1;
    }

    if (jt_inv_number_chk == 1 && jt_inv_date_chk == 1 && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})
