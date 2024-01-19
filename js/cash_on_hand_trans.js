$('#coh_attach').on('change', function() {
    previewImage(this, 'coh_attach_preview')
})

//autocomplete
$(document).ready(function() {

    if (!($("#coh_pic").attr('disabled'))) {
        $("#coh_pic").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= USR_USER ?>', // json filename (generated when login)
            }
            console.log(param["elementID"]);
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
})

//jQuery form validation
$("#coh_type").on("input", function() {
    $(".coh-type-err").remove();
});

$("#coh_pic").on("input", function() {
    $(".coh-pic-err").remove();
});

$("#coh_date").on("input", function() {
    $(".coh-date-err").remove();
});

$("#coh_bank").on("input", function() {
    $(".coh-bank-err").remove();
});

$("#coh_currency").on("input", function() {
    $(".coh-curr-err").remove();
});

$("#coh_amt").on("input", function() {
    $(".coh-amt-err").remove();
});

$("#coh_desc").on("input", function() {
    $(".coh-desc-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var type_chk = 0;
    var pic_chk = 0;
    var date_chk = 0;
    var bank_chk = 0;
    var currency_chk = 0;
    var amt_chk = 0;
    var desc_chk = 0;

    if ($('#coh_type').val() === '' || $('#coh_type').val() === null || $('#coh_type')
        .val() === undefined) {
        type_chk = 0;
        $("#coh_type").after(
            '<span class="error-message coh-type-err">Type is required!</span>');
    } else {
        $(".coh-type-err").remove();
        type_chk = 1;
    }

    if (($('#coh_pic').val() === '' || $('#coh_pic').val() === null || $('#coh_pic')
            .val() === undefined)) {
        pic_chk = 0;
        $("#coh_pic").after(
            '<span class="error-message coh-pic-err">Person-in-charge is required!</span>');
    } else {
        $(".coh-pic-err").remove();
        pic_chk = 1;
    }

    if (($('#coh_date').val() === '' || $('#coh_date').val() === null || $('#coh_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#coh_date").after(
            '<span class="error-message coh-date-err">Date is required!</span>');
    } else {
        $(".coh-date-err").remove();
        date_chk = 1;
    }

    if (($('#coh_bank').val() === '' || $('#coh_bank').val() === null || $('#coh_bank')
            .val() === undefined)) {
        bank_chk = 0;
        $("#coh_bank").after(
            '<span class="error-message coh-bank-err">Bank is required!</span>');
    } else {
        $(".coh-bank-err").remove();
        bank_chk = 1;
    }

    if (($('#coh_currency').val() === '' || $('#coh_currency').val() === null || $('#coh_currency')
            .val() === undefined)) {
        currency_chk = 0;
        $("#coh_currency").after(
            '<span class="error-message coh-curr-err">Currency is required!</span>');
    } else {
        $(".coh-curr-err").remove();
        currency_chk = 1;
    }

    if (($('#coh_amt').val() == '' || $('#coh_amt').val() == '0' || $('#coh_amt').val() === null || $('#coh_amt')
            .val() === undefined)) {
        amt_chk = 0;
        $("#coh_amt").after(
            '<span class="error-message coh-amt-err">Amount is required!</span>');
    } else {
        $(".coh-amt-err").remove();
        amt_chk = 1;
    }

    if (($('#coh_desc').val() == '' || $('#coh_desc').val() === null || $('#coh_desc')
            .val() === undefined)) {
        desc_chk = 0;
        $("#coh_desc").after(
            '<span class="error-message coh-desc-err">Description is required!</span>');
    } else {
        $(".coh-desc-err").remove();
        desc_chk = 1;
    }

    if (type_chk == 1 && pic_chk == 1 && date_chk == 1 && bank_chk == 1 && currency_chk == 1 && amt_chk == 1 && desc_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})