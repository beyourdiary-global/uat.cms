var currentCourierValue = $("#dfc_courier_hidden").val();
$(document).ready(function () {

if (!($("#dfc_courier").attr('disabled'))) {
$("#dfc_courier").keyup(function () {
    var param = {
        search: $(this).val(),
        searchType: 'name', // column of the table
        elementID: $(this).attr('id'), // id of the input
        hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
        dbTable: '<?= COURIER ?>', // json filename (generated when login)
    }
    searchInput(param, '<?= $SITEURL ?>');
});
if (!($("#for_channel").attr('disabled'))) {
    $("#for_channel").keyup(function() {
        var param = {
            search: $(this).val(),
            searchType: 'name', // column of the table
            elementID: $(this).attr('id'), // id of the input
            hiddenElementID: $(this).attr('id') + '_hidden', // hidden input fcb storing the value
            dbTable: '<?= CHANEL_SC_MD ?>', // json filename (generated when login)
        }
        searchInput(param, '<?= $SITEURL ?>');
    });
}

}
});