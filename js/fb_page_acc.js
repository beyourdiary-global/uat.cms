//jQuery form validation

$("#fb_name").on("input", function() {
    $(".fb-name-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;

    if (($('#fb_name').val() === '' || $('#fb_name').val() === null || $('#fb_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#fb_name").after(
            '<span class="error-message sa-name-err">Name is required!</span>');
    } else {
        $(".sa-name-err").remove();
        name_chk = 1;
    }

    if ( name_chk == 1 )
        $(this).closest('form').submit();
    else
        return false;

})