//autocomplete
$(document).ready(function() {

    if (!($("#fcb_pic").attr('disabled'))) {
        $("#fcb_pic").keyup(function() {
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
    if (!($("#fcb_country").attr('disabled'))) {
        $("#fcb_country").keyup(function() {
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
    if (!($("#fcb_brand").attr('disabled'))) {
        $("#fcb_brand").keyup(function() {
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
    //series
    if (!($("#fcb_series").attr('disabled'))) {
        $("#fcb_series").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= BRD_SERIES ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    //fb page account
    if (!($("#fcb_fbpage").attr('disabled'))) {
        $("#fcb_fbpage").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= FB_PAGE_ACC ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    // channel
    if (!($("#fcb_channel").attr('disabled'))) {
        $("#fcb_channel").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
                dbTable: '<?= CHANNEL ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
})

//jQuery fcbm validation
$("#fcb_name").on("input", function() {
    $(".fcb-name-err").remove();
});

$("#fcb_link").on("input", function() {
    $(".fcb-link-err").remove();
});

$("#fcb_contact").on("input", function() {
    $(".fcb-contact-err").remove();
});

$("#fcb_pic").on("input", function() {
    $(".fcb-pic-err").remove();
});

$("#fcb_country").on("input", function() {
    $(".fcb-country-err").remove();
});

$("#fcb_brand").on("input", function() {
    $(".fcb-brand-err").remove();
});

$("#fcb_series").on("input", function() {
    $(".fcb-series-err").remove();
});

$("#fcb_fbpage").on("input", function() {
    $(".fcb-fbpage-err").remove();
});

$("#fcb_channel").on("input", function() {
    $(".fcb-channel-err").remove();
});

$("#fcb_rec_name").on("input", function() {
    $(".fcb-rec-name-err").remove();
});

$("#fcb_rec_ctc").on("input", function() {
    $(".fcb-rec-ctc-err").remove();
});

$("#fcb_rec_add").on("input", function() {
    $(".fcb-rec-add-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    var name_chk = 0;
    var link_chk = 0;
    var ctc_chk = 0;
    var pic_chk = 0;
    var country_chk = 0;
    var brand_chk = 0;
    var series_chk = 0;
    var fbpage_chk = 0;
    var channel_chk = 0;
    var rec_name_chk = 0;
    var rec_ctc_chk = 0;
    var rec_add_chk = 0;

    if ($('#fcb_name').val() === '' || $('#fcb_name').val() === null || $('#fcb_name')
        .val() === undefined) {
        name_chk = 0;
        $("#fcb_name").after(
            '<span class="error-message fcb-name-err">Name is required!</span>');
    } else {
        $(".fcb-name-err").remove();
        name_chk = 1;
    }

    if (($('#fcb_link').val() === '' || $('#fcb_link').val() === null || $('#fcb_link')
            .val() === undefined)) {
        link_chk = 0;
        $("#fcb_link").after(
            '<span class="error-message fcb-link-err">Facebook Link is required!</span>');
    } else {
        $(".fcb-link-err").remove();
        link_chk = 1;
    }

    if (($('#fcb_contact').val() === '' || $('#fcb_contact').val() === null || $('#fcb_contact')
            .val() === undefined)) {
        ctc_chk = 0;
        $("#fcb_contact").after(
            '<span class="error-message fcb-contact-err">Contact is required!</span>');
    } else {
        $(".fcb-contact-err").remove();
        ctc_chk = 1;
    }

    if (($('#fcb_pic_hidden').val() === ''  || $('#fcb_pic_hidden').val() == '0' || $('#fcb_pic_hidden').val() === null || $('#fcb_pic_hidden')
            .val() === undefined)) {
        pic_chk = 0;
        $("#fcb_pic").after(
            '<span class="error-message fcb-pic-err">Sales Person-In-Charge is required!</span>');
    } else {
        $(".fcb-pic-err").remove();
        pic_chk = 1;
    }


    if (($('#fcb_country_hidden').val() == '' || $('#fcb_country_hidden').val() == '0' || $('#fcb_country_hidden').val() === null || $('#fcb_country_hidden')
            .val() === undefined)) {
        country_chk = 0;
        $("#fcb_country").after(
            '<span class="error-message fcb-country-err">Country is required!</span>');
    } else {
        $(".fcb-country-err").remove();
        country_chk = 1;
    }

    if (($('#fcb_brand_hidden').val() == '' || $('#fcb_brand_hidden').val() == '0' || $('#fcb_brand_hidden').val() === null || $('#fcb_brand_hidden')
            .val() === undefined)) {
        brand_chk = 0;
        $("#fcb_brand").after(
            '<span class="error-message fcb-brand-err">Brand is required!</span>');
    } else {
        $(".fcb-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#fcb_series_hidden').val() == '' || $('#fcb_series_hidden').val() == '0' || $('#fcb_series_hidden').val() === null || $('#fcb_series_hidden')
            .val() === undefined)) {
        series_chk = 0;
        $("#fcb_series").after(
            '<span class="error-message fcb-series-err">Series is required!</span>');
    } else {
        $(".fcb-series-err").remove();
        series_chk = 1;
    }

    if (($('#fcb_fbpage_hidden').val() == '' || $('#fcb_fbpage_hidden').val() == '0' || $('#fcb_fbpage_hidden').val() === null || $('#fcb_fbpage_hidden')
            .val() === undefined)) {
        fbpage_chk = 0;
        $("#fcb_fbpage").after(
            '<span class="error-message fcb-fbpage-err">Facebook Page is required!</span>');
    } else {
        $(".fcb-fbpage-err").remove();
        fbpage_chk = 1;
    }

    if (($('#fcb_channel_hidden').val() == '' || $('#fcb_channel_hidden').val() == '0' || $('#fcb_channel_hidden').val() === null || $('#fcb_channel_hidden')
            .val() === undefined)) {
        channel_chk = 0;
        $("#fcb_channel").after(
            '<span class="error-message fcb-channel-err">Channel is required!</span>');
    } else {
        $(".fcb-channel-err").remove();
        channel_chk = 1;
    }

    if (($('#fcb_rec_name').val() == '' || $('#fcb_rec_name').val() === null || $('#fcb_rec_name')
            .val() === undefined)) {
        rec_name_chk = 0;
        $("#fcb_rec_name").after(
            '<span class="error-message fcb-rec-name-err">Shipping Receiver Name is required!</span>');
    } else {
        $(".fcb-rec-name-err").remove();
        rec_name_chk = 1;
    }

    if (($('#fcb_rec_ctc').val() == '' || $('#fcb_rec_ctc').val() === null || $('#fcb_rec_ctc')
            .val() === undefined)) {
        rec_ctc_chk = 0;
        $("#fcb_rec_ctc").after(
            '<span class="error-message fcb-rec-ctc-err">Shipping Receiver Contact is required!</span>');
    } else {
        $(".fcb-rec-ctc-err").remove();
        rec_ctc_chk = 1;
    }

    if (($('#fcb_rec_add').val() == '' || $('#fcb_rec_add').val() === null || $('#fcb_rec_add')
            .val() === undefined)) {
        rec_add_chk = 0;
        $("#fcb_rec_add").after(
            '<span class="error-message fcb-rec-ctc-err">Shipping Receiver Address is required!</span>');
    } else {
        $(".fcb-rec-add-err").remove();
        rec_add_chk = 1;
    }

    if (name_chk == 1 && link_chk == 1 && ctc_chk == 1 && pic_chk == 1 && country_chk == 1 && brand_chk == 1 && series_chk == 1 && fbpage_chk == 1 && channel_chk == 1 && rec_name_chk == 1 && rec_add_chk == 1 && rec_ctc_chk == 1)
        $(this).closest('fcbm').submit();
    else
        return false;

})