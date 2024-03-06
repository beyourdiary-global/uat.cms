<?php
$pageTitle = "Facebook Ads Top Up Transaction";
$isFinance = 1;
include '../menuHeader.php';
include '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/finance/fb_ads_topup_trans.php';
$result = getData('*', '', '', FB_ADS_TOPUP, $finance_connect);
$result2 = getData('*', '', '', FB_ADS_TOPUP, $finance_connect);
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>
<script>
    $(document).ready(() => {
        createSortingTable('fb_ads_topup_trans_table');
    });
</script>

<body>

    <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-8">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2><?php echo $pageTitle ?></h2>
                        <div class="mt-auto mb-auto">
                            <?php if (isActionAllowed("Add", $pinAccess)) : ?>
                                <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn" href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add Transaction </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="container">
                <div class="row mb-3">
                    <div class="col-md-2 dateFilters">
                        <label for="timeInterval" class="form-label">Filter by:</label>
                        <select class="form-select" id="timeInterval" >
                        
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-4 dateFilters">
                        <label for="dateFilter" class="form-label">Filter by Payment Date:</label>
                        <div class="input-group date" id="datepicker">
                            <input type="text" class="form-control" placeholder="Select date">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                        <div class="input-daterange input-group" id="datepicker2" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start date"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End date"/>
                        </div>
                        <div class="input-group input-daterange" id="datepicker3" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start month"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End month"/>
                            
                            </div>
                        <div class="input-group input-daterange" id="datepicker4" style="display: none;">
                            <input type="text" class="input form-control" name="start" placeholder="Start year"/>
                                <span class="input-group-addon date-separator"> to </span>
                            <input type="text" class="input-sm form-control" name="end" placeholder="End year"/>
                            
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Group by:</label>
                        <select class="form-select" id="group">
                            <option value="metaaccount" selected>Meta Account</option>
                            <option value="invoice">Invoice/Payment Date</option>
                        </select>
                    </div>
                    <div class="col-md-3 meta">
                        <label class="form-label">Group by Meta Account:</label>
                        <select class="form-select" id="metagroup">
                            <option value="" selected>Select a Meta account</option>
                            <?php
                            $uniqueNames = array();

                            foreach ($result2 as $row1) {
                                $metaQuery1 = getData('*', "id='" . $row1['meta_acc'] . "'", '', META_ADS_ACC, $finance_connect);
                                $meta_acc1 = $metaQuery1->fetch_assoc();
                                $accNames = isset($meta_acc1['accName']) ? $meta_acc1['accName'] : '';

                                if (!in_array($accNames, $uniqueNames)) {
                                    echo '<option value="' . $accNames . '">' . $accNames . '</option>';
                                    $uniqueNames[] = $accNames;
                                }
                            }
                            ?>
                        </select>
                    </div>
        
                 
                </div>
             
                    
                
            </div>
         
            
            <table class="table table-striped" id="fb_ads_topup_trans_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th id="group_header" scope="col">Meta Account</th>
                        <th scope="col">Total Top-up Amount</th>
                        
                        
                    </tr>
                </thead>

                 
                <tbody>
    <?php
   
    $groupOption = isset($_GET['group']) ? $_GET['group'] : 'metaaccount'; // Default to 'metaaccount' if no group is selected
    $groupHeader = $groupOption === 'metaaccount' ? 'Meta Account' : 'Invoice/Payment Date';
    $groupedRows = [];

    while ($row = $result->fetch_assoc()) {
        $metaQuery = getData('*', "id='" . $row['meta_acc'] . "'", '', META_ADS_ACC, $finance_connect);
        $meta_acc = $metaQuery->fetch_assoc();
        $accName = isset($meta_acc['accName']) ? $meta_acc['accName'] : '';
        $paymentDate = $row['payment_date'];

        if ($groupOption === 'metaaccount') {
            $totalTopupAmount = isset($groupedRows[$accName]['totalTopupAmount']) ? $groupedRows[$accName]['totalTopupAmount'] : 0;
            $totalTopupAmount += $row['topup_amt'];

            $groupedRows[$accName] = [
                'id' => $row['id'],
                'num' => isset($groupedRows[$accName]['num']) ? $groupedRows[$accName]['num'] + 1 : 1,
                'totalTopupAmount' => number_format($row['topup_amt'], 0, '.', '') 
                
            ];
        } else{

            if (!isset($groupedRows[$paymentDate])) {
                $groupedRows[$paymentDate] = [
                    'id' => $row['id'],
                    'num' => 1,
                    'totalTopupAmount' => number_format($row['topup_amt'], 0, '.', '') 
                ];
            } else {
                $groupedRows[$paymentDate]['num']++;
                $groupedRows[$paymentDate]['totalTopupAmount'] += $row['topup_amt'];
            }
        }
    }

    foreach ($groupedRows as $key => $groupedRow) {
        echo '<tr>';
        echo '<th class="hideColumn" scope="row">' . $groupedRow['id'] . '</th>';
        echo '<th scope="row">' . $groupedRow['num'] . '</th>';
        echo '<td scope="row">' . $key . '</td>';

        echo '<td scope="row">' . $groupedRow['totalTopupAmount'] . '</td>';
        echo '</tr>';
    }
    ?>
