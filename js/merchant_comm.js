$('#mcr_attach').on('change', function() {
    previewImage(this, 'mcr_attach_preview')
})

//autocomplete
$(document).ready(function() {

    if (!($("#mcr_pic").attr('disabled'))) {
        $("#mcr_pic").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= USR_USER ?>', // json filename (generated when login)
            }
            console.log(param["elementID"]);
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
})

//jQuery form validation
$("#mcr_type").on("input", function() {
    $(".mcr-type-err").remove();
});

$("#mcr_pic").on("input", function() {
    $(".mcr-pic-err").remove();
});

$("#mcr_date").on("input", function() {
    $(".mcr-date-err").remove();
});

$("#mcr_bank").on("input", function() {
    $(".mcr-bank-err").remove();
});

$("#mcr_currency").on("input", function() {
    $(".mcr-curr-err").remove();
});

$("#mcr_amt").on("input", function() {
    $(".mcr-amt-err").remove();
});

$("#mcr_desc").on("input", function() {
    $(".mcr-desc-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var date_chk = 0;
    var currency_chk = 0;
    var amt_chk = 0;

    if (($('#mcr_date').val() === '' || $('#mcr_date').val() === null || $('#mcr_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#mcr_date").after(
            '<span class="error-message mcr-date-err">Date is required!</span>');
    } else {
        $(".mcr-date-err").remove();
        date_chk = 1;
    }

    if (($('#mcr_currency').val() === '' || $('#mcr_currency').val() === null || $('#mcr_currency')
            .val() === undefined)) {
        currency_chk = 0;
        $("#mcr_currency").after(
            '<span class="error-message mcr-curr-err">Currency is required!</span>');
    } else {
        $(".mcr-curr-err").remove();
        currency_chk = 1;
    }

    if (($('#mcr_amt').val() == '' || $('#mcr_amt').val() == '0' || $('#mcr_amt').val() === null || $('#mcr_amt')
            .val() === undefined)) {
        amt_chk = 0;
        $("#mcr_amt").after(
            '<span class="error-message mcr-amt-err">Amount is required!</span>');
    } else {
        $(".mcr-amt-err").remove();
        amt_chk = 1;
    }

    if (date_chk == 1 && currency_chk == 1 && amt_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})