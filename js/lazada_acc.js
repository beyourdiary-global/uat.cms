//autocomplete
$(document).ready(function() {
    

    if (!($("#la_country").attr('disabled'))) { 
        $("#la_country").keyup(function() { 
            var param = { 
                search: $(this).val(), 
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');  console.log(searchInput);
        });
        
       
    }
    if (!($("#la_currency").attr('disabled'))) {
        $("#la_currency").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'unit', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= CUR_UNIT ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
            console.log(hiddenElementID.val())
        });
    }
})
  
//jQuery form validation

$("#la_name").on("input", function() {
    $(".la-name-err").remove();
});

$("#la_country").on("input", function() {
    $(".la-country-err").remove();
});

$("#la_currency").on("input", function() {
    $(".la-currency-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;
    var country_chk = 0;
    var currency_chk = 0;

    if (($('#la_name').val() === '' || $('#la_name').val() === null || $('#la_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#la_name").after(
            '<span class="error-message la-name-err">Name is required!</span>');
    } else {
        $(".la-name-err").remove();
        name_chk = 1;
    }

    if (($('#la_country').val() === '' || $('#la_country').val() === null || $('#la_country')
            .val() === undefined)) {
        country_chk = 0;
        $("#la_country").after(
            '<span class="error-message la-country-err">Country is required!</span>');
    } else {
        $(".la-country-err").remove();
        country_chk = 1;
    }

    if (($('#la_currency').val() === '' || $('#la_currency').val() === null || $('#la_currency')
            .val() === undefined)) {
        currency_chk = 0;
        $("#la_currency").after(
            '<span class="error-message la-currency-err">Currency is required!</span>');
    } else {
        $(".la-currency-err").remove();
        currency_chk = 1;
    }

    if ( name_chk == 1 && country_chk == 1 && currency_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})