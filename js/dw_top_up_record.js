$('#dtur_attach').on('change', function() {
    previewImage(this, 'dtur_attach_preview')
})

//autocomplete
$(document).ready(function() {
    
    if (!($("#dtur_brand").attr('disabled'))) {
        $("#dtur_brand").keyup(function () {
            var param = {
                search: $(this).val(),
                searchType: 'unit', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= BRAND ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }

    if (!($("#dtur_agent").attr('disabled'))) { 
        $("#dtur_agent").keyup(function() { 
            var param = { 
                search: $(this).val(), 
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= AGENT ?>', // json filename (generated when login)
            }
            searchInput(param, '<?= $SITEURL ?>');
        });

    }
    $("#dtur_agent").change(calculateBrand); 
})

//jQuery form validation
$("#dtur_agent").on("input", function() {
    $(".dtur-agent-err").remove();
});

$("#dtur_amount").on("input", function() {
    $(".dtur-amount-err").remove();
});

$('.submitBtn').on('click', () => {
    $(".error-message").remove();
    //event.preventDefault();
    var agent_chk = 0;
    var amount_chk = 0;


    if (($('#dtur_agent').val() === '' || $('#dtur_agent').val() === null || $('#dtur_agent')
            .val() === undefined)) {
        agent_chk = 0;
        $("#dtur_agent").after(
            '<span class="error-message dtur_agent-err">Agent is required!</span>');
    } else {
        $(".dtur-agent-err").remove();
        agent_chk = 1;
    }

    if (($('#dtur_amount').val() == '' || $('#dtur_amount').val() == '0' || $('#dtur_amount').val() === null || $('#dtur_amount')
            .val() === undefined)) {
        amount_chk = 0;
        $("#dtur_amount").after(
            '<span class="error-message dtur-amount-err">Amount is required!</span>');
    } else {
        $(".dtur_amount-err").remove();
        amount_chk = 1;
    }


    if (agent_chk == 1 && amount_chk == 1 )
        $(this).closest('form').submit();
    else
        return false;

})

function calculateBrand() {

    var paramAgent = {
        search: $("#dtur_agent_hidden").val(),
        searchCol: 'id',
        searchType: '*',
        dbTable: '<?= AGENT ?>',
        isFin: 1,
    };

    retrieveDBData(paramAgent, '<?= $SITEURL ?>', function (result) {
        getBrand(result);
        $("#dtur_brand_hidden").val(result[0]['brand']);
    });

    function getBrand(result) {
        if (result && result.length > 0) {
            brand = result[0]['brand'];
            
                var paramBrand = {
                    search: brand,
                    searchCol: 'id',
                    searchType: '*',
                    dbTable: '<?= BRAND ?>',
                    isFin: 0,
                };

                retrieveDBData(paramBrand, '<?= $SITEURL ?>', function (result) {
                    $("#dtur_brand").val(result[0]['name']);
                });
            } else {
                console.error('Error retrieving agent data');
            }
        }
    }