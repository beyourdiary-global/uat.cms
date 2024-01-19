//jQuery form validation

$("#sa_name").on("input", function() {
    $(".sa-name-err").remove();
});

$("#sa_country").on("input", function() {
    $(".sa-country-err").remove();
});

$("#sa_currency").on("input", function() {
    $(".sa-currency-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;
    var country_chk = 0;
    var currency_chk = 0;

    if (($('#sa_name').val() === '' || $('#sa_name').val() === null || $('#sa_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#sa_name").after(
            '<span class="error-message sa-name-err">Name is required!</span>');
    } else {
        $(".sa-name-err").remove();
        name_chk = 1;
    }

    if (($('#sa_country').val() === '' || $('#sa_country').val() === null || $('#sa_country')
            .val() === undefined)) {
        id_chk = 0;
        $("#sa_country").after(
            '<span class="error-message sa-country-err">country is required!</span>');
    } else {
        $(".sa-country-err").remove();
        id_chk = 1;
    }

    if (($('#sa_currency').val() === '' || $('#sa_currency').val() === null || $('#sa_currency')
            .val() === undefined)) {
        id_chk = 0;
        $("#sa_currency").after(
            '<span class="error-message sa-currency-err">currency is required!</span>');
    } else {
        $(".sa-currency-err").remove();
        id_chk = 1;
    }

    if ( name_chk == 1 && country_chk == 1 && currency_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})