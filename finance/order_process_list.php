<?php
$pageTitle = "Order Process List";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page2 = $SITEURL . '/update_shipment_info.php';
$deleteRedirectPage = $SITEURL . '/finance/order_process_list.php';
$result = getData('*', '', '', SHOPEE_SG_ORDER_REQ,$finance_connect
);
$result2 = getData('*', '', '', FB_ORDER_REQ,$finance_connect
);
$result3 = getData('*', '', '', WEB_ORDER_REQ ,$finance_connect);
$result4 = getData('*', '', '', LAZADA_ORDER_REQ ,$finance_connect);
 
if (isset($_POST['id'], $_POST['order_status'], $_POST['table_name'])) {
    // Assuming you have a database connection established
    $id = $_POST['id'];
    $newStatus = $_POST['order_status'];
    $tableName = $_POST['table_name'];

$query = "UPDATE $tableName SET order_status = '$newStatus' WHERE id = $id";
$acc_result = $finance_connect->query($query);
if ($tableName == 'LAZADA_ORDER_REQ') {
    $acc_result = $connect->query($query);
} else {
    $acc_result = $finance_connect->query($query);
}
if ($acc_result) {
    $response = array('success' => true);
} else {
    $response = array('success' => false, 'error' => $connect->error);
}
} else {
    $response = array('success' => false, 'error' => 'Missing id, order_status, or table_name in POST request');
}

?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('order_process_list');
    });
</script>

<style>
    .btn {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        margin: 3px;
    }
    .btn-container {
        white-space: nowrap;
    }
</style>

