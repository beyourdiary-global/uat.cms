$(document).ready(function() {
  
    function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    var groupParam = getParameterByName('group');
    if (groupParam) {
        $('#group').val(groupParam);
    }
  
    var timeParam3 = getParameterByName('timeInterval');
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
          }else{
              
              window.location.search = '?group=' + group ;
          }
         
        
      
   

    });

  
   
    $('#datepicker input, #datepicker2 input, #datepicker3 input, #datepicker4 input').change(function() {
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


    if (group === 'metaaccount') {
      
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else if (group === 'invoice'){
        
            window.location.search = '?group=' + group + (timeRange ? '&timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
        
    } else{
        window.location.search = (timeRange ? '?timeRange=' + timeRange : '') + (timeInterval ? '&timeInterval=' + timeInterval : '');
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


    $('#datepicker2').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        maxViewMode: 1,
        todayHighlight: true,
        toggleActive: true,
        orientation: 'bottom',
    });

        
    $('#datepicker3').datepicker({
        format: "yyyy-mm",
        minViewMode: 1,
        autoclose: true,
        orientation: 'bottom',
    });

    $('#datepicker4').datepicker({
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