<?php
$pageTitle = "Stock Costing";

include_once 'menuHeader.php';
include_once 'checkCurrentPagePin.php';


$act = input('act');
$tblname = STK_COST;
$pageAction = getPageAction($act);

$redirect_page = $SITEURL . '/stockCosting.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';
if (!($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}
$stock_cost = getData('*', '', '', $tblname, $connect);

if (post('actionBtn')) {
    $action = post('actionBtn');

    $sc_brand = postSpaceFilter('sc_brand');
    $sc_product = postSpaceFilter('sc_product');
    $sc_shipping_cost = postSpaceFilter('sc_shipping_cost');
    $sc_quantity = postSpaceFilter('sc_quantity');
    $sc_balance_quantity = postSpaceFilter('sc_balance_quantity');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRecord':

            if (!$sc_brand) {
                $brand_err = "Brand cannot be empty.";
                break;            
            } else if (!$sc_product) {
                $product_err = "Name cannot be empty.";
                break;
            } else if (!$sc_shipping_cost) {
                $shipping_cost_err = "Contact cannot be empty.";
                break;
            } else if (!$sc_quantity) {
                $quantity_err = "Customer Email cannot be empty.";
                break;
            }
    }

}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
   
</head>

<body>
    <div class="container d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6">
            <form id="stockForm" method="post" action="">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group my-5">
                            <h3>
                                Stock Costing
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="brand">Brand</label>
                            <input class="form-control" type="text" name="brand" id="brand" value="" >
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="prod">Product</label>
                            <input class="form-control" type="text" name="prod" id="prod" value="" >
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="shipping_cost">Shipping Cost</label>
                            <input class="form-control" type="text" name="shipping_cost" id="shipping_cost" value="" >
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="quantity">Quantity</label>
                            <input class="form-control" type="number" name="quantity" id="quantity" value="" >
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" for="balance_quantity">Balance Quantity</label>
                            <input class="form-control" type="number" name="balance_quantity" id="balance_quantity">
                        </div>
                    </div>
                </div>
                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    
                   <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addRecord">Add Stock Costing</button>
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>

<?php
 if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo $clearLocalStorage;
    echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","I");</script>';
}
?>
<script>
    
        
        // Notification
      
    </script>