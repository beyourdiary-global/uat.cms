$(document).ready(function() {
    if (!($("#mrcht_pic").attr('disabled'))) {
        $("#mrcht_pic").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= USR_USER ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
})
$("#merchant_name").on("input", function() {
    $(".mrcht-name-err").remove();
});

$("#mrcht_email").on("input", function() {
    $(".mrcht-email-err").remove();
});

$("#mrcht_pic").on("input", function() {
    $(".pic-name-err").remove();
});

$("#mrcht_pic_contact").on("input", function() {
    $(".contact-err").remove();
});
$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var name_chk = 0;
    var email_chk = 0;
    var pic_chk = 0;
    var contact_chk = 0;

    if (($('#currentDataName').val() === '' || $('#currentDataName').val() === null || $('#currentDataName')
            .val() === undefined)) {
        name_chk = 0;
        $("#currentDataName").after(
            '<span class="error-message mrcht-name-err">Merchant name is required!</span>');
    } else {
        name_chk = 1;
        $(".mrcht-name-err").remove();
       
    }

    if (($('#mrcht_email').val() === '' || $('#mrcht_email').val() === null || $('#mrcht_email').val() ===
            undefined) ) {
        email_chk = 0;
        $("#mrcht_email").after('<span class="error-message mrcht-email-err">Email is required!</span>');
    }
    else if(!(isEmail($('#mrcht_email').val()))){
        email_chk = 0;
        $("#mrcht_email").after('<span class="error-message mrcht-email-err">Wrong email format!</span>');
      
    } else {
        email_chk = 1;
        $(".mrcht-email-err").remove();
    }

    if (($('#mrcht_pic').val() === '' || $('#mrcht_pic').val() === null || $('#mrcht_pic')
        .val() === undefined)) {
    pic_chk = 0;
    $("#mrcht_pic").after(
        '<span class="error-message pic-name-err">Person in Charge is required!</span>');
    } else {
        pic_chk = 1;
        $(".pic-name-err").remove();
    
    }

    if (($('#mrcht_pic_contact').val() === '' || $('#mrcht_pic_contact').val() === null || $('#mrcht_pic_contact')
        .val() === undefined)) {
    contact_chk = 0;
    $("#mrcht_pic_contact").after(
        '<span class="error-message contact-err">Person in Charge Contact is required!</span>');
    } else {
        contact_chk = 1;
    $(".contact-err").remove();

    }

    if (name_chk == 1 && email_chk == 1 && pic_chk == 1 && contact_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})