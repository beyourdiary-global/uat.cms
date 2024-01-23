var page = "<?= $pageTitle ?>";
var action = "<?php echo isset($act) ? $act : ''; ?>";

checkCurrentPage(page, action);
setButtonColor();
setAutofocus(action);
preloader(300, action);

$('#fat_attach').on('change', function() {
    previewImage(this, 'fat_attach_preview')
})

//autocomplete
$(document).ready(function() {

    if (!($("#fat_pic").attr('disabled'))) {
        $("#fat_pic").keyup(function() {
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

    if (!($("#fat_meta_acc").attr('disabled'))) {
        $("#fat_meta_acc").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'accName', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= META_ADS_ACC ?>', // json filename (generated when login)
            }
            console.log(param["elementID"]);
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
})

//jQuery form validation
$("#fat_meta_acc").on("input", function() {
    $(".fat-acc-err").remove();
});

$("#fat_trans_id").on("input", function() {
    $(".fat-id-err").remove();
});

$("#fat_date").on("input", function() {
    $(".fat-date-err").remove();
});

$("#fat_pic").on("input", function() {
    $(".fat-pic-err").remove();
});

$("#fat_topup_amt").on("input", function() {
    $(".fat-amt-err").remove();
});

$("#fat_attach").on("input", function() {
    $(".fat-attach-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var acc_chk = 0;
    var id_chk = 0;
    var date_chk = 0;
    var pic_chk = 0;
    var amt_chk = 0;
    var attach_chk = 0;

    if ($('#fat_meta_acc').val() === '' || $('#fat_meta_acc').val() === null || $('#fat_meta_acc')
        .val() === undefined) {
        acc_chk = 0;
        $("#fat_meta_acc").after(
            '<span class="error-message fat-acc-err">Account is required!</span>');
    } else {
        $(".fat-acc-err").remove();
        acc_chk = 1;
    }

    if (($('#fat_trans_id').val() === '' || $('#fat_trans_id').val() === null || $('#fat_trans_id')
            .val() === undefined)) {
        id_chk = 0;
        $("#fat_trans_id").after(
            '<span class="error-message fat-id-err">Transaction ID is required!</span>');
    } else {
        $(".fat-id-err").remove();
        id_chk = 1;
    }

    if (($('#fat_pic').val() === '' || $('#fat_pic').val() === null || $('#fat_pic')
            .val() === undefined)) {
        pic_chk = 0;
        $("#fat_pic").after(
            '<span class="error-message fat-pic-err">Person-in-charge is required!</span>');
    } else {
        $(".fat-pic-err").remove();
        pic_chk = 1;
    }

    if (($('#fat_date').val() === '' || $('#fat_date').val() === null || $('#fat_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#fat_date").after(
            '<span class="error-message fat-date-err">Date is required!</span>');
    } else {
        $(".fat-date-err").remove();
        date_chk = 1;
    }

    if (($('#fat_amt').val() == '' || $('#fat_amt').val() == '0' || $('#fat_amt').val() === null || $('#fat_amt')
            .val() === undefined)) {
        amt_chk = 0;
        $("#fat_amt").after(
            '<span class="error-message fat-amt-err">Amount is required!</span>');
    } else {
        $(".fat-amt-err").remove();
        amt_chk = 1;
    }

    var fileInput = $('#fat_attach')[0];
    console.log($('#fat_attachmentValue').val());
    // Check if a new file is selected or if there is an existing attachment
    if ((fileInput.files.length === 0) && ($('#fat_attachmentValue').val() == '' || $('#fat_attachmentValue').val() == '0' || $('#fat_attachmentValue').val() === null || $('#fat_attachmentValue')
    .val() === undefined)) {
        // No file selected and no existing attachment
        attach_chk = 0;
        $("#fat_attach").after('<span class="error-message fat-attach-err">Attachment is required!</span>');
    } else {
        // File selected or existing attachment present
        attach_chk = 1;
    }

    if (acc_chk == 1 && id_chk == 1 && pic_chk == 1 && date_chk == 1 && amt_chk == 1 && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})