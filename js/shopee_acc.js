//autocomplete
$(document).ready(function() {
    

    if (!($("#sa_country").attr('disabled'))) { 
        $("#sa_country").keyup(function() { 
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
    if (!($("#sa_currency").attr('disabled'))) {
        $("#sa_currency").keyup(function() {
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

//autofocus
function setAutofocus(action) {
    if (action === "I" || action === "E") {
      var saNameInput = $("#sa_name");
      saNameInput.prop("disabled", false); // Enable the input field
      saNameInput.focus();
    }
  }
  
//jQuery form validation

$("#sa_name").on("input", function() {
    $(".sa-name-err").remove();
});

$("#sa_country").on("input", function() {
    $(".sa-country-err").remove();
});

$("#sa_currency").on("input", function() {
    $(".sa-currency-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;
    var country_chk = 0;
    var currency_chk = 0;

    if (($('#sa_name').val() === '' || $('#sa_name').val() === null || $('#sa_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#sa_name").after(
            '<span class="error-message sa-name-err">Name is required!</span>');
    } else {
        $(".sa-name-err").remove();
        name_chk = 1;
    }

    if (($('#sa_country').val() === '' || $('#sa_country').val() === null || $('#sa_country')
            .val() === undefined)) {
        country_chk = 0;
        $("#sa_country").after(
            '<span class="error-message sa-country-err">Country is required!</span>');
    } else {
        $(".sa-country-err").remove();
        country_chk = 1;
    }

    if (($('#sa_currency').val() === '' || $('#sa_currency').val() === null || $('#sa_currency')
            .val() === undefined)) {
        currency_chk = 0;
        $("#sa_currency").after(
            '<span class="error-message sa-currency-err">Currency is required!</span>');
    } else {
        $(".sa-currency-err").remove();
        currency_chk = 1;
    }

    if ( name_chk == 1 && country_chk == 1 && currency_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})