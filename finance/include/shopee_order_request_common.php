<?php
$isProcess = 1;
include_once '../../menuHeader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $orderId = intval($_POST['id']);

    // Update the order status
    $updateSql = "UPDATE " . SHOPEE_SG_ORDER_REQ . " SET order_status = 'C' WHERE id = $orderId";
    $updateResult = mysqli_query($finance_connect, $updateSql);

    if ($updateResult) {
        // Verify the update
        $checkSql = "SELECT order_status FROM " . SHOPEE_SG_ORDER_REQ . " WHERE id = $orderId AND order_status = 'C'";
        $checkResult = mysqli_query($finance_connect, $checkSql);

        if ($row = mysqli_fetch_assoc($checkResult)) {
            if ($row['order_status'] === 'C') {
                echo "success";
            } else {
                echo "status_mismatch";
            }
        } else {
            echo "status_mismatch";
        }
    } else {
        echo "error";
    }
}
?>
