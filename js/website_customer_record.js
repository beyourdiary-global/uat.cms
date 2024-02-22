//autocomplete
$(document).ready(function() {

    if (!($("#wcr_pic").attr('disabled'))) {
        $("#wcr_pic").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= USR_USER ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    //country
    if (!($("#wcr_country").attr('disabled'))) {
        $("#wcr_country").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'nicename', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= COUNTRIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    //brand
    if (!($("#wcr_brand").attr('disabled'))) {
        $("#wcr_brand").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= BRAND ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
 
})

//jQuery fcbm validation
$("#wcr_name").on("input", function() {
    $(".wcr-name-err").remove();
});

$("#wcr_contact").on("input", function() {
    $(".wcr-contact-err").remove();
});

$("#wcr_pic").on("input", function() {
    $(".wcr-pic-err").remove();
});

$("#wcr_country").on("input", function() {
    $(".wcr-country-err").remove();
});

$("#wcr_brand").on("input", function() {
    $(".wcr-brand-err").remove();
});

$("#wcr_series").on("input", function() {
    $(".wcr-series-err").remove();
});

$("#wcr_rec_name").on("input", function() {
    $(".wcr-rec-name-err").remove();
});

$("#wcr_rec_ctc").on("input", function() {
    $(".wcr-rec-ctc-err").remove();
});

$("#wcr_rec_add").on("input", function() {
    $(".wcr-rec-add-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    var name_chk = 0;
    var ctc_chk = 0;
    var pic_chk = 0;
    var country_chk = 0;
    var brand_chk = 0;
    var series_chk = 0;
    var rec_name_chk = 0;
    var rec_ctc_chk = 0;
    var rec_add_chk = 0;

    if ($('#wcr_name').val() === '' || $('#wcr_name').val() === null || $('#wcr_name')
        .val() === undefined) {
        name_chk = 0;
        $("#wcr_name").after(
            '<span class="error-message wcr-name-err">Name is required!</span>');
    } else {
        $(".wcr-name-err").remove();
        name_chk = 1;
    }

    if (($('#wcr_contact').val() === '' || $('#wcr_contact').val() === null || $('#wcr_contact')
            .val() === undefined)) {
        ctc_chk = 0;
        $("#wcr_contact").after(
            '<span class="error-message wcr-contact-err">Contact is required!</span>');
    } else {
        $(".wcr-contact-err").remove();
        ctc_chk = 1;
    }

    if (($('#wcr_pic_hidden').val() === ''  || $('#wcr_pic_hidden').val() == '0' || $('#wcr_pic_hidden').val() === null || $('#wcr_pic_hidden')
            .val() === undefined)) {
        pic_chk = 0;
        $("#wcr_pic").after(
            '<span class="error-message wcr-pic-err">Sales Person-In-Charge is required!</span>');
    } else {
        $(".wcr-pic-err").remove();
        pic_chk = 1;
    }


    if (($('#wcr_country_hidden').val() == '' || $('#wcr_country_hidden').val() == '0' || $('#wcr_country_hidden').val() === null || $('#wcr_country_hidden')
            .val() === undefined)) {
        country_chk = 0;
        $("#wcr_country").after(
            '<span class="error-message wcr-country-err">Country is required!</span>');
    } else {
        $(".wcr-country-err").remove();
        country_chk = 1;
    }

    if (($('#wcr_brand_hidden').val() == '' || $('#wcr_brand_hidden').val() == '0' || $('#wcr_brand_hidden').val() === null || $('#wcr_brand_hidden')
            .val() === undefined)) {
        brand_chk = 0;
        $("#wcr_brand").after(
            '<span class="error-message wcr-brand-err">Brand is required!</span>');
    } else {
        $(".wcr-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#wcr_series').val() == '' || $('#wcr_series').val() == '0' || $('#wcr_series').val() === null || $('#wcr_series')
            .val() === undefined)) {
        series_chk = 0;
        $("#wcr_series").after(
            '<span class="error-message wcr-series-err">Series is required!</span>');
    } else {
        $(".wcr-series-err").remove();
        series_chk = 1;
    }

    if (($('#wcr_rec_name').val() == '' || $('#wcr_rec_name').val() === null || $('#wcr_rec_name')
            .val() === undefined)) {
        rec_name_chk = 0;
        $("#wcr_rec_name").after(
            '<span class="error-message wcr-rec-name-err">Shipping Receiver Name is required!</span>');
    } else {
        $(".wcr-rec-name-err").remove();
        rec_name_chk = 1;
    }

    if (($('#wcr_rec_ctc').val() == '' || $('#wcr_rec_ctc').val() === null || $('#wcr_rec_ctc')
            .val() === undefined)) {
        rec_ctc_chk = 0;
        $("#wcr_rec_ctc").after(
            '<span class="error-message wcr-rec-ctc-err">Shipping Receiver Contact is required!</span>');
    } else {
        $(".wcr-rec-ctc-err").remove();
        rec_ctc_chk = 1;
    }

    if (($('#wcr_rec_add').val() == '' || $('#wcr_rec_add').val() === null || $('#wcr_rec_add')
            .val() === undefined)) {
        rec_add_chk = 0;
        $("#wcr_rec_add").after(
            '<span class="error-message wcr-rec-ctc-err">Shipping Receiver Address is required!</span>');
    } else {
        $(".wcr-rec-add-err").remove();
        rec_add_chk = 1;
    }

    if (name_chk == 1 && ctc_chk == 1 && pic_chk == 1 && country_chk == 1 && brand_chk == 1 && series_chk == 1 && rec_name_chk == 1 && rec_add_chk == 1 && rec_ctc_chk == 1)
        $(this).closest('fcbm').submit();
    else
        return false;

})