<body>

    <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-12 col-md-11">

            <div class="d-flex flex-column mb-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                            class="fa-solid fa-chevron-right fa-xs"></i>
                        <?php echo $pageTitle ?>
                    </p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>
                            <?php echo $pageTitle ?>
                        </h2>
                    </div>
                </div>
            </div>
            <?php
            if (!$result) {
                echo '<div class="text-center"><h4>No Result!</h4></div>';
            } else {
                ?>

                <table class="table table-striped" id="shopee_order_req_table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Channel</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Receiver Name</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                  $resultSets = array();

                  if ($result) {
                      $resultSets['result'] = $result;
                  }
                  
                  if ($result2) {
                      $resultSets['result2'] = $result2;
                  }
                  
                  if ($result3) {
                      $resultSets['result3'] = $result3;
                  }
                  
                  if ($result4) {
                      $resultSets['result4'] = $result4;
                  }
                

                     // Process data from all result sets using four while loops grouped together
                     foreach ($resultSets as $resultSetKey => $resultSet) {
                        while ($row = $resultSet->fetch_assoc()) {
                            if($row['order_status'] != 'SP'){
                            $channel = '';
                            $pic = '';
                            switch ($resultSetKey) {
                                case 'result':
                                    $channel = 'Shopee'; 
                                    break;
                                case 'result2':
                                    $channel = 'Facebook'; 
                                    break;
                                case 'result3':
                                    $channel = 'Web'; 
                                    break;
                                case 'result4':
                                    $channel = 'Lazada'; 
                                    break;
                                default:
                                $channel = ''; 
                                    break;
                            }
                            $channel_rst = getData('*', "name = '$channel'", '', CHANEL_SC_MD, $finance_connect);
                            $channel_row = $channel_rst->fetch_assoc();
                            $channelid = $channel_row['id'];
                            $cust = null;

                            if (isset($row['order_id'])) {
                                $orderId = $row['order_id'];
                            } elseif (isset($row['orderID'])) {
                                $orderId = $row['orderID'];
                            } elseif (isset($row['oder_number'])) {
                                $orderId = $row['oder_number'];
                            } elseif (isset($row['id'])) {
                                $orderId = $row['id'];
                            }
                            if (isset($row['package']) || isset($row['pkg'])) {
                                $packageId = isset($row['package']) ? $row['package'] : $row['pkg'];
                                $q2 = getData('name', "id='" . $packageId . "'", '', PKG, $connect);
                                $pkg = $q2->fetch_assoc();
                            }

                            $q3 = getData('name', "id='" . $row['brand'] ."'", '', BRAND, $connect);
                            $brand = $q3->fetch_assoc();

                            if (isset($row['name']) || isset($row['cust_name'] )) {
                                $cust = isset($row['name']) ? $row['name'] : $row['cust_name'];
                            }else if (isset($row['buyer'])){
                                $q4 = getData('buyer_username', "id='" . $row['buyer'] . "'", '', SHOPEE_CUST_INFO, $finance_connect);
                                $custRow = $q4->fetch_assoc();
                                if ($custRow) {
                                    $cust = $custRow['buyer_username'];
                                }
                            }

                            if (isset($row['ship_rec_name']) || isset($row['shipping_name'] )) {
                                $pic = isset($row['ship_rec_name']) ? $row['ship_rec_name'] : $row['shipping_name'];
                       
                            } 
                            if (isset($row['pic']) ){
                            $q5 = getData('name', "id='" . $row['pic'] . "'", '', USR_USER, $connect);
                            $picid = $q5->fetch_assoc();
                            if ($picid) {
                                $pic = $picid['name'];
                            }
                            }
                            if (isset($row['shopee_acc']) ){
                                $q1 = getData('*', "id='" . $row['shopee_acc'] . "'", '', SHOPEE_ACC, $finance_connect);
                                $picid = $q1->fetch_assoc();
                                if ($picid) {
                                    $pic = $picid['name'];
                                }
                            }
                           
                            ?>

                            <tr>
                                
                                <th class="hideColumn" scope="row">
                                    <?= $row['id'] ?>
                                </th>
                                <th scope="row">
                                    <?= $num++; ?>
                                </th>

                                <td scope="row" class="btn-container">
                                <?php
                                $tableKey = '';
                                switch ($resultSetKey) {
                                   
                                    case 'result':
                                        $tableKey = SHOPEE_SG_ORDER_REQ;
                                        $redirect_page = $SITEURL . '/finance/shopee_order_req.php';
                                       
                                        break;
                                    case 'result2':
                                        $tableKey = FB_ORDER_REQ;
                                        $redirect_page = $SITEURL . '/finance/fb_order_req.php';
                                        break;
                                    case 'result3':
                                        $tableKey = WEB_ORDER_REQ;
                                        $redirect_page = $SITEURL . '/finance/website_order_request.php';
                                        break;
                                    case 'result4':
                                        $tableKey = LAZADA_ORDER_REQ;
                                        $redirect_page = $SITEURL . '/lazada_order_req.php';
                                        break;
                                    default:
                                        $tableKey = '';
                                        break;
                                }
                              
                              
                                echo'<a class="btn btn-primary me-1" href="' . $redirect_page . '?id=' . $row['id'] . '" title="View order"><i class="fas fa-eye" title="View order"></i></a>';
                               
                                echo'<a class="btn btn-warning me-1" href="' . $redirect_page2 . '?id=' . $row['id'] . '&act=' . $act_1 .'&channel=' . $channelid .'&orderid=' . $orderId .'" title="Update shipment"><i class="fas fa-edit" title="Update shipment"></i></a>';

                                echo '<a class="btn btn-danger me-1" href="javascript:void(0)" onclick="updateOrderStatus('.$row['id'].', \'WP\', \''.$tableKey.'\')" title="Process shipment"><i class="fa fa-cog" title="Process shipment"></i></a>';?>
                                </td>
                                <td scope="row">
                                    <?= $orderId ?>
                                </td>
                                <td scope="row">
                                    <?= $channel ?>
                                </td>
                                <td scope="row">
                                    <?= $cust ?>
                                </td>
                                <td scope="row">
                                    <?= $pic ?>
                                </td>    
                                <td scope="row">
                                    <?= $brand['name'] ?? '' ?>
                                </td>
                                <td scope="row">
                                    <?= $pkg['name'] ?? '' ?>
                                </td>
                            </tr>
                        <?php }}} ?>
                    </tbody>
                    <tfoot>
                    <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col" width="60px">S/N</th>
                            <th scope="col" id="action_col" width="100px">Action</th>
                            <th scope="col">Order ID</th>
                            <th scope="col">Channel</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Receiver Name</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Package</th>
                        </tr>
                    </tfoot>
                </table>
            <?php }?>
        </div>

    </div>

</body>
<script>
function updateOrderStatus(id, newStatus, tableName) {
    $.ajax({
        url: 'order_process_list.php',
        type: 'POST',
        data: { id: id, order_status: newStatus, table_name: tableName },

        success: function(data) {
                console.log('TEST7');
                window.location.href = 'order_process_list.php';
            },
        error: function() {
            // Handle the AJAX error
        }
    });
}
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
    datatableAlignment('shopee_order_req_table');
</script>

</html>