//autocomplete
$(document).ready(function() {
    
    if (!($("#agt_brand").attr('disabled'))) {
        $("#agt_brand").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= BRAND ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    $(document).ready(function() {

        if (!($("#agt_pic").attr('disabled'))) {
            $("#agt_pic").keyup(function() {
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
    
    if (!($("#agt_country").attr('disabled'))) { 
        $("#agt_country").keyup(function() { 
            var param = { 
                search: $(this).val(), 
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
        
       
    }
})


//jQuery form validation

$("#name").on("input", function() {
    $(".name-err").remove();
});

$("#agt_brand").on("input", function() {
    $(".agt_brand-err").remove();
});

$("#agt_pic").on("input", function() {
    $(".agt_pic-err").remove();
});

$("#agt_country").on("input", function() {
    $(".agt_country-err").remove();
});


function isValidEmail(email) {
    // Simple regex for basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;
    var email_chk = 0;
    var brand_chk = 0;
    var pic_chk = 0;
    var country_chk = 0;

    if (($('#name').val() === '' || $('#name').val() === null || $('#name')
            .val() === undefined)) {
        name_chk = 0;
        $("#name").after(
            '<span class="error-message name-err">Name is required!</span>');
    } else {
        $(".name-err").remove();
        name_chk = 1;
    }
    const emailField = $('#email');
    const emailValue = emailField.val();
    if (($('#email').val() === '' || $('#email').val() === null || $('#email')
        .val() === undefined)) {
            email_chk = 0;
        $("#email").after(
            '<span class="error-message email-err">Email is required!</span>');
    }else if (!isValidEmail(emailValue)) {
        email_chk = 0;
        $("#email").after('<span class="error-message email-err">Invalid email format!</span>');
    }else {
        $(".email-err").remove();
        email_chk = 1;
    }

    if (($('#agt_brand').val() === '' || $('#agt_brand').val() === null || $('#agt_brand')
            .val() === undefined)) {
                brand_chk = 0;
        $("#agt_brand").after(
            '<span class="error-message agt_brand-err">Brand is required!</span>');
    } else {
        $(".agt-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#agt_pic').val() === '' || $('#agt_pic').val() === null || $('#agt_pic')
            .val() === undefined)) {
        pic_chk = 0;
        $("#agt_pic").after(
            '<span class="error-message agt-pic-err">Person-In-Charge is required!</span>');
    } else {
        $(".agt-pic-err").remove();
        pic_chk = 1;
    }

    if (($('#agt_country').val() === '' || $('#agt_country').val() === null || $('#agt_country')
            .val() === undefined)) {
        country_chk = 0;
        $("#agt_country").after(
            '<span class="error-message agt-country-err">Country is required!</span>');
    } else {
        $(".agt-country-err").remove();
        country_chk = 1;
    }


    if ( name_chk == 1 && brand_chk == 1 && pic_chk == 1 && country_chk == 1 &&  email_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})