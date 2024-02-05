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
})

//jQuery form validation
$("#swt_date").on("input", function() {
    $(".swt-date-err").remove();
});

$("#swt_id").on("input", function() {
    $(".swt-id-err").remove();
});

$("#swt_amt").on("input", function() {
    $(".swt-amt-err").remove();
});

$("#swt_pic").on("input", function() {
    $(".swt-pic-err").remove();
});


$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var date_chk = 0;
    var id_chk = 0;
    var amt_chk = 0;
    var pic_chk = 0;
    


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
            '<span class="error-message swt-id-err">ID is required!</span>');
    } else {
        $(".swt-id-err").remove();
        id_chk = 1;
    }


    if (($('#swt_amt').val() == '' || $('#swt_amt').val() == '0' || $('#swt_amt').val() === null || $('#swt_amt')
            .val() === undefined)) {
        amt_chk = 0;
        $("#swt_amt").after(
            '<span class="error-message coh-amt-err">Amount is required!</span>');
    } else {
        $(".swt-amt-err").remove();
        amt_chk = 1;
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


    if (date_chk == 1 && id_chk == 1 && amt_chk == 1 && pic_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})