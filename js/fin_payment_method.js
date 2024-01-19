//jQuery form validation
$("#pmf_name").on("input", function() {
    $(".pmf-name-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var name_chk = 0;;

    if ($('#pmf_name').val() === '' || $('#pmf_name').val() === null || $('#pmf_name')
        .val() === undefined) {
            name_chk = 0;
        $("#pmf_name").after(
            '<span class="error-message pmf-name-err">Name is required!</span>');
    } else {
        $(".pmf-name-err").remove();
        name_chk = 1;
    }

    if (name_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})