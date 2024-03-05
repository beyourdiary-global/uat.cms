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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
            <div class="row mb-3">
                    <div class="col">
                        <label for="timeInterval" class="form-label">Filter by:</label>
                        <select class="form-select" id="timeInterval">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    
                <div class="col">

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
                    
            </div>
            <div class="row mb-3">
            <div class="col-md-6">
                        <label>Group by:</label>
                        <select class="form-select" id="group">
                            <option value="">-</option>
                          
                        </select>
                    </div>
            </div>
            <table class="table table-striped" id="fb_ads_topup_trans_table">
                <thead>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col">Meta Account</th>
                        <th scope="col">Transaction ID</th>
                        <th scope="col">Invoice/Payment Date</th>
                        <th scope="col">Person In Charge</th>
                        <th scope="col">Top-up Amount</th>
                        <th scope="col">Attachment</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()) {
                        if (isset($row['transactionID'], $row['id']) && !empty($row['transactionID'])) {
                            $metaQuery = getData('*', "id='" . $row['meta_acc'] . "'", '', META_ADS_ACC, $finance_connect);
                            $meta_acc = $metaQuery->fetch_assoc();
                            $pic = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $usr = $pic->fetch_assoc();
                    ?>
                            <tr>
                                <th class="hideColumn" scope="row"><?= $row['id'] ?></th>
                                <th scope="row"><?= $num++; ?></th>
                                <td scope="row"><?php if (isset($meta_acc['accName'])) echo  $meta_acc['accName'] ?></td>
                                <td scope="row"><?= $row['transactionID'] ?></td>
                                <td scope="row"><?php if (isset($row['payment_date'])) echo $row['payment_date'] ?></td>
                                <td scope="row"><?php if (isset($usr['name'])) echo $usr['name'] ?></td>
                                <td scope="row"><?php if (isset($row['topup_amt'])) echo  $row['topup_amt'] ?></td>
                                <td scope="row"><?php if (isset($row['attachment'])) echo $row['attachment'] ?></td>
                                <td scope="row"><?php if (isset($row['remark'])) echo $row['remark'] ?></td>     
                                <td scope="row">
                                    <div class="dropdown" style="text-align:center">
                                        <a class="text-reset me-3 dropdown-toggle hidden-arrow" href="#" id="actionDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <button id="action_menu_btn"><i class="fas fa-ellipsis-vertical fa-lg" id="action_menu"></i></button>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="actionDropdownMenu">
                                            <li>
                                                <?php if (isActionAllowed("View", $pinAccess)) : ?>
                                                    <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] ?>">View</a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <?php if (isActionAllowed("Edit", $pinAccess)) : ?>
                                                    <a class="dropdown-item" href="<?= $redirect_page . "?id=" . $row['id'] . '&act=' . $act_2 ?>">Edit</a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <?php if (isActionAllowed("Delete", $pinAccess)) : ?>
                                                    <a class="dropdown-item" onclick="confirmationDialog('<?= $row['id'] ?>',['<?= $row['meta_acc'] ?>','<?= $row['transactionID'] ?>'],'<?= $pageTitle ?>','<?= $redirect_page ?>','<?= $SITEURL ?>/fb_ads_topup_trans_table.php','D')">Delete</a>
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="hideColumn" scope="col">ID</th>
                        <th scope="col" width="60px">S/N</th>
                        <th scope="col">Meta Account</th>
                        <th scope="col">Transaction ID</th>
                        <th scope="col">Invoice/Payment Date</th>
                        <th scope="col">Person In Charge</th>
                        <th scope="col">Top-up Amount</th>
                        <th scope="col">Attachment</th>
                        <th scope="col">Remark</th>
                        <th scope="col" id="action_col">Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</body>
<script>$(document).ready(function() {
    // Initialize the datepickers
    $('#datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        maxViewMode: 0, // Set to show only days
        minViewMode: 0, // Set to show only days
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

    // Function to filter the table based on the selected date range
    function filterTable() {
        var selectedOption = $('#timeInterval').val();
        if (selectedOption === 'daily') {
            var selectedDate = $('#datepicker input').val();
            $('#fb_ads_topup_trans_table tbody tr').each(function() {
                var paymentDate = $(this).find('td:nth-child(5)').text();
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

            $('#fb_ads_topup_trans_table tbody tr').each(function() {
                var paymentDate = $(this).find('td:nth-child(5)').text();
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
                var paymentDate = $(this).find('td:nth-child(5)').text();
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
            var paymentYear = $(this).find('td:nth-child(5)').text().slice(0, 4);
            if ((startYear === '' || paymentYear >= startYear) && (endYear === '' || paymentYear <= endYear)) {
                $(this).show();
            } else {
                $(this).hide();
            }
            });
        }

    }

    // Filter the table when the date range changes
    $('#datepicker, #datepicker2, #datepicker3, #datepicker4').on('changeDate', filterTable);

    // Filter the table when the time interval changes
    $('#timeInterval').change(function() {
        var selectedOption = $(this).val();
        if (selectedOption === 'daily') {
            $('#datepicker').prop('disabled', false).show();
            $('#datepicker2').prop('disabled', true).hide();
            $('#datepicker3').prop('disabled', true).hide();
            $('#datepicker4').prop('disabled', true).hide();
        } else if (selectedOption === 'weekly') {
            $('#datepicker').prop('disabled', true).hide();
            $('#datepicker2').prop('disabled', false).show();
            $('#datepicker3').prop('disabled', true).hide();
            $('#datepicker4').prop('disabled', true).hide();
        } else if (selectedOption === 'monthly') {
            $('#datepicker').prop('disabled', true).hide();
            $('#datepicker2').prop('disabled', true).hide();
            $('#datepicker3').prop('disabled', false).show();
            $('#datepicker4').prop('disabled', true).hide();
        } else if (selectedOption === 'yearly') {
            $('#datepicker').prop('disabled', true).hide();
            $('#datepicker2').prop('disabled', true).hide();
            $('#datepicker3').prop('disabled', true).hide();
            $('#datepicker4').prop('disabled', false).show();
        }
        filterTable();
    });

    // Initial hide based on selected option
    
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