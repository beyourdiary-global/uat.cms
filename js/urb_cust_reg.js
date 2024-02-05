$('#umr_attach').on('change', function() {
    previewImage(this, 'umr_attach_preview')
})

//jQuery form validation
$("#umr_name").on("input", function() {
    $(".umr-name-err").remove();
});

$("#umr_ic").on("input", function() {
    $(".umr-ic-err").remove();
});

$("#umr_add").on("input", function() {
    $(".umr-add-err").remove();
});

$("#umr_date").on("input", function() {
    $(".umr-date-err").remove();
});

$("#umr_attach").on("input", function() {
    $(".umr-attach-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var name_chk = 0;
    var ic_chk = 0;
    var add_chk = 0;
    var date_chk = 0;
    var attach_chk = 0;

    if (($('#umr_name').val() === '' || $('#umr_name').val() === null || $('#umr_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#umr_name").after(
            '<span class="error-message umr-name-err">Name is required!</span>');
    } else {
        $(".umr-name-err").remove();
        name_chk = 1;
    }
    if (($('#umr_ic').val() === '' || $('#umr_ic').val() === null || $('#umr_ic')
            .val() === undefined)) {
        ic_chk = 0;
        $("#umr_ic").after(
            '<span class="error-message umr-ic-err">IC is required!</span>');
    } else {
        $(".umr-ic-err").remove();
        ic_chk = 1;
    }
    if (($('#umr_add').val() === '' || $('#umr_add').val() === null || $('#umr_add')
            .val() === undefined)) {
        add_chk = 0;
        $("#umr_add").after(
            '<span class="error-message umr-add-err">Address is required!</span>');
    } else {
        $(".umr-add-err").remove();
        add_chk = 1;
    }

    if (($('#umr_date').val() === '' || $('#umr_date').val() === null || $('#umr_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#umr_date").after(
            '<span class="error-message umr-date-err">Registration Date is required!</span>');
    } else {
        $(".umr-date-err").remove();
        date_chk = 1;
    }

    var fileInput = $('#umr_attach')[0];
    
    // Check if a new file is selected or if there is an existing attachment
    if ((fileInput.files.length === 0) && ($('#umr_attachmentValue').val() == '' || $('#umr_attachmentValue').val() == '0' || $('#umr_attachmentValue').val() === null || $('#umr_attachmentValue')
    .val() === undefined)) {
        // No file selected and no existing attachment
        attach_chk = 0;
        $("#umr_attach").after('<span class="error-message umr-attach-err">Attachment is required!</span>');
    } else {
        // File selected or existing attachment present
        attach_chk = 1;
    }

    if (name_chk == 1 && ic_chk == 1 && add_chk == 1 && date_chk == 1 && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})