function showNotification(message) {
    $("#notification").text(message).fadeIn().delay(3000).fadeOut();
}
$(document).ready(function () {
    var barcode = '<?=$barcode?>';
    var stock_rec_count = '<?= count($stock_rec) ?>';
    var prod_barcode_slot_total = '<?= $prod_barcode_slot_total ?>';
    function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    var pkg = getParameterByName('pkg_id');
    var usr = getParameterByName('usr_id');
    var whse = getParameterByName('whse_id');
  
    if (!($("#order_id").attr('disabled'))) {
        $("#order_id").keyup(function () {
            var searchValue = $(this).val();
    
            var searchParams = {
                search: searchValue,
                searchTypes: 'orderID', // Array of search types
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: 'orderid',
                pkgID: pkg,
                usrID: usr,
                whseID: whse,

            };
            
            searchInput2(searchParams, '<?= $SITEURL ?>');
        });
    }
    
    
});

// Form submission 
$('#submitBtn').on('click', function () {
    var barcodeInputs = $('[name="barcode_input[]"]').toArray();
    var isValid = true;

    if (isValid) {
        $('#stockForm').submit();
    } else {
        showNotification('Please fill in all required fields.');
    }
});
$("#barcode_input_<?=$x?>").on("input", function() {
    $("#barcode_input_err").remove();
});

$("#expire_date").on("input", function() {
    $("#expire_date_err").remove();
});
$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var expire_chk = 0;
    var date_chk = 0;

    
   

    if ($('#expire_date').val() === '' || $('#expire_date').val() === null || $('#expire_date')
        .val() === undefined) {
            date_chk = 0;
        $("#expire_date").after(
            '<span class="error-message expire_date_err">Product Expire Date is required!</span>');
    } else {
        $(".expire_date_err").remove();
        date_chk = 1;
    }
    if (date_chk == 1)
        $(this).closest('form').submit();
    else
        return false;
});