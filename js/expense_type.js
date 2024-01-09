//jQuery form validation
$("#et_name").on("input", function() {
    $(".et-name-err").remove();
});

$("#et_code").on("input", function() {
    $(".et-code-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var name_chk = 0;
    var code_chk = 0;

    if (($('#et_name').val() === '' || $('#et_name').val() === null || $('#et_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#et_name").after(
            '<span class="error-message et-name-err">Name is required!</span>');
    } else {
        $(".et-name-err").remove();
        name_chk = 1;
    }

    if (($('#et_code').val() === '' || $('#et_code').val() === null || $('#et_code')
            .val() === undefined)) {
        code_chk = 0;
        $("#et_code").after(
            '<span class="error-message et-code-err">Code is required!</span>');
    } else {
        $(".maa-name-err").remove();
        code_chk = 1;
    }

    if (code_chk == 1 && name_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})