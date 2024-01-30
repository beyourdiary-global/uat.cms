//autocomplete
$(document).ready(function() {
    

    if (!($("#tax_country").attr('disabled'))) { 
        $("#tax_country").keyup(function() { 
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

$("#tax_country").on("input", function() {
    $(".tax_country-err").remove();
});

$("#percentage").on("input", function() {
    $(".percentage-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;
    var country_chk = 0;
    var percentage_chk = 0;

    if (($('#name').val() === '' || $('#name').val() === null || $('#name')
            .val() === undefined)) {
        name_chk = 0;
        $("#name").after(
            '<span class="error-message sa-name-err">Name is required!</span>');
    } else {
        $(".sa-name-err").remove();
        name_chk = 1;
    }

    if (($('#tax_country').val() === '' || $('#tax_country').val() === null || $('#tax_country')
            .val() === undefined)) {
        country_chk = 0;
        $("#tax_country").after(
            '<span class="error-message sa-country-err">Country is required!</span>');
    } else {
        $(".tax-country-err").remove();
        country_chk = 1;
    }

    if (($('#percentage').val() === '' || $('#percentage').val() === null || $('#percentage')
            .val() === undefined)) {
        currency_chk = 0;
        $("#percentage").after(
            '<span class="error-message percentage-err">Percentage is required!</span>');
    } else {
        $(".percentage-err").remove();
        percentage_chk = 1;
    }

    if ( name_chk == 1 && country_chk == 1 && percentage_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})