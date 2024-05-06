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
$("#wcr_cust_id").on("input", function() {
    $(".wcr-cust-id-err").remove();
});

$("#wcr_name").on("input", function() {
    $(".wcr-name-err").remove();
});

$("#wcr_contact").on("input", function() {
    $(".wcr-contact-err").remove();
});

$("#wcr_cust_email").on("input", function() {
    $(".wcr-cust-email-err").remove();
});

$("#wcr_cust_birthday").on("input", function() {
    $(".wcr-cust-birthday-err").remove();
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
    var cust_id_chk = 0;
    var name_chk = 0;
    var ctc_chk = 0;
    var cust_email_chk = 0;
    var cust_birthday_chk = 0;
    var pic_chk = 0;
    var country_chk = 0;
    var brand_chk = 0;
    var series_chk = 0;
    var rec_name_chk = 0;
    var rec_ctc_chk = 0;
    var rec_add_chk = 0;

    if ($('#wcr_cust_id').val() === '' || $('#wcr_cust_id').val() === null || $('#wcr_cust_id')
        .val() === undefined) {
        cust_id_chk = 0;
        $("#wcr_cust_id").after(
            '<span class="error-message wcr-cust-id-err">Customer ID is required!</span>');
    } else {
        $(".wcr-cust-id-err").remove();
        cust_id_chk = 1;
    }

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

    if ($('#wcr_cust_email').val() === '' || $('#wcr_cust_email').val() === null || $('#wcr_cust_email')
        .val() === undefined) {
        cust_email_chk = 0;
        $("#wcr_cust_email").after(
            '<span class="error-message wcr-cust-email-err">Customer Email is required!</span>');
    } else {
        $(".wcr-cust-email-err").remove();
        cust_email_chk = 1;
    }

    var wcr_cust_birthday = $('#wcr_cust_birthday').val();
    var today = new Date().toISOString().slice(0, 10);

        if (wcr_cust_birthday === '' || wcr_cust_birthday === null || wcr_cust_birthday === today ||wcr_cust_birthday === undefined) {
            cust_birthday_chk = 0;
            $("#wcr_cust_birthday").after(
                '<span class="error-message wcr-cust-id-err">Customer Birthday is required!</span>');
        } else {
            $(".wcr-cust-birthday-err").remove();
            cust_birthday_chk = 1;
        }
        
    if (($('#wcr_pic').val() === ''  || $('#wcr_pic').val() == '0' || $('#wcr_pic').val() === null || $('#wcr_pic_hidden')
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

    if (cust_id_chk == 1 && name_chk == 1 && ctc_chk == 1 && cust_email_chk == 1 && cust_birthday_chk == 1 && pic_chk == 1 && country_chk == 1 && brand_chk == 1 && series_chk == 1 && rec_name_chk == 1 && rec_add_chk == 1 && rec_ctc_chk == 1)
        $(this).closest('fcbm').submit();
    else
        return false;

})