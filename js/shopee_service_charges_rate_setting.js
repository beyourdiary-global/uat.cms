//jQuery form validation
$("#curr").on("input", function() {
    $(".curr-err").remove();
});

$("#commission").on("input", function() {
    $(".commission-err").remove();
});

$("#service").on("input", function() {
    $(".service-err").remove();
});

$("#transaction").on("input", function() {
    $(".transaction-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var curr_chk = 0;
    var commission_chk = 0;
    var service_chk = 0;
    var transaction_chk = 0;

    if (($('#curr').val() === '' || $('#curr').val() === null || $('#curr')
            .val() === undefined)) {
        curr_chk = 0;
        $("#curr").after(
            '<span class="error-message curr-err">Currency Unit is required!</span>');
    } else {
        $(".curr-err").remove();
        curr_chk = 1;
    }

    if (($('#commission').val() === '' || $('#commission').val() === null || $('#commission')
            .val() === undefined)) {
                commission_chk = 0;
        $("#commission").after(
            '<span class="error-message commission-err">Commission Fees Rate is required!</span>');
    } else {
        $(".commission-err").remove();
        commission_chk = 1;
    }

    if (($('#service').val() === '' || $('#service').val() === null || $('#service')
            .val() === undefined)) {
                service_chk = 0;
        $("#service").after(
            '<span class="error-message service-err">Service Fee Rate is required!</span>');
    } else {
        $(".service-err").remove();
        service_chk = 1;
    }

    if (($('#transaction').val() === '' || $('#transaction').val() === null || $('#transaction')
            .val() === undefined)) {
        transaction_chk = 0;
        $("#transaction").after(
            '<span class="error-message transaction-err">Transaction Fee is required!</span>');
    } else {
        $(".transaction-err").remove();
        transaction_chk = 1;
    }

    if ( curr_chk == 1 && commission_chk == 1 && service_chk == 1 && transaction_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})