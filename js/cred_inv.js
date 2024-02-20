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

$(document).ready(function() {

    if (!($("#cni_name").attr('disabled'))) {
        $("#cni_name").keyup(function() {
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
        $("#cni_pic").keyup(function() {
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
        $("#cni_curr").keyup(function() {
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
    $("#cni_name").change(autofillMrcht);

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


function Add() {
    AddRow($("#prod_name").val(), $("#prod_price").val(), $("#qty").val(), $("#amount").val());
};
function AddRow(description, price, quantity, amount) {
    // Get the reference of the Table's TBODY element.
    var tBody = $("#productList > TBODY")[0];
    
    // Calculate the numbering based on the existing rows.
    var numbering = $("#productList > TBODY > TR").length + 1;

    // Add Row.
    var row = tBody.insertRow(-1);

    // Add cells.
    var cell = $(row.insertCell(-1));
    cell.html(numbering);

    cell = $(row.insertCell(-1));
    cell.html('<input type="text" name="prod_name[]" value="' + description + '">');
    
    cell = $(row.insertCell(-1));
    cell.html('<input type="text" name="price[]" value="' + price + '">');

    cell = $(row.insertCell(-1));
    cell.html('<input type="text" name="quantity[]" value="' + quantity + '">');

    cell = $(row.insertCell(-1));
    cell.html('<input type="text" name="amount[]" value="' + amount + '">');
   
    // Add Button cell for removal.
    cell = $(row.insertCell(-1));
    var btnRemove = $('<button class="mt-1" id="action_menu_btn"><i class="fa-regular fa-trash-can fa-xl" style="color:#ff0000"></i></button>');
    btnRemove.attr("type", "button");
    btnRemove.click(function() {
        Remove(this);
    });
    cell.append(btnRemove);
}

function Remove(button) {
    // Determine the reference of the Row using the Button.
    var row = $(button).closest("tr");
    var name = $("td", row).eq(1).find("input[type='text']").val(); // Assuming description is in the second cell
    if (confirm("Do you want to delete: " + name)) {
        row.remove();
    }
}

// // product autofill
// function prodInfo(element) {
//     var id = $(element).attr('id').split('_');
//     id = id[(id.length) - 1];

//     if (!($(element).attr('readonly'))) {
//         var param = {
//             search: $(element).val(),
//             searchType: 'name',
//             page: 'package',
//             elementID: $(element).attr('id'),
//             hiddenElementID: 'prod_val_' + id,
//             dbTable: '<?= PROD ?>'
//         }
//         searchInput(param, '<?= $SITEURL ?>');

//         if ($(element).val() == '') {
//             $('#prod_val_' + id).val('');
//             $('#wgt_' + id).val('');
//             $('#wgt_unit_' + id).val('');
//             $('#wgt_unit_val_' + id).val('');
//             $('#barcode_status_' + id).val('');
//             $('#barcode_slot_' + id).val('');
//         }
//     }
// }

// function prodInfoAutoFill(element) {
//     var id = $(element).attr('id').split('_');
//     id = id[(id.length) - 1];
//     var prodArr = [];
//     var wgtArr = [];
//     var rowCount = parseInt($("#productList TBODY TR:last TD").eq(0).html());

//     var retrieveProdInfo = async () => {
//         prodArr = await retrieveJSONData($(element).attr('value'), 'id', '<?= PROD ?>');
//     }

//     var setProdInfo = async () => {
//         $('#wgt_' + id).val(prodArr[0]['weight']);
//         $('#wgt_unit_val_' + id).val(prodArr[0]['weight_unit']);
//         $('#barcode_status_' + id).val(prodArr[0]['barcode_status']);
//         $('#barcode_slot_' + id).val(prodArr[0]['barcode_slot']);
//     }

//     var retrieveWgtUnit = async () => {
//         wgtArr = await retrieveJSONData($('#wgt_unit_val_' + id).attr('value'), 'id', '<?= WGT_UNIT ?>');
//     }

//     var setWgtUnit = async () => {
//         $('#wgt_unit_' + id).val(wgtArr[0]['unit']);
//     }

//     var allFunc = async () => {
//         await retrieveProdInfo();
//         await setProdInfo();
//         await retrieveWgtUnit();
//         await setWgtUnit();
//         await setBarcodeSlotTotal(rowCount);
//     }

//     allFunc();
// }

