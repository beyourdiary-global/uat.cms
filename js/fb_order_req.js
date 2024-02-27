$('#for_attach').on('change', function() {
    previewImage(this, 'for_attach_preview')
})

//autocomplete
$(document).ready(function() {

    if (!($("#for_pic").attr('disabled'))) {
        $("#for_pic").keyup(function() {
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
    //country
    if (!($("#for_country").attr('disabled'))) {
        $("#for_country").keyup(function() {
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
    //brand
    if (!($("#for_brand").attr('disabled'))) {
        $("#for_brand").keyup(function() {
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
    //series
    if (!($("#for_series").attr('disabled'))) {
        $("#for_series").keyup(function() {
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
    //package
    if (!($("#for_pkg").attr('disabled'))) {
        $("#for_pkg").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= PKG ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    //fb page account
    if (!($("#for_fbpage").attr('disabled'))) {
        $("#for_fbpage").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= FB_PAGE_ACC ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    // channel
    if (!($("#for_channel").attr('disabled'))) {
        $("#for_channel").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= CHANEL_SC_MD ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
    //payment method
    if (!($("#for_pay_meth").attr('disabled'))) {
        $("#for_pay_meth").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= FIN_PAY_METH ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    $("#for_pkg").keyup(function() {
        // Empty the #for_price field
        $("#for_price").val('');
    });
    $("#for_pkg").change(calculatePrice);
})

function calculatePrice() {
    var paramPkg = {
        search: $("#for_pkg_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= PKG ?>',
        isFin: 0,
    };

    retrieveDBData(paramPkg, '<?= $SITEURL ?>', function (result) {
        if (result && result.length > 0) {
            var pkg_price = parseFloat(result[0]['price']);
            var pkg_curr = result[0]['currency_unit'];
            console.log('curr', pkg_curr);
            $("#for_price").val(pkg_price.toFixed(2));

            var paramCountry = {
                search: country,
                searchCol: 'country',
                searchType: '*',
                dbTable: '<?= TAX_SETT ?>',
                isFin: 1,
            };

            retrieveDBData(paramTaxSetting, '<?= $SITEURL ?>', function (result) {
                handleTaxSettingData(result);
            });
        } else {
            console.error('Error retrieving Courier data');
        }
    });
}

//jQuery form validation
$("#for_name").on("input", function() {
    $(".for-name-err").remove();
});

$("#for_link").on("input", function() {
    $(".for-link-err").remove();
});

$("#for_contact").on("input", function() {
    $(".for-contact-err").remove();
});

$("#for_pic").on("input", function() {
    $(".for-pic-err").remove();
});

$("#for_country").on("input", function() {
    $(".for-country-err").remove();
});

$("#for_brand").on("input", function() {
    $(".for-brand-err").remove();
});

$("#for_series").on("input", function() {
    $(".for-series-err").remove();
});

$("#for_pkg").on("input", function() {
    $(".for-pkg-err").remove();
});

$("#for_fbpage").on("input", function() {
    $(".for-fbpage-err").remove();
});

$("#for_channel").on("input", function() {
    $(".for-channel-err").remove();
});

$("#for_price").on("input", function() {
    $(".for-price-err").remove();
});

$("#for_pay_meth").on("input", function() {
    $(".for-pay-err").remove();
});

$("#for_rec_name").on("input", function() {
    $(".for-rec-name-err").remove();
});

$("#for_rec_ctc").on("input", function() {
    $(".for-rec-ctc-err").remove();
});

$("#for_rec_add").on("input", function() {
    $(".for-rec-add-err").remove();
});

$("#for_attach").on("input", function() {
    $(".for-attach-err").remove();
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
    var pkg_chk = 0;
    var fbpage_chk = 0;
    var channel_chk = 0;
    var price_chk = 0;
    var pay_chk = 0;
    var rec_name_chk = 0;
    var rec_ctc_chk = 0;
    var rec_add_chk = 0;
    var attach_chk = 0;

    if ($('#for_name').val() === '' || $('#for_name').val() === null || $('#for_name')
        .val() === undefined) {
        name_chk = 0;
        $("#for_name").after(
            '<span class="error-message for-name-err">Name is required!</span>');
    } else {
        $(".for-name-err").remove();
        name_chk = 1;
    }

    if (($('#for_link').val() === '' || $('#for_link').val() === null || $('#for_link')
            .val() === undefined)) {
        link_chk = 0;
        $("#for_link").after(
            '<span class="error-message for-link-err">Facebook Link is required!</span>');
    } else {
        $(".for-link-err").remove();
        link_chk = 1;
    }

    if (($('#for_contact').val() === '' || $('#for_contact').val() === null || $('#for_contact')
            .val() === undefined)) {
        ctc_chk = 0;
        $("#for_contact").after(
            '<span class="error-message for-contact-err">Contact is required!</span>');
    } else {
        $(".for-contact-err").remove();
        ctc_chk = 1;
    }

    if (($('#for_pic_hidden').val() === ''  || $('#for_pic_hidden').val() == '0' || $('#for_pic_hidden').val() === null || $('#for_pic_hidden')
            .val() === undefined)) {
        pic_chk = 0;
        $("#for_pic").after(
            '<span class="error-message for-pic-err">Sales Person-In-Charge is required!</span>');
    } else {
        $(".for-pic-err").remove();
        pic_chk = 1;
    }


    if (($('#for_country_hidden').val() == '' || $('#for_country_hidden').val() == '0' || $('#for_country_hidden').val() === null || $('#for_country_hidden')
            .val() === undefined)) {
        country_chk = 0;
        $("#for_country").after(
            '<span class="error-message for-country-err">Country is required!</span>');
    } else {
        $(".for-country-err").remove();
        country_chk = 1;
    }

    if (($('#for_brand_hidden').val() == '' || $('#for_brand_hidden').val() == '0' || $('#for_brand_hidden').val() === null || $('#for_brand_hidden')
            .val() === undefined)) {
        brand_chk = 0;
        $("#for_brand").after(
            '<span class="error-message for-brand-err">Brand is required!</span>');
    } else {
        $(".for-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#for_series_hidden').val() == '' || $('#for_series_hidden').val() == '0' || $('#for_series_hidden').val() === null || $('#for_series_hidden')
            .val() === undefined)) {
        series_chk = 0;
        $("#for_series").after(
            '<span class="error-message for-series-err">Series is required!</span>');
    } else {
        $(".for-series-err").remove();
        series_chk = 1;
    }

    if (($('#for_pkg_hidden').val() == '' || $('#for_pkg_hidden').val() == '0' || $('#for_pkg_hidden').val() === null || $('#for_pkg_hidden')
            .val() === undefined)) {
        pkg_chk = 0;
        $("#for_pkg").after(
            '<span class="error-message for-pkg-err">Package is required!</span>');
    } else {
        $(".for-pkg-err").remove();
        pkg_chk = 1;
    }

    if (($('#for_fbpage_hidden').val() == '' || $('#for_fbpage_hidden').val() == '0' || $('#for_fbpage_hidden').val() === null || $('#for_fbpage_hidden')
            .val() === undefined)) {
        fbpage_chk = 0;
        $("#for_fbpage").after(
            '<span class="error-message for-fbpage-err">Facebook Page is required!</span>');
    } else {
        $(".for-fbpage-err").remove();
        fbpage_chk = 1;
    }

    if (($('#for_channel_hidden').val() == '' || $('#for_channel_hidden').val() == '0' || $('#for_channel_hidden').val() === null || $('#for_channel_hidden')
            .val() === undefined)) {
        channel_chk = 0;
        $("#for_channel").after(
            '<span class="error-message for-channel-err">Channel is required!</span>');
    } else {
        $(".for-channel-err").remove();
        channel_chk = 1;
    }

    if (($('#for_price').val() == '' || $('#for_price').val() == '0' || $('#for_price').val() === null || $('#for_price')
            .val() === undefined)) {
        price_chk = 0;
        $("#for_price").after(
            '<span class="error-message for-price-err">Price is required!</span>');
    } else {
        $(".for-price-err").remove();
        price_chk = 1;
    }

    if (($('#for_pay_meth_hidden').val() == '' || $('#for_pay_meth_hidden').val() == '0' || $('#for_pay_meth_hidden').val() === null || $('#for_pay_meth_hidden')
            .val() === undefined)) {
        pay_chk = 0;
        $("#for_pay_meth").after(
            '<span class="error-message for-pay-err">Payment Method is required!</span>');
    } else {
        $(".for-pay-err").remove();
        pay_chk = 1;
    }

    if (($('#for_rec_name').val() == '' || $('#for_rec_name').val() === null || $('#for_rec_name')
            .val() === undefined)) {
        rec_name_chk = 0;
        $("#for_rec_name").after(
            '<span class="error-message for-rec-name-err">Shipping Receiver Name is required!</span>');
    } else {
        $(".for-rec-name-err").remove();
        rec_name_chk = 1;
    }

    if (($('#for_rec_ctc').val() == '' || $('#for_rec_ctc').val() === null || $('#for_rec_ctc')
            .val() === undefined)) {
        rec_ctc_chk = 0;
        $("#for_rec_ctc").after(
            '<span class="error-message for-rec-ctc-err">Shipping Receiver Contact is required!</span>');
    } else {
        $(".for-rec-ctc-err").remove();
        rec_ctc_chk = 1;
    }

    if (($('#for_rec_add').val() == '' || $('#for_rec_add').val() === null || $('#for_rec_add')
            .val() === undefined)) {
        rec_add_chk = 0;
        $("#for_rec_add").after(
            '<span class="error-message for-rec-ctc-err">Shipping Receiver Address is required!</span>');
    } else {
        $(".for-rec-add-err").remove();
        rec_add_chk = 1;
    }


    var fileInput = $('#for_attach')[0];
    console.log($('#for_attachmentValue').val());
    // Check if a new file is selected or if there is an existing attachment
    if ((fileInput.files.length === 0) && ($('#for_attachmentValue').val() == '' || $('#for_attachmentValue').val() == '0' || $('#for_attachmentValue').val() === null || $('#for_attachmentValue')
    .val() === undefined)) {
        // No file selected and no existing attachment
        attach_chk = 0;
        $("#for_attach").after('<span class="error-message for-attach-err">Attachment is required!</span>');
    } else {
        // File selected or existing attachment present
        attach_chk = 1;
    }

    if (name_chk == 1 && link_chk == 1 && ctc_chk == 1 && pic_chk == 1 && country_chk == 1 && brand_chk == 1 && series_chk == 1 && pkg_chk == 1 && fbpage_chk == 1 && channel_chk == 1 && price_chk == 1 && pay_chk == 1 && rec_name_chk == 1 && rec_add_chk == 1 && rec_ctc_chk == 1 && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})