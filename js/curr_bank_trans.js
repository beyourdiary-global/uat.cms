$('#cba_attach').on('change', function() {
    previewImage(this, 'cba_attach_preview')
})

//jQuery form validation
$("#cba_type").on("input", function() {
    $(".cba-type-err").remove();
});

$("#cba_date").on("input", function() {
    $(".cba-date-err").remove();
});

$("#cba_bank").on("input", function() {
    $(".cba-bank-err").remove();
});

$("#cba_currency").on("input", function() {
    $(".cba-curr-err").remove();
});

$("#cba_amt").on("input", function() {
    $(".cba-amt-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var type_chk = 0;
    var date_chk = 0;
    var bank_chk = 0;
    var currency_chk = 0;
    var amt_chk = 0;

    if ($('#cba_type').val() === '' || $('#cba_type').val() === null || $('#cba_type')
        .val() === undefined) {
        type_chk = 0;
        $("#cba_type").after(
            '<span class="error-message cba-type-err">Type is required!</span>');
    } else {
        $(".cba-type-err").remove();
        type_chk = 1;
    }

    if (($('#cba_date').val() === '' || $('#cba_date').val() === null || $('#cba_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#cba_date").after(
            '<span class="error-message cba-date-err">Date is required!</span>');
    } else {
        $(".cba-date-err").remove();
        date_chk = 1;
    }

    if (($('#cba_bank').val() === '' || $('#cba_bank').val() === null || $('#cba_bank')
            .val() === undefined)) {
        bank_chk = 0;
        $("#cba_bank").after(
            '<span class="error-message cba-bank-err">Bank is required!</span>');
    } else {
        $(".cba-bank-err").remove();
        bank_chk = 1;
    }

    if (($('#cba_currency').val() === '' || $('#cba_currency').val() === null || $('#cba_currency')
            .val() === undefined)) {
        currency_chk = 0;
        $("#cba_currency").after(
            '<span class="error-message cba-curr-err">Currency is required!</span>');
    } else {
        $(".cba-curr-err").remove();
        currency_chk = 1;
    }

    if (($('#cba_amt').val() == '' ||  $('#cba_amt').val() == '0' || $('#cba_amt').val() === null || $('#cba_amt')
            .val() === undefined)) {
        amt_chk = 0;
        $("#cba_amt").after(
            '<span class="error-message cba-amt-err">Amount is required!</span>');
    } else {
        $(".cba-amt-err").remove();
        amt_chk = 1;
    }

    if (type_chk == 1 && date_chk == 1 && bank_chk == 1 && currency_chk == 1 && amt_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})