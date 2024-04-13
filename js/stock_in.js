function showNotification(message) {
    $("#notification").text(message).fadeIn().delay(3000).fadeOut();
}

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

    
    $('[id^="barcode_input_"]').each(function() {
        var barcodeInputId = $(this).attr('id');
        var barcodeInputValue = $(this).val();

        if (barcodeInputValue === '' || barcodeInputValue === null || barcodeInputValue === undefined) {
            expire_chk = 0;
            $(this).after('<span class="error-message barcode_input_err">Barcode is required for this product!</span>');
        } else {
            $(".barcode_input_err").remove();
            expire_chk = 1;
        }
    });

    if ($('#expire_date').val() === '' || $('#expire_date').val() === null || $('#expire_date')
        .val() === undefined) {
            date_chk = 0;
        $("#expire_date").after(
            '<span class="error-message expire_date_err">Product Expire Date is required!</span>');
    } else {
        $(".expire_date_err").remove();
        date_chk = 1;
    }
    if (expire_chk == 1 && date_chk == 1)
        $(this).closest('form').submit();
    else
        return false;
});