<?php
$pageTitle = "Shopee Account";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = SHOPEE_ACC;

$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);

$redirect_page = $SITEURL . '/finance/shopee_acc_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

// to display data to input
if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);

    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } else {
        // If $rst is false or no data found ($act==null)
        $errorExist = 1;
        $_SESSION['tempValConfirmBox'] = true;
        $act = "F";
    }
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}


if (post('actionBtn')) {
    $action = post('actionBtn');

    $sa_name = postSpaceFilter("sa_name");
    $sa_country = postSpaceFilter("sa_country_hidden");
    $sa_currency = postSpaceFilter("sa_currency_hidden");

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addAccount':
        case 'updAccount':

            if (!$sa_name) {
                $name_err = "Please specify the account name.";
                break;
            } else if (!$sa_country) {
                $country_err = "Please specify the account country.";
                break;
            } else if (!$sa_currency) {
                $currency_err = "Please specify the account currency.";
                break;
            } else if ($action == 'addAccount') {
                try {

                    // check value

                    if ($sa_name) {
                        array_push($newvalarr, $sa_name);
                        array_push($datafield, 'name');
                    }

                    if ($sa_country) {
                        array_push($newvalarr, $sa_country);
                        array_push($datafield, 'country');
                    }

                    if ($sa_currency) {
                        array_push($newvalarr, $sa_currency);
                        array_push($datafield, 'currency');
                    }

                    $query = "INSERT INTO " . $tblName  . "(name,country,currency,create_by,create_date,create_time) VALUES ('$sa_name','$sa_country','$sa_currency','" . USER_ID . "',curdate(),curtime())";

                    // Execute the query
                    $returnData = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $finance_connect);
                    $row = $rst->fetch_assoc();

                    // check value

                    if ($row['name'] != $sa_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $sa_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['country'] != $sa_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $sa_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['currency'] != $sa_currency) {
                        array_push($oldvalarr, $row['currency']);
                        array_push($chgvalarr, $sa_currency);
                        array_push($datafield, 'currency');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $tblName  . " SET name = '$sa_name',country = '$sa_country',currency = '$sa_currency' update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
                        $returnData = mysqli_query($finance_connect, $query);
                
                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            }

            // audit log
            if (isset($query)) {

                $log = [
                    'log_act'      => $pageAction,
                    'cdate'        => $cdate,
                    'ctime'        => $ctime,
                    'uid'          => USER_ID,
                    'cby'          => USER_ID,
                    'query_rec'    => $query,
                    'query_table'  => $tblName,
                    'page'         => $pageTitle,
                    'connect'      => $connect,
                ];

                if ($pageAction == 'Add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($pageAction == 'Edit') {
                    $log['oldval']  = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                }
                audit_log($log);
            }

            break;
            case 'back':
                if ($action == 'addAccount' || $action == 'updAccount') {
                    echo $clearLocalStorage . ' ' . $redirectLink;
                } else {
                    echo $redirectLink;
                }
                break;
    }
}


if (post('act') == 'D') {
        try {
            // take name
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName, $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];
            //SET the record status to 'D'
            deleteRecord($tblName , $dataID, $sa_name, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $acc_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $acc_name . "</b> from <b><i>$tblName Table</i></b>.";
    }

    $log = [
        'log_act' => $pageAction,
        'cdate'   => $cdate,
        'ctime'   => $ctime,
        'uid'     => USER_ID,
        'cby'     => USER_ID,
        'act_msg' => $viewActMsg,
        'page'    => $pageTitle,
        'connect' => $connect,
    ];

    audit_log($log);
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">

</head>

<body>
<div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
                echo displayPageAction($act, $pageTitle);
                 ?>
            </p>

        </div>

    <div id="SAformContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="SAForm" method="post" action="" enctype="multipart/form-data">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        echo displayPageAction($act, $pageTitle);
                        ?>
                    </h2>
                </div>

                <div id="err_msg" class="mb-3">
                    <span class="mt-n2" style="font-size: 21px;"><?php if (isset($err1)) echo $err1; ?></span>
                </div>

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-md-12">
                        <label class="form-label form_lbl" id="sa_name_lbl" for="sa_name">Account Name<span class="requireRed">*</span></label>
                            <input class="form-control" type="text" name="sa_name" id="sa_name" value="<?php 
                                    if (isset($dataExisted) && isset($row['name']) && !isset($sa_name)) {
                                        echo $row['name'];
                                        } else if (isset($dataExisted) && isset($row['name']) && isset($sa_name)) {
                                            echo $sa_name;
                                            } else {
                                                echo '';
                                            } ?>" <?php if ($act == '') echo 'disabled' ?>>
        
                            <?php if (isset($name_err)) { ?>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php echo $name_err; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
    <div class="row">
        <div class="form-group autocomplete col-md-6 mb-3 mb-md-0">
            <label class="form-label form_lbl" id="sa_country_lbl" for="sa_country">Country<span class="requireRed">*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['country']))
                $echoVal = $row['country'];

            if (isset($echoVal)) {
                $country_rst = getData('name', "id = '$echoVal'", '', COUNTRIES, $connect);
                if (!$country_rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $country_row = $country_rst->fetch_assoc();
                echo $country_row['name'];
            }
            ?>

            <input class="form-control" type="text" name="sa_country" id="sa_country" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $country_row['name'] : ''  ?>">

            <input type="hidden" name="sa_country_hidden" id="sa_country_hidden" value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">

            <?php if (isset($country_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $country_err; ?></span>
                </div>
            <?php } ?>
        </div>

        
        <div class="form-group autocomplete col-md-6 mb-3 mb-md-0">
            <label class="form-label form_lbl" id="sa_currency_lbl" for="sa_currency">Currency<span class="requireRed">*</span></label>
            <?php
            unset($echoVal);

            if (isset($row['currency']))
                $echoVal = $row['currency'];

            if (isset($echoVal)) {
                $currency_rst = getData('unit', "id = '$echoVal'", '', CUR_UNIT, $connect);
                if (!$currency_rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $currency_row = $currency_rst->fetch_assoc();
            }
            ?>
            <input class="form-control" type="text" name="sa_currency" id="sa_currency" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $currency_row['unit'] : ''  ?>">
            <input type="hidden" name="sa_currency_hidden" id="sa_currency_hidden" value="<?php echo (isset($row['currency'])) ? $row['currency'] : ''; ?>">

            <?php if (isset($currency_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $currency_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

                        
                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addAccount">Add Account</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updAccount">Edit Account</button>';
                            break;
                    }
                    ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn"
                            id="actionBtn" value="back">Back</button>
                    </div>
            </form>
        </div>
    </div>
</div>

<?php
   /*
        oufei 20231014
        common.fun.js
        function(title, subtitle, page name, ajax url path, redirect path, action)
        to show action dialog after finish certain action (eg. edit)
    */
    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo $clearLocalStorage;
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>

    <script>
        <?php include "../js/shopee_acc.js" ?>

        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setAutofocus(action);
        setButtonColor();
        preloader(300, action);
    </script>
</body>
</html>