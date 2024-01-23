async function setBarcodeSlotTotal(rowCount) {
    var num = 1;
    // init
    var totalSlot_id = $('#barcode_slot_total');
    totalSlot_id.text(0);

    while (num <= rowCount) {
        totalSlot = parseInt(totalSlot_id.text());
        var barcodeSlot_id = $('#barcode_slot_' + num);

        if (barcodeSlot_id !== 0) {
            var barcodeSlot = parseInt(barcodeSlot_id.val());

            if (!isNaN(barcodeSlot))
                totalSlot += barcodeSlot;

            totalSlot_id.text(totalSlot);
            totalSlot_id.append('<input name="barcode_slot_total_hidden" id="barcode_slot_total_hidden" type="hidden" value="' + totalSlot + '">');

            num++;
        }
    }
}

function Add() {
    AddRow($("#prod_name").val(), $("#prod_val").val(), $("#wgt").val(), $("#wgt_unit").val(), $("#wgt_unit_val").val(), $("#barcode_status").val(), $("#barcode_slot").val());
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
    cell.html('<input type="text" name="prod_name[]" id="prod_name_' + numbering + '" value="" onkeyup="prodInfo(this)"><input type="hidden" name="prod_val[]" id="prod_val_' + numbering + '" value="" oninput="prodInfoAutoFill(this)">');
    cell.addClass('autocomplete');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="wgt[]" id="wgt_' + numbering + '" value="" readonly>');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="wgt_unit[]" id="wgt_unit_' + numbering + '" value="" readonly><input type="hidden" name="wgt_unit_val[]" id="wgt_unit_val_' + numbering + '" value="" readonly>');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="barcode_status[]" id="barcode_status_' + numbering + '" value="" readonly>');
    cell = $(row.insertCell(-1));
    cell.html('<input class="readonlyInput" type="text" name="barcode_slot[]" id="barcode_slot_' + numbering + '" value="" readonly>');

    //Add Button cell.
    cell = $(row.insertCell(-1));
    var btnRemove = $('<button class="mt-1" id="action_menu_btn"><i class="fa-regular fa-trash-can fa-xl" style="color:#ff0000"></i></button>');
    btnRemove.attr("type", "button");
    btnRemove.attr("onclick", "Remove(this);");
    btnRemove.val("Remove");
    cell.append(btnRemove);
};

function Remove(button) {
    //Determine the reference of the Row using the Button.
    var row = $(button).closest("TR");
    var name = $("TD", row).eq(0).html();
    var rowCount = parseInt($("#productList TBODY TR:last TD").eq(0).html());

    if (confirm("Do you want to delete: " + name)) {

        //Get the reference of the Table.
        var table = $("#productList")[0];

        //Delete the Table row using it's Index.
        table.deleteRow(row[0].rowIndex);

        //Recalc barcode slot
        setBarcodeSlotTotal(rowCount);
    }
};

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
$("#package_cost").on("input", function() {
    $(".package-cost-err").remove();
});

$(document).ready(function() {
    if (!($("#cur_unit").attr('readonly'))) {
        $("#cur_unit").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'unit',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= CUR_UNIT ?>'
            }
            searchInput(param,'<?= $SITEURL ?>');
        });
        $("#cur_unit").change(function() {
            if ($(this).val() == '')
                $('#' + $(this).attr('id') + '_hidden').val('');
        });
    }
    if (!($("#brand").attr('readonly'))) {
        $("#brand").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= BRAND ?>'
            }
            searchInput(param,'<?= $SITEURL ?>');
        });
        $("#brand").change(function() {
            if ($(this).val() == '')
                $('#' + $(this).attr('id') + '_hidden').val('');
        });
    }
    if (!($("#cost_curr").attr('readonly'))) {
        $("#cost_curr").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'unit',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= CUR_UNIT ?>'
            }
            searchInput(param,'<?= $SITEURL ?>');
        });
        $("#cost_curr").change(function() {
            if ($(this).val() == '')
                $('#' + $(this).attr('id') + '_hidden').val('');
        });
    }
})

//block "e" in input type number field
document.querySelector("#package_cost").addEventListener("keypress", function (evt) {
    var inputValue = this.value;

    if (evt.which != 8 && evt.which != 0 && (evt.which < 48 || evt.which > 57) && evt.which != 46) {
        evt.preventDefault();
    }

    // Allow only one decimal point
    if (inputValue.indexOf('.') !== -1 && evt.which == 46) {
        evt.preventDefault();
    }
});