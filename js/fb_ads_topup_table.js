
//export notification
function exportData() {
    var checkboxes = document.querySelectorAll('.export:checked');
    if (checkboxes.length === 0) {
        alert('Please select data to export.');
        return false;
    }
    return true;
}

function showExportNotification() {
    alert('Export successful!');
}

function captureAndExport(tblName) {
    var selectedIds = [];
    document.querySelectorAll('input.export:checked').forEach(function(checkbox) {
        selectedIds.push(checkbox.value);
    });

    // Pass the selected IDs for auditing
    auditExport(selectedIds, tblName);

    // Trigger export action
    if (exportData()) {
        showExportNotification();
    }
}

function auditExport(ids, tblName) {
    
    // Use AJAX to send the selected IDs for auditing
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../export.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('ids=' + ids.join(',') + '&tblName=' + tblName);
}

$(document).ready(function ($) {
    $(document).on("change", ".exportAll", function (event) { //checkbox handling
        event.preventDefault();

        var isChecked = $(this).prop("checked");
        $(".export").prop("checked", isChecked);
        $(".exportAll").prop("checked", isChecked);

        updateCheckboxesOnOtherPages(isChecked);
    });

    $('a[name="exportBtn"]').on("click", function () {
        var checkboxValues = [];

        // Loop through all pages to collect checked checkboxes
        $('#fb_ads_topup_trans_table').DataTable().$('tr', { "filter": "applied" }).each(function () {
            var checkbox = $(this).find('.export:checked');
            if (checkbox.length > 0) {
                checkbox.each(function () {
                    checkboxValues.push($(this).val());
                });
            }
        });

        if (checkboxValues.length > 0) {
            console.log('Checked row IDs:', checkboxValues);
            // Send checkboxValues to the server using AJAX
            setCookie('rowID', checkboxValues.join(','), 1);

            //uncheck checkboxes
            var checkboxes = document.querySelectorAll('.export');
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });

            var selectAllCheckbox = document.querySelector('.exportAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }

            window.location.href = "fb_ads_topup_trans_table.php";
        } else {
            console.log('No checkboxes are checked.');
        }
    });

    function updateCheckboxesOnOtherPages(isChecked) {
        // Get all cells in the DataTable
        var cells = $('#fb_ads_topup_trans_table').DataTable().cells().nodes();

        // Check/uncheck all checkboxes in the DataTable
        $(cells).find('.export').prop('checked', isChecked);
    }
});


$('#resetButton').click(function() {
    $('#datepicker input, #datepicker2 input[name="start"], #datepicker2 input[name="end"], #datepicker3 input[name="start"], #datepicker3 input[name="end"], #datepicker4 input[name="start"], #datepicker4 input[name="end"]').val('');
    $('#group').val('');
    $('#timeInterval').val('');
    $('#datepicker input').change();
});




$(document).ready(function() {
    var timeParam3 = getParameterByName('timeInterval');
    var groupParam = getParameterByName('group');
    window.onload = function() {
        if(timeParam3 == null){
            document.getElementById("timeInterval").value = 'daily'; 
        }else if(timeParam3){
            document.getElementById("timeInterval").value = timeParam3; 
            document.getElementById("group").value = groupParam; 
        }else{
            document.getElementById("timeInterval").value = 'daily'; 
            document.getElementById("group").value = 'courier'; 
        }
        
        
};

$('#datepicker input, #datepicker2 input[name="end"], #datepicker3 input[name="end"], #datepicker4 input[name="end"]').change(function() {
    var time =  $('#datepicker input').val();
    var timeInterval = $('#timeInterval').val();
    var startDate = $('#datepicker2 input[name="start"]').val();
    var endDate = $('#datepicker2 input[name="end"]').val();
    var startMonth = $('#datepicker3 input[name="start"]').val();
    var endMonth = $('#datepicker3 input[name="end"]').val();
    var startYear = $('#datepicker4 input[name="start"]').val();
    var endYear = $('#datepicker4 input[name="end"]').val();
   
    var timeRange;
    if (timeInterval === 'weekly') {
    timeRange = startDate + 'to' + endDate;
    } else if (timeInterval === 'monthly') {
        timeRange = startMonth + 'to' + endMonth;
    } else if (timeInterval === 'yearly') {
        timeRange = startYear + 'to' + endYear;
    } else if (timeInterval === 'daily') {
        timeRange = time;
    }

    

    var group = $('#group').val();


    if (group === 'metaaccount' || group === 'courier' || group === 'shopee' || group === 'method' || group === 'brand' || group === 'package' || group === 'person' || group ==='merchant' || group === 'currencynperson') {
      
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else if (group === 'invoice' || group === 'currency' || group === 'agent' || group === 'outlet'){
        
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else if(group != ''){
        window.location.search = (timeRange ? '?timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
    }



    });


    function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    var groupParam = getParameterByName('group');
    if (groupParam) {
        $('#group').val(groupParam);
    }
  
    
    var timeParam4 = getParameterByName('timeRange');
    var timeInterval = $('#timeInterval').val();
    if (timeParam3 == 'weekly') {
    $('#timeInterval').val(timeParam3);
    var dateRange = timeParam4.split('to');
    $('#datepicker2 input[name="start"]').val(dateRange[0]);
    $('#datepicker2 input[name="end"]').val(dateRange[1]);
    handleTimeIntervalChange();
    $('#timeRangeParam').val(timeParam4);
    $('#timeIntervalParam').val(timeParam3);
    } else if (timeParam3 == 'monthly') {
        $('#timeInterval').val(timeParam3);
        var dateRange = timeParam4.split('to');
        $('#datepicker3 input[name="start"]').val(dateRange[0]);
        $('#datepicker3 input[name="end"]').val(dateRange[1]);
        handleTimeIntervalChange();
        $('#timeRangeParam').val(timeParam4);
        $('#timeIntervalParam').val(timeParam3); 
    } else if (timeParam3 == 'yearly') {
        $('#timeInterval').val(timeParam3);
        var dateRange = timeParam4.split('to');
        $('#datepicker4 input[name="start"]').val(dateRange[0]);
        $('#datepicker4 input[name="end"]').val(dateRange[1]);
        handleTimeIntervalChange();
        $('#timeRangeParam').val(timeParam4);
        $('#timeIntervalParam').val(timeParam3);
    } else if (timeParam3 == 'daily') {
        $('#timeInterval').val('daily');
        $('#timeInterval').val(timeParam3);
        $('#timeIntervalParam').val('daily');
        $('#timeRangeParam').val(timeParam4);
        $('#datepicker input').val(timeParam4);
        handleTimeIntervalChange();
    }
    if (timeParam3 === 'daily') {
        $('#datepicker').prop('disabled', false).show();
    } else if (timeParam3 === 'weekly') {
        $('#datepicker2').prop('disabled', false).show();
    } else if (timeParam3 === 'monthly') {
        $('#datepicker3').prop('disabled', false).show();
    } else if (timeParam3 === 'yearly') {
        $('#datepicker4').prop('disabled', false).show();
    }   

    $('#group').change(function(){
    var group = $(this).val();
   
          
          if(timeParam4){
            
              window.location.search = '?group=' + group + '&timeRange=' + timeParam4 + (timeParam3 ? '&timeInterval=' + timeParam3 : '');
          }else if(group != ''){
              
              window.location.search = '?group=' + group ;
          }
         
        
      
   

    });
   
   
    
    $('#datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        maxViewMode: 0, 
        minViewMode: 0,
        todayHighlight: true,
        toggleActive: true,
        orientation: 'bottom left',
    });


    $('#datepicker2 input[name="start"]').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        maxViewMode: 1,
        todayHighlight: true,
        toggleActive: true,
        orientation: 'bottom',
    });

    $('#datepicker2 input[name="end"]').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        maxViewMode: 1,
        todayHighlight: true,
        toggleActive: true,
        orientation: 'bottom',
    });
        
    $('#datepicker3 input[name="start"]').datepicker({
        format: "yyyy-mm",
        minViewMode: 1,
        autoclose: true,
        orientation: 'bottom',
    });

    $('#datepicker3 input[name="end"]').datepicker({
        format: "yyyy-mm",
        minViewMode: 1,
        autoclose: true,
        orientation: 'bottom',
    });

    $('#datepicker4 input[name="start"]').datepicker({
        format: "yyyy",
        minViewMode: 2,
        autoclose: true,
        orientation: 'bottom',
    });

    $('#datepicker4 input[name="end"]').datepicker({
        format: "yyyy",
        minViewMode: 2,
        autoclose: true,
        orientation: 'bottom',
    });

 
   
    function handleTimeIntervalChange() {
    var selectedOption = $('#timeInterval').val();
    $('#datepicker, #datepicker2, #datepicker3, #datepicker4').prop('disabled', true).hide();

    if (selectedOption === 'daily') {
        $('#datepicker').prop('disabled', false).show();
    } else if (selectedOption === 'weekly') {
        $('#datepicker2').prop('disabled', false).show();
    } else if (selectedOption === 'monthly') {
        $('#datepicker3').prop('disabled', false).show();
    } else if (selectedOption === 'yearly') {
        $('#datepicker4').prop('disabled', false).show();
    }
}
 
$('#timeInterval').change(handleTimeIntervalChange);
});