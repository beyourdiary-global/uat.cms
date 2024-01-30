<?php
$pageTitle = "Tax";
$isFinance = 1;
include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = TAX_SETT;

//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');
$act = !empty(input('act')) ? input('act') : post('act');
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';


//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/finance/tax_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

//Check a current page pin is exist or not
$pageAction = getPageAction($act);
$pageActionTitle = $pageAction . " " . $pageTitle;
$pinAccess = checkCurrentPin($connect, $pageTitle);


if ($dataID) { //edit/remove/view
    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);

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

//Delete Data
if ($act == 'D') {
    deleteRecord($tblName, $dataID, $row['name'], $finance_connect, $connect, $cdate, $ctime, $pageTitle);
    $_SESSION['delChk'] = 1;
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');

    $tax_country = postSpaceFilter('tax_country_hidden');
    $name = postSpaceFilter('name');
    $percentage = postSpaceFilter('percentage');
    $dataRemark = postSpaceFilter('currentDataRemark');

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addData':
        case 'updData':
            
            if (!$tax_country) {
                $country_err = "Please specify the country.";
                break;
            } else if (!$name) {
                $name_err = "Please specify the tax name.";
                break;
            } else if (!$percentage) {
                $percentage_err = "Please specify the percentage.";
                break;
            } else if ($action == 'addData') {
                try {

                     // check value

                    if ($tax_country) {
                        array_push($newvalarr, $tax_country);
                        array_push($datafield, 'country');
                    }

                    if ($name) {
                        array_push($newvalarr, $name);
                        array_push($datafield, 'name');
                    }

                    if ($percentage) {
                        array_push($newvalarr, $percentage);
                        array_push($datafield, 'percentage');
                    }

                    if ($dataRemark) {
                        array_push($newvalarr, $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    $query = "INSERT INTO " . $tblName . "(country,name,percentage,remark,create_by,create_date,create_time) VALUES ('$tax_country','$name',$percentage,'$dataRemark','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($finance_connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);
                    $row = $rst->fetch_assoc();

                    if ($row['country'] != $tax_country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $tax_country);
                        array_push($datafield, 'country');
                    }

                    if ($row['name'] != $name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $name);
                        array_push($datafield, 'name');
                    }


                    if ($row['percentage'] != $percentage) {
                        array_push($oldvalarr, $row['percentage']);
                        array_push($chgvalarr, $percentage);
                        array_push($datafield, 'percentage');
                    }


                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                        array_push($datafield, 'remark');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {                        
                        $query = "UPDATE " . $tblName  . " SET country = '$tax_country', name = '$name', percentage ='$percentage', remark ='$dataRemark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                if ($action == 'addData' || $action == 'upData') {
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
            deleteRecord($tblName , $dataID, $name, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

//view
if (($dataID) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $name . "</b> from <b><i>$tblName Table</i></b>.";
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
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
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

    <div id="TAXformContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="TAXForm" method="post" action="" enctype="multipart/form-data">
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
        <div class="form-group autocomplete col-md-12">
            <label class="form-label form_lbl" id="tax_country_lbl" for="tax_country">Country<span class="requireRed">*</span></label>
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

            <input class="form-control" type="text" name="tax_country" id="tax_country" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $country_row['name'] : ''  ?>">

            <input type="hidden" name="tax_country_hidden" id="tax_country_hidden" value="<?php echo (isset($row['country'])) ? $row['country'] : ''; ?>">

            <?php if (isset($country_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $country_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
    </div>

    <div class="form-group mb-3">
    <div class="row">
        <div class="form-group mb-3 col-md-6">
            <label class="form-label form_lbl" id="name_lbl" for="name">Tax Name<span class="requireRed">*</span></label>
            <input class="form-control" type="text" name="name" id="name" value="<?php 
                    if (isset($dataExisted) && isset($row['name']) && !isset($name)) {
                        echo $row['name'];
                    } else if (isset($dataExisted) && isset($row['name']) && isset($name)) {
                        echo $name;
                    } else {
                        echo '';
                    } ?>" <?php if ($act == '') echo 'disabled' ?>>

            <?php if (isset($name_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $name_err; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="form-group mb-3 col-md-6">
            <label class="form-label form_lbl" id="percentage_lbl" for="percentage">Percentage<span class="requireRed">*</span></label>
            <input class="form-control" type="number" name="percentage" id="percentage" value="<?php 
                    if (isset($dataExisted) && isset($row['percentage']) && !isset($percentage)) {
                        echo $row['percentage'];
                    } else if (isset($dataExisted) && isset($row['percentage']) && isset($percentage)) {
                        echo $percentage;
                    } else {
                        echo '';
                    } ?>" <?php if ($act == '') echo 'disabled' ?>>

            <?php if (isset($percentage_err)) { ?>
                <div id="err_msg">
                    <span class="mt-n1"><?php echo $percentage_err; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>

                    <div class="form-group mb-3">
                        <label class="form-label form_lbl" for="currentDataRemark_lbl"><?php echo $pageTitle ?> Remark</label>
                        <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addData">Add Tax</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updData">Edit Tax</button>';
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
    
    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo $clearLocalStorage;
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>

    <script>


        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ''; ?>";

        checkCurrentPage(page, action);
        centerAlignment("formContainer");
        setAutofocus(action);
        setButtonColor();
        preloader(300, action);
        <?php include "../js/tax.js" ?>
    </script>

</body>

</html>