
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



$('#resetButton').click(function() {
    $('#datepicker input, #datepicker2 input[name="start"], #datepicker2 input[name="end"], #datepicker3 input[name="start"], #datepicker3 input[name="end"], #datepicker4 input[name="start"], #datepicker4 input[name="end"]').val('');
    $('#group').val('');
    $('#timeInterval').val('');
    $('#datepicker input').change();
});




$(document).ready(function() {
    var group = $('#group').val();
    var group2 = $('#group2').val();
    var timeParam3 = getParameterByName('timeInterval');
    var groupParam = getParameterByName('group');
    var ids = getParameterByName('ids');
    var key = getParameterByName('key');
    var time =  $('#datepicker input').val();
    var timeInterval = $('#timeInterval').val();
    var timeRange;
    var currentDate = new Date().toISOString().slice(0,10);
      
        $('#datepicker input').val(currentDate);
    window.onload = function() {
        var time =  $('#datepicker input').val();
        if(timeParam3 == null){
            document.getElementById("timeInterval").value = 'daily'; 
            timeParam3 = 'daily';
            if(document.getElementById("group").value == null){
                document.getElementById("group").value == groupParam;
            } 

            document.getElementById("timeInterval").value = timeParam3; 
            timeRange = time;
          
        }else if(timeParam3){
            document.getElementById("timeInterval").value = timeParam3; 
            if(document.getElementById("group").value == null){
                document.getElementById("group").value == groupParam;
            }
        }else{
            document.getElementById("timeInterval").value = 'daily'; 
            document.getElementById("group").value = 'courier'; 
        }
        if(document.getElementById("group2").value == null){
            document.getElementById("group2").value == '';
            group2 = ''
            
        }
        if (!window.location.search) {
            window.location.search = '?group=' + group + (time ? '&timeRange=' + time : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
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

    

   
    if (group === 'metaaccount' || group === 'courier' || group === 'shopee' || group === 'method' || group === 'brand' ||group === 'series' || group === 'package' || group === 'person' || group ==='merchant' || group === 'currencynperson') {
      
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else if (group === 'invoice' || group === 'currency' || group === 'agent' || group === 'outlet'||group === 'facebook' ||group === 'channel' ||group === 'platform'|| group === 'stock_type' || group === 'product' || group === 'whse' || group === 'pdtcategory' || group === 'platform' || group === 'stockinpic' || group === 'stockoutpic'){
        
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else if(group != ''){
        window.location.search = (timeRange ? '?timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
    }else 

    var group2 = $('#group2').val();
console.log(group);

    if (group2 === 'metaaccount' || group2 === 'courier' || group2 === 'shopee' || group2 === 'method' || group2 === 'brand' ||group2 === 'series' || group2 === 'package' || group2 === 'person' || group2 ==='merchant' || group2 === 'currencynperson') {
      
            window.location.search = '?group=' + group + (group2 ? '&group2=' + group2 : '') + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        

    } else if (group2 === 'invoice' || group2 === 'currency' || group2 === 'agent' || group2 === 'outlet'||group2 === 'facebook' ||group2 === 'channel'){
        
        window.location.search = '?group=' + group + (group2 ? '&group2=' + group2 : '') + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else if(group2 != null){
        window.location.search = '?group=' + group  + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
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
            
              window.location.search = '?group=' + group +'&timeRange=' + timeParam4 + (timeParam3 ? '&timeInterval=' + timeParam3 : '');
          }else if(group != ''){
              
              window.location.search = '?group=' + group ;
          }
        
    
         
        
      
   

    });

    $('#group2').change(function(){
        
        var group= $('#group').val();
        group = groupParam;
        console.log(group);
        var group2 = $(this).val();
        var group2value= $('#group2').val();
        group2value = group2;
        if (timeParam3 != 'daily'){
            window.location.search = '?group=' + group +(group2 ? '&group2=' + group2 : '')+ '&timeRange=' + timeParam4 + (timeParam3 ? '&timeInterval=' + timeParam3 : '') +  (ids ? '&ids=' + ids : '')+  (key ? '&key=' + key : '');  
        
        }
        else if (ids != '' && timeParam3 == 'daily'){
            window.location.search = '?group=' + group +(group2 ? '&group2=' + group2 : '')+ '&timeRange=' + timeParam4 + (timeParam3 ? '&timeInterval=' + timeParam3 : '') +  (ids ? '&ids=' + ids : '')+  (key ? '&key=' + key : '');  
        
        } else{
            window.location.search = '?group=' + group +(group2 ? '&group2=' + group2 : '')+ '&timeRange=' + timeParam4 + (timeParam3 ? '&timeInterval=' + timeParam3 : '');  
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
    if (window.location.pathname === '/fb_order_req_income_table_summary.php') {
        $('#timeInterval').prop('disabled', true).show();
        
    if (selectedOption === 'daily') {
        $('#datepicker').prop('disabled', true).show();
    } else if (selectedOption === 'weekly') {
        $('#datepicker2').prop('disabled', true).show();
    } else if (selectedOption === 'monthly') {
        $('#datepicker3').prop('disabled', true).show();
    } else if (selectedOption === 'yearly') {
        $('#datepicker4').prop('disabled', true).show();
    }
    }else{
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
    
}
 
$('#timeInterval').change(handleTimeIntervalChange);
});