$("#btb_year").datepicker({
    format: "yyyy",
    viewMode: 'years',
    minViewMode: 'years',
    orientation: "bottom",
    autoclose: true
});

$("#btb_month").datepicker({
    format: "M",
    viewMode: 'months',
    minViewMode: 'months',
    maxViewMode: 'months',
    orientation: "bottom",
    autoclose: true
});

$('#btb_attach').on('change', function() {
    previewImage(this, 'btb_attach_preview')
})

//jQuery form validation
$("#btb_year").on("input", function() {
    $(".btb-year-err").remove();
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
    var year_chk = 0;
    var month_chk = 0;
    var attach_chk = 0;

    if (($('#btb_year').val() === '' || $('#btb_year').val() === null || $('#btb_year')
            .val() === undefined)) {
        year_chk = 0;
        $("#btb_year").after(
            '<span class="error-message btb-year-err">Year is required!</span>');
    } else {
        $(".btb-year-err").remove();
        year_chk = 1;
    }

    if (($('#btb_month').val() === '' || $('#btb_month').val() === null || $('#btb_month')
            .val() === undefined)) {
        month_chk = 0;
        $("#btb_month").after(
            '<span class="error-message btb-month-err">Month is required!</span>');
    } else {
        $(".btb-date-err").remove();
        month_chk = 1;
    }

    var fileInput = $('#btb_attach')[0];
    
    // Check if a new file is selected or if there is an existing attachment
    if ((fileInput.files.length === 0) && ($('#btb_attachmentValue').val() == '' || $('#btb_attachmentValue').val() == '0' || $('#btb_attachmentValue').val() === null || $('#btb_attachmentValue')
    .val() === undefined)) {
        // No file selected and no existing attachment
        attach_chk = 0;
        $("#btb_attach").after('<span class="error-message btb-attach-err">Attachment is required!</span>');
    } else {
        // File selected or existing attachment present
        attach_chk = 1;
    }

    if (year_chk == 1 && month_chk == 1 && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})