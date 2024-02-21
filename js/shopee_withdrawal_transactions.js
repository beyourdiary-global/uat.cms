$('#swt_attach').on('change', function() {
    previewImage(this, 'swt_attach_preview')
})

//autocomplete
$(document).ready(function() {

    if (!($("#swt_pic").attr('disabled'))) {
        $("#swt_pic").keyup(function() {
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

    if (!($("#curr").attr('disabled'))) {
        $("#curr").keyup(function() {
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

//jQuery form validation
$("#swt_date").on("input", function() {
    $(".swt-date-err").remove();
});

$("#swt_id").on("input", function() {
    $(".swt-id-err").remove();
});

$("#swt_pic").on("input", function() {
    $(".swt-pic-err").remove();
});

$("#curr").on("input", function() {
    $(".curr-err").remove();
});

$("#swt_attach").on("input", function() {
    $(".swt-attach-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var date_chk = 0;
    var id_chk = 0;
    var pic_chk = 0;
    var curr_chk = 0;
    var attach_chk = 0;


    if (($('#swt_date').val() === '' || $('#swt_date').val() === null || $('#swt_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#swt_date").after(
            '<span class="error-message swt-date-err">Date is required!</span>');
    } else {
        $(".swt-date-err").remove();
        date_chk = 1;
    }

    if (($('#swt_id').val() === '' || $('#swt_id').val() === null || $('#swt_id')
            .val() === undefined)) {
        id_chk = 0;
        $("#swt_id").after(
            '<span class="error-message swt-id-err">Withdrawal ID is required!</span>');
    } else {
        $(".swt-id-err").remove();
        id_chk = 1;
    }

    if (($('#swt_pic').val() === '' || $('#swt_pic').val() === null || $('#swt_pic')
            .val() === undefined)) {
        pic_chk = 0;
        $("#swt_pic").after(
            '<span class="error-message swt-pic-err">Person-in-charge is required!</span>');
    } else {
        $(".swt-pic-err").remove();
        pic_chk = 1;
    }

     if (($('#curr').val() === '' || $('#curr').val() === null || $('#curr')
            .val() === undefined)) {
        curr_chk = 0;
        $("#curr").after(
            '<span class="error-message curr-err">Currency Unit is required!</span>');
    } else {
        $(".curr-err").remove();
        curr_chk = 1;
    }
    
    if ($('#swt_attach').prop('files').length > 0 || $('#swt_attachmentValue').val() !== '') {
        attach_chk = 1;
    } else {
        attach_chk = 0;
        $("#swt_attach").after('<span class="error-message swt-attach-err">Attachment is required!</span>');
    }

    if (date_chk == 1 && id_chk == 1 && pic_chk == 1 && curr_chk == 1 && attach_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})