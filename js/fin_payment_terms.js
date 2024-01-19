//jQuery form validation
$("#pay_terms_name").on("input", function() {
    $(".pt-name-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var name_chk = 0;;

    if ($('#pay_terms_name').val() === '' || $('#pay_terms_name').val() === null || $('#pay_terms_name')
        .val() === undefined) {
            name_chk = 0;
        $("#pay_terms_name").after(
            '<span class="error-message pt-name-err">Name is required!</span>');
    } else {
        $(".pt-name-err").remove();
        name_chk = 1;
    }

    if (name_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})