
$('#atb_attach').on('change', function() {
    previewImage(this, 'atb_attach_preview')
})

//jQuery form validation
$("#atb_trans_id").on("input", function() {
    $(".atb-trans-id-err").remove();
});

$("#btb_month").on("input", function() {
    $(".btb-month-err").remove();
});

$("#btb_attach").on("input", function() {
    $(".btb-attach-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var atb_trans_id_chk = 0;
    var atb_atome_id_chk = 0;
    var atb_trans_outlet_chk = 0;
    var atb_platform_id_chk = 0;
    var attach_chk = 0;

    if (($('#atb_trans_id').val() === '' || $('#atb_trans_id').val() === null || $('#atb_trans_id')
            .val() === undefined)) {
        atb_trans_id_chk = 0;
        $("#atb_trans_id").after(
            '<span class="error-message atb-trans-id-err">Transaction ID is required!</span>');
    } else {
        $(".atb-trans-id-err").remove();
        atb_trans_id_chk = 1;
    }

    if (($('#atb_atome_id').val() === '' || $('#atb_atome_id').val() === null || $('#atb_atome_id')
            .val() === undefined)) {
        atb_atome_id_chk = 0;
        $("#atb_atome_id").after(
            '<span class="error-message atb-atome-id-err">Atome Order ID is required!</span>');
    } else {
        $(".atb-atome-id-err").remove();
        atb_atome_id_chk = 1;
    }

    if (($('#atb_trans_outlet').val() === '' || $('#atb_trans_outlet').val() === null || $('#atb_trans_outlet')
            .val() === undefined)) {
        atb_trans_outlet_chk = 0;
        $("#atb_trans_outlet").after(
            '<span class="error-message atb-trans-outlet-err">Transaction Outlet is required!</span>');
    } else {
        $(".atb-trans-outlet-err").remove();
        atb_trans_outlet_chk = 1;
    }

    if (($('#atb_platform_id').val() === '' || $('#atb_platform_id').val() === null || $('#atb_platform_id')
            .val() === undefined)) {
        atb_platform_id_chk = 0;
        $("#atb_platform_id").after(
            '<span class="error-message atb-platform-id-err">E-commerce Platform Order ID is required!</span>');
    } else {
        $(".atb-platform-id-err").remove();
        atb_platform_id_chk = 1;
    }

    var fileInput = $('#atb_attach')[0];
    
    // Check if a new file is selected or if there is an existing attachment
    if ((fileInput.files.length === 0) && ($('#atb_attachmentValue').val() == '' || $('#atb_attachmentValue').val() == '0' || $('#atb_attachmentValue').val() === null || $('#atb_attachmentValue')
    .val() === undefined)) {
        // No file selected and no existing attachment
        attach_chk = 0;
        $("#atb_attach").after('<span class="error-message atb-attach-err">Attachment is required!</span>');
    } else {
        // File selected or existing attachment present
        attach_chk = 1;
    }

    if (atb_trans_id_chk == 1 && atb_atome_id_chk == 1 && atb_trans_outlet_chk == 1 && atb_platform_id_chk == 1  && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})
