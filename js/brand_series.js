$(document).ready(function() {
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
})