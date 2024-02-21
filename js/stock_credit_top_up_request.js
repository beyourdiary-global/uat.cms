$('#attachment').on('change', function() {
    previewImage(this, 'attach_preview')
})

//autocomplete
$(document).ready(function() {

    if (!($("#sc_mrcht").attr('readonly'))) {
        $("#sc_mrcht").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name',
                elementID: $(this).attr('id'),
                hiddenElementID: $(this).attr('id') + '_hidden',
                dbTable: '<?= MERCHANT ?>'
            }
            searchInput(param,'<?= $SITEURL ?>');
        });
        $("#sc_mrcht").change(function() {
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

    if (!($("#sc_currency").attr('disabled'))) {
        $("#sc_currency").keyup(function() {
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
