$('#initca_attach').on('change', function() {
    previewImage(this, 'initca_attach_preview')
})

//jQuery form validation
$("#initca_date").on("input", function() {
    $(".initca-date-err").remove();
});

$("#initca_currency").on("input", function() {
    $(".initca-curr-err").remove();
});

$("#initca_amt").on("input", function() {
    $(".initca-amt-err").remove();
});

$("#initca_desc").on("input", function() {
    $(".initca-desc-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    var date_chk = 0;
    var currency_chk = 0;
    var amt_chk = 0;

    if (($('#initca_date').val() === '' || $('#initca_date').val() === null || $('#initca_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#initca_date").after(
            '<span class="error-message initca-date-err">Date is required!</span>');
    } else {
        $(".initca-date-err").remove();
        date_chk = 1;
    }

    if (($('#initca_currency').val() === '' || $('#initca_currency').val() === null || $('#initca_currency')
            .val() === undefined)) {
        currency_chk = 0;
        $("#initca_currency").after(
            '<span class="error-message initca-curr-err">Currency is required!</span>');
    } else {
        $(".initca-curr-err").remove();
        currency_chk = 1;
    }

    if (($('#initca_amt').val() == '' || $('#initca_amt').val() === null || $('#initca_amt')
            .val() === undefined)) {
        amt_chk = 0;
        $("#initca_amt").after(
            '<span class="error-message initca-amt-err">Amount is required!</span>');
    } else {
        $(".initca-amt-err").remove();
        amt_chk = 1;
    }

    if (($('#initca_desc').val() == '' || $('#initca_desc').val() === null || $('#initca_desc')
            .val() === undefined)) {
        desc_chk = 0;
        $("#initca_desc").after(
            '<span class="error-message initca-desc-err">Description is required!</span>');
    } else {
        $(".initca-desc-err").remove();
        desc_chk = 1;
    }

    if (date_chk == 1 && currency_chk == 1 && amt_chk == 1 && desc_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})