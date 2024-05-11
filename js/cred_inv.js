$("#cni_date").datepicker({
    format: "yyyy-mm-dd",
    orientation: "bottom",
    autoclose: true
});

$("#cni_due").datepicker({
    format: "yyyy-mm-dd",
    orientation: "bottom",
    autoclose: true
});

$('.createInvoiceButton').on('click', () => {
    $("#createInvoice").val(1);
});

$(document).ready(function () {
    calculateTotal();
    $('input[name="amount[]"]').on('input', calculateSubtotal);
    $("#cni_sub").on('change', calculateTotal);

    if (!($("#cni_name").attr('disabled'))) {
        $("#cni_name").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= MERCHANT ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
    if (!($("#cni_pic").attr('disabled'))) {
        $("#cni_pic").keyup(function () {
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
    if (!($("#cni_curr").attr('disabled'))) {
        $("#cni_curr").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'unit', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= CUR_UNIT ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });
    }

    if (!($("#prod_desc").attr('disabled'))) {
        $("#prod_desc").keyup(function () {
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

    
    $("#cni_name").change(autofillMrcht);

    $('#payment-terms').change(function () {
        if ($(this).is(':checked')) {
            $('#pay_terms').show();
        } else {
            // If the checkbox is unchecked, hide the payment terms dropdown
            $('#pay_terms').hide();
            // Clear the selected value of the payment terms dropdown
            $('#pay_terms').val('0');
        }
    });
    if (!$('#payment-terms').is(':checked')) {
        $('#pay_terms').hide();
    }

})
function autofillMrcht() {
    var paramMrcht = {
        search: $("#cni_name_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= MERCHANT ?>',
        isFin: 1,
    };

    retrieveDBData(paramMrcht, '<?= $SITEURL ?>', function (result) {
        if (result && result.length > 0) {
            $("#cni_email").val(result[0]['email']);
            $("#cni_address").val(result[0]['address']);
            $("#cni_ctc").val(result[0]['contact']);
        }
    });
}
$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    var cni_due_chk = 0;
    var cni_curr_chk = 0;
    var cni_name_chk = 0;
    var cni_ctc_chk = 0;
    var prod_desc_chk = 0;
    var price_chk = 0;
    var quantity_chk = 0;

    if ($('#cni_due').val() === '' || $('#cni_due').val() === null || $('#cni_due')
    .val() === undefined) {
        cni_due_chk = 0;
    $("#cni_due").after(
        '<span class="error-message date_err">Due Date is required!</span>');
    }  else {
        $(".date_err").remove();
        cni_due_chk = 1;
    }

    if ($('#cni_curr').val() === '' || $('#cni_curr').val() === null || $('#cni_curr')
    .val() === undefined) {
        cni_curr_chk = 0;
    $("#cni_curr").after(
        '<span class="error-message cni_curr">Currency is required!</span>');
    }  else {
        $(".curr_err").remove();
        cni_curr_chk = 1;
    }

    if ($('#cni_name').val() === '' || $('#cni_name').val() === null || $('#cni_name')
    .val() === undefined) {
        cni_name_chk = 0;
    $("#cni_name").after(
        '<span class="error-message cni_name">Name is required!</span>');
    }  else {
        $(".curr_err").remove();
        cni_name_chk = 1;
    }

  
    
    if ( cni_curr_chk == 1 && cni_due_chk == 1 && cni_name_chk == 1 )
        $(this).closest('fcbm').submit();
    else
        return false;
    

});

function Add() {
    AddRow($("#prod_desc").val(), $("#price").val(), $("#quantity").val(), $("#amount").val());
};

function AddRow() {
    //Get the reference of the Table's TBODY element.
    var tBody = $("#productList > TBODY")[0];
    var numbering = +$("#productList > TBODY > TR:last > TD:first").text();
    numbering += 1;
    numbering = numbering.toFixed(0)

    //Add Row.
    row = tBody.insertRow(-1);

    //Add cell.
    var cell = $(row.insertCell(-1));
    cell.html(numbering);
    var cell = $(row.insertCell(-1));
    cell.html('<label class="form-label form_lbl" for="prod_desc_' + numbering + '" hidden>Description<span class="required-dot">*</span></label>' +
    '<input type="text" name="prod_desc[]" id="prod_desc_' + numbering + '" value="" onkeyup="prodInfo(this)" required  >' +
    '<input type="hidden" name="prod_val[]" id="prod_val_' + numbering + '" value="" oninput="prodInfoAutoFill(this)">');
    cell.addClass('autocomplete');
    cell = $(row.insertCell(-1));
    cell.html('<label class="form-label form_lbl" for="price_' + numbering + '" hidden>Price<span class="required-dot">*</span></label>' +
    '<input type="text" name="price[]" id="price_' + numbering + '" value="" required  oninput="calculateAmount(' + numbering + ')">');
    cell = $(row.insertCell(-1));
    cell.html('<label class="form-label form_lbl" for="quantity_' + numbering + '" hidden>Quantity<span class="required-dot">*</span></label>' +
    '<input type="text" name="quantity[]" id="quantity_' + numbering + '" value="" required oninput="calculateAmount(' + numbering + ')">');
    cell = $(row.insertCell(-1));
    cell.html('<input  class="readonlyInput" type="text" name="amount[]" id="amount_' + numbering + '" value="">');

    //Add Button cell.
    cell = $(row.insertCell(-1));
    var btnRemove = $('<button class="mt-1" id="action_menu_btn"><i class="fa-regular fa-trash-can fa-xl" style="color:#ff0000"></i></button>');
    btnRemove.attr("type", "button");
    btnRemove.attr("onclick", "Remove(this);");
    btnRemove.val("Remove");
    cell.append(btnRemove);
};

function Remove(button) {
    // Determine the reference of the Row using the Button.
    var row = $(button).closest("tr");
    var name = $("td", row).eq(1).find("input[type='text']").val(); // Assuming description is in the second cell
    if (confirm("Do you want to delete: " + name)) {
        row.remove();
    }
    calculateSubtotal();
    calculateTotal();
}

// product autofill
function prodInfo(element) {
    var id = $(element).attr('id').split('_');
    id = id[(id.length) - 1];

    if (!($(element).attr('readonly'))) {
        var param = {
            search: $(element).val(),
            searchType: 'name',
            page: 'package',
            elementID: $(element).attr('id'),
            hiddenElementID: 'prod_val_' + id,
            dbTable: '<?= PROD ?>'
        }
        searchInput(param, '<?= $SITEURL ?>');

        if ($(element).val() == '') {
            $('#prod_val_' + id).val('');
            $('#wgt_' + id).val('');
            $('#wgt_unit_' + id).val('');
            $('#wgt_unit_val_' + id).val('');
            $('#barcode_status_' + id).val('');
            $('#barcode_slot_' + id).val('');
        }
    }
}
function prodInfoAutoFill(element) {
    var id = $(element).attr('id').split('_');
    id = id[(id.length) - 1];
    var prodArr = [];
    var wgtArr = [];
    var rowCount = parseInt($("#productList TBODY TR:last TD").eq(0).html());

    var retrieveProdInfo = async () => {
        prodArr = await retrieveJSONData($(element).attr('value'), 'id', '<?= PROD ?>');
    }

    var setProdInfo = async () => {
        $('#wgt_' + id).val(prodArr[0]['weight']);
        $('#wgt_unit_val_' + id).val(prodArr[0]['weight_unit']);
        $('#barcode_status_' + id).val(prodArr[0]['barcode_status']);
        $('#barcode_slot_' + id).val(prodArr[0]['barcode_slot']);
    }

    var retrieveWgtUnit = async () => {
        wgtArr = await retrieveJSONData($('#wgt_unit_val_' + id).attr('value'), 'id', '<?= WGT_UNIT ?>');
    }

    var setWgtUnit = async () => {
        $('#wgt_unit_' + id).val(wgtArr[0]['unit']);
    }

    var allFunc = async () => {
        await retrieveProdInfo();
        await setProdInfo();
        await retrieveWgtUnit();
        await setWgtUnit();
        await setBarcodeSlotTotal(rowCount);
    }

    allFunc();
}



function calculateTotal() {
    var subtotalInput = document.getElementById("cni_sub");
    var discountInput = document.getElementById("cni_disc");
    var taxInput = document.getElementById("cni_tax");
    var totalSpan = document.getElementById("cni_total");

    var subtotal = parseFloat(subtotalInput.value) || 0;
    var discount = parseFloat(discountInput.value) || 0;
    var tax = parseFloat(taxInput.value) || 0;

    var total = subtotal + discount + tax;

    totalSpan.textContent = total.toFixed(2);

    var totalInput = document.getElementById("cni_total_input");
    totalInput.value = total.toFixed(2);
}

function calculateSubtotal() {
    const amountInputs = document.getElementsByName("amount[]");
    let subtotal = 0;

    for (let i = 0; i < amountInputs.length; i++) {
        const amountValue = parseFloat(amountInputs[i].value) || 0;
        subtotal += amountValue;
    }

    const subtotalInput = document.getElementById("cni_sub");
    subtotalInput.value = subtotal.toFixed(2);
    calculateTotal();
}
function calculateAmount(rowNum) {
    var priceInput = document.getElementById('price_' + rowNum);
    var quantityInput = document.getElementById('quantity_' + rowNum);
    var amountInput = document.getElementById('amount_' + rowNum);

    var price = parseFloat(priceInput.value) || 0;
    var quantity = parseFloat(quantityInput.value) || 0;

    var amount = price * quantity;
    amountInput.value = amount.toFixed(2);
    calculateSubtotal();
}
