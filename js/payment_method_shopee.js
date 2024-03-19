//jQuery form validation

$("#pms_name").on("input", function() {
    $(".pms-name-err").remove();
});

$("#pms_fees").on("input", function() {
    $(".pms-fees-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;
    var fees_chk = 0;

    if (($('#pms_name').val() === '' || $('#pms_name').val() === null || $('#pms_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#pms_name").after(
            '<span class="error-message pms-name-err">Name is required!</span>');
    } else {
        $(".pms-name-err").remove();
        name_chk = 1;
    }

    if (($('#pms_fees').val() === '' || $('#pms_fees').val() === null || $('#pms_fees')
            .val() === undefined)) {
        fees_chk = 0;
        $("#pms_fees").after(
            '<span class="error-message pms-fees-err">Transaction fees is required!</span>');
    } else {
        $(".pms-fees-err").remove();
        fees_chk = 1;
    }

    if ( name_chk == 1 && fees_chk == 1 )
        $(this).closest('form').submit();
    else
        return false;

})