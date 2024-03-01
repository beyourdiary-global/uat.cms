//autocomplete
$(document).ready(function() {
    

    if (!($("#scr_pic").attr('disabled'))) { 
        $("#scr_pic").keyup(function() { 
            var param = { 
                search: $(this).val(), 
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= USR_USER ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');  console.log(searchInput);
        });
        
       
    }
    if (!($("#scr_country").attr('disabled'))) {
        $("#scr_country").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'nicename', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
    if (!($("#scr_brand").attr('disabled'))) {
        $("#scr_brand").keyup(function() {
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
    if (!($("#scr_series").attr('disabled'))) {
        $("#scr_series").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= BRD_SERIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
})

//jQuery form validation
$("#scr_username").on("input", function() {
    $(".sa-name-err").remove();
});

$("#scr_pic").on("input", function() {
    $(".scr-pic-err").remove();
});

$("#scr_country").on("input", function() {
    $(".scr-country-err").remove();
});

$("#scr_brand").on("input", function() {
    $(".scr-brand-err").remove();
});

$("#scr_series").on("input", function() {
    $(".scr-series-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    
    var name_chk = 0;
    var country_chk = 0;
    var currency_chk = 0;

    if (($('#scr_username').val() === '' || $('#scr_username').val() === null || $('#scr_username')
            .val() === undefined)) {
        name_chk = 0;
        $("#scr_username").after(
            '<span class="error-message scr-name-err">Shopee Buyer Username is required!</span>');
    } else {
        $(".scr-name-err").remove();
        name_chk = 1;
    }

    if (($('#scr_pic').val() === '' || $('#scr_pic').val() === null || $('#scr_pic')
            .val() === undefined)) {
        pic_chk = 0;
        $("#scr_pic").after(
            '<span class="error-message scr-pic-err">Sales Person In Charge is required!</span>');
    } else {
        $(".scr-pic-err").remove();
        pic_chk = 1;
    }

    if (($('#scr_brand').val() === '' || $('#scr_brand').val() === null || $('#scr_brand')
            .val() === undefined)) {
        brand_chk = 0;
        $("#scr_brand").after(
            '<span class="error-message scr-brand-err">Brand is required!</span>');
    } else {
        $(".scr-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#scr_country').val() === '' || $('#scr_country').val() === null || $('#scr_country')
            .val() === undefined)) {
        country_chk = 0;
        $("#scr_country").after(
            '<span class="error-message scr-country-err">Country is required!</span>');
    } else {
        $(".scr-pic-err").remove();
        country_chk = 1;
    }

    if (($('#scr_series').val() === '' || $('#scr_series').val() === null || $('#scr_series')
            .val() === undefined)) {
        series_chk = 0;
        $("#scr_series").after(
            '<span class="error-message scr-series-err">Series is required!</span>');
    } else {
        $(".scr-series-err").remove();
        series_chk = 1;
    }

    if ( name_chk == 1 && pic_chk == 1 && country_chk == 1 && brand_chk && series_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})