$('#dtur_attach').on('change', function() {
    previewImage(this, 'dtur_attach_preview')
})

document.getElementById("dtur_agent").addEventListener("change", function () {
    var agentId = document.getElementById("dtur_agent_hidden").value;

    fetch('/finance/agent_table.php?agent_id=' + agentId)
        .then(response => response.json())
        .then(data => {
            document.getElementById("dtur_brand").value = data.brand;
        })
        .catch(error => {
            console.error('Error fetching brand data:', error);
        });
});

//autocomplete
$(document).ready(function() {
    

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