</tbody>
   
                <tfoot>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th id="group_header2" scope="col">Meta Account</th>
                        <th scope="col">Total Top-up Amount</th>
                        
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</body>
<script>

    window.onload = function() {
        document.getElementById("timeInterval").value = 'daily';
        document.getElementById("metagroup").value = '';
};

$(document).ready(function() {
    var selectedMetaAccount = $('#metagroup').val();
    $('#group').val('metaaccount');
    $('#group').change(function(){
    var group = $(this).val();
    window.location.href = window.location.pathname + '?group=' + group;

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

 
   function filterTable() {
        var selectedOption = $('#timeInterval').val();
        var selectedMetaAccount = $('#metagroup').val();
         if (selectedMetaAccount){
            
            $('#fb_ads_topup_trans_table tbody tr').each(function() {
                var metaAccount = $(this).find('td:nth-child(3)').text()
         
                if (metaAccount.toLowerCase() === selectedMetaAccount.toLowerCase()) {
                     $(this).show();     
                } else {
                    $(this).hide();
                }
            });
          
           
         }
        if (selectedOption === 'daily' && !selectedMetaAccount) {
            var selectedDate = $('#datepicker input').val();
            $('#fb_ads_topup_trans_table tbody tr').each(function() {
                var paymentDate = $(this).find('td:nth-child(3)').text();
                if (paymentDate === selectedDate) {
                    $(this).show();
                
                    
                } else {
                    $(this).hide();
                }
            });
        } 
        
        else if (selectedOption === 'weekly') {
            var startDate = $('#datepicker2 input[name="start"]').val();
            var endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + 6); // Add 6 days to get a total of 7 days
            var endDateFormatted = endDate.toISOString().split('T')[0]; // Format the date as yyyy-mm-dd
            $('#datepicker2 input[name="end"]').val(endDateFormatted);
            var selectedMetaAccount = $('#metagroup').val();
            
            $('#fb_ads_topup_trans_table tbody tr').each(function() {
                var paymentDate = $(this).find('td:nth-child(3)').text();
                if ((startDate === '' || paymentDate >= startDate) && (endDateFormatted === '' || paymentDate <= endDateFormatted)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        else if (selectedOption === 'monthly') {
            var startDate = $('#datepicker2').datepicker('getDate');
            var endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 7);
            $('#fb_ads_topup_trans_table tbody tr').each(function() {
                var paymentDate = $(this).find('td:nth-child(3)').text();
                if ((startDate === '' || paymentDate >= startDate) && (endDate === '' || paymentDate <= endDate)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        else if (selectedOption === 'yearly') {
        var startYear = $('#datepicker4 input[name="start"]').val();
        var endYear = $('#datepicker4 input[name="end"]').val();
        $('#fb_ads_topup_trans_table tbody tr').each(function() {
            var paymentYear = $(this).find('td:nth-child(3)').text().slice(0, 4);
            if ((startYear === '' || paymentYear >= startYear) && (endYear === '' || paymentYear <= endYear)) {
                $(this).show();
            } else {
                $(this).hide();
            }
            });
        }

    }
    $('#datepicker, #datepicker2, #datepicker3, #datepicker4').on('changeDate', filterTable);
    $('#metagroup').change(filterTable);

   $('#timeInterval').change(function() {
    var selectedOption = $(this).val();
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
});



    document.getElementById('group').addEventListener('change', function() {
        let selectedValue = this.value;
        let groupHeader = document.getElementById('group_header');
        let groupHeader2 = document.getElementById('group_header2');
        if (selectedValue === 'metaaccount') {
            groupHeader.textContent = 'Meta Account';
            groupHeader2.textContent = 'Meta Account';
        } else if (selectedValue === 'invoice') {
            groupHeader.textContent = 'Invoice/Payment Date';
            groupHeader2.textContent = 'Invoice/Payment Date';
        }
    });

    
});
</script>




<script>

    
    /**
  oufei 20231014
  common.fun.js
  function(void)
  to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
*/
    dropdownMenuDispFix();

    /**
      oufei 20231014
      common.fun.js
      function(id)
      to resize table with bootstrap 5 classes
    */
    datatableAlignment('fb_ads_topup_trans_table');
</script>

</html>