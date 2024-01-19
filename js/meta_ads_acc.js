//jQuery form validation
$("#maa_id").on("input", function() {
    $(".maa-id-err").remove();
});

$("#maa_name").on("input", function() {
    $(".maa-name-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var id_chk = 0;
    var name_chk = 0;

    if (($('#maa_id').val() === '' || $('#maa_id').val() === null || $('#maa_id')
            .val() === undefined)) {
        id_chk = 0;
        $("#maa_id").after(
            '<span class="error-message maa-id-err">ID is required!</span>');
    } else {
        $(".maa-id-err").remove();
        id_chk = 1;
    }

    if (($('#maa_name').val() === '' || $('#maa_name').val() === null || $('#maa_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#maa_name").after(
            '<span class="error-message maa-name-err">Name is required!</span>');
    } else {
        $(".maa-name-err").remove();
        name_chk = 1;
    }

    if (id_chk == 1 && name_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})