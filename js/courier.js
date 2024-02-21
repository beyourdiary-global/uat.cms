//autocomplete
$(document).ready(function() {

    if (!($("#courier_country").attr('disabled'))) {
        $("#courier_country").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'nicename', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            console.log("Element ID:", param["elementID"]);
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
})

//jQuery form validation
$("#courier_id").on("input", function() {
    $(".courier-id-err").remove();
});

$("#courier_name").on("input", function() {
    $(".courier-name-err").remove();
});

$("#courier_country").on("input", function() {
    $(".courier-country-err").remove();
});

$("#courier_tax").on("input", function() {
    $(".courier-tax-err").remove();
});



$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var id_chk = 0;
    var name_chk = 0;
    var country_chk = 0;
    var tax_chk = 0;

    if (($('#courier_id').val() == '' ||  $('#courier_id').val() == '0' || $('#courier_id').val() === null || $('#courier_id')
    .val() === undefined)) {
        id_chk = 0;
        $("#courier_id").after(
            '<span class="error-message courier-id-err">ID is required!</span>');
    } else {
        $(".courier-id-err").remove();
        id_chk = 1;
    }

    if (($('#courier_name').val() === '' || $('#courier_name').val() === null || $('#courier_name')
            .val() === undefined)) {
        name_chk = 0;
        $("#courier_name").after(
            '<span class="error-message courier-name-err">Name is required!</span>');
    } else {
        $(".courier-name-err").remove();
        name_chk = 1;
    }

    if (($('#courier_country').val() === '' || $('#courier_country').val() === null || $('#courier_country')
            .val() === undefined)) {
            country_chk = 0;
        $("#courier_country").after(
            '<span class="error-message courier-country-err">Country is required!</span>');
    } else {
        $(".courier-country-err").remove();
        country_chk = 1;
    }

    if (($('#courier_tax').val() === '' || $('#courier_tax').val() === null || $('#courier_tax')
            .val() === undefined)) {
        tax_chk = 0;
        $("#courier_tax").after(
            '<span class="error-message courier-tax-err">Tax is required!</span>');
    } else {
        $(".courier-tax-err").remove();
        tax_chk = 1;
    }

    if (id_chk == 1 && name_chk == 1 && country_chk == 1 && tax_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})