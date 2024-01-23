var page = "<?= $pageTitle ?>";
var action = "<?php echo isset($act) ? $act : ''; ?>";

checkCurrentPage(page, action);
setButtonColor();
setAutofocus(action);
preloader(300, action);

//autocomplete
$(document).ready(function() {

    if (!($("#ici_pic").attr('disabled'))) {
        $("#ici_pic").keyup(function() {
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

    if (!($("#ici_brand").attr('disabled'))) {
        $("#ici_brand").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'brand', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= BRAND ?>', // json filename (generated when login)
            }
            console.log(param["elementID"]);
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

if (!($("#ici_package").attr('disabled'))) {
    $("#ici_package").keyup(function() {
        var param = {
            search: $(this).val(),
            searchType: 'package', // column of the table
            elementID: $(this).attr('id'), // id of the input
            hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
            dbTable: '<?= PKG ?>', // json filename (generated when login)
        }
        console.log(param["elementID"]);
        searchInput(param, '<?= $SITEURL ?>');
    });

}
})

//jQuery form validation
$("#ici_date").on("input", function() {
    $(".ici-date-err").remove();
});

$("#ici_pic").on("input", function() {
    $(".ici-pic-err").remove();
});

$("#ici_pic").on("input", function() {
    $(".ici-brand-err").remove();
});

$("#ici_package").on("input", function() {
    $(".ici-package-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var date_chk = 0;
    var pic_chk = 0;
    var brand_chk = 0;
    var package_chk = 0;
 

    if (($('#ici_date').val() === '' || $('#ici_date').val() === null || $('#ici_date')
            .val() === undefined)) {
        date_chk = 0;
        $("#ici_date").after(
            '<span class="error-message ici-date-err">Date is required!</span>');
    } else {
        $(".ici-date-err").remove();
        date_chk = 1;
    }

    if (($('#ici_pic').val() === '' || $('#ici_pic').val() === null || $('#ici_pic')
            .val() === undefined)) {
        pic_chk = 0;
        $("#ici_pic").after(
            '<span class="error-message ici-pic-err">Person-in-charge is required!</span>');
    } else {
        $(".ici-pic-err").remove();
        pic_chk = 1;
    }

    if (($('#ici_brand').val() === '' || $('#ici_brand').val() === null || $('#ici_brand')
            .val() === undefined)) {
        brand_chk = 0;
        $("#ici_brand").after(
            '<span class="error-message ici-brand-err">Brand is required!</span>');
    } else {
        $(".ici-brand-err").remove();
        brand_chk = 1;
    }

    if (($('#ici_package').val() === '' || $('#ici_package').val() === null || $('#ici_package')
            .val() === undefined)) {
        package_chk = 0;
        $("#ici_package").after(
            '<span class="error-message ici-package-err">Package is required!</span>');
    } else {
        $(".ici-package-err").remove();
        package_chk = 1;
    }

    if (pic_chk == 1 && date_chk == 1 && brand_chk == 1 && package_chk == 1)
        $(this).closest('form').submit();
    else
        return false;

})