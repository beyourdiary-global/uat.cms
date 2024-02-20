//jQuery form validation

$("#csm_name").on("input", function() {
    $(".csm-name-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;

    if (($('#csm_name').val() === '' || $('#csm_name').val() === null || $('#csm_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#csm_name").after(
            '<span class="error-message sa-name-err">Name is required!</span>');
    } else {
        $(".csm-name-err").remove();
        name_chk = 1;
    }

    if ( name_chk == 1 )
        $(this).closest('form').submit();
    else
        return false;

})