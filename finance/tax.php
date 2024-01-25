<?php
$pageTitle = "Tax";
$isFinance = 1;
include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

$tblName = TAX_SETT;

//Current Page Action And Data ID
$dataID = input('id');
$act = input('act');
$pageAction = getPageAction($act);


//Page Redirect Link , Clean LocalStorage , Error Alert Msg 
$redirect_page = $SITEURL . '/tax_setting_table.php';
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

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');
    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addData':
        case 'updData':

            $country = postSpaceFilter('country');
            $currentDataName = postSpaceFilter('currentDataName');
            $percentage = postSpaceFilter('percentage');
            $dataRemark = postSpaceFilter('currentDataRemark');

            if (isDuplicateRecord("name", $currentDataName, $tblName, $connect, $dataID)) {
                $err = "Duplicate record found for " . $pageTitle . " name.";
                break;
            }

            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;

                    if ($country) {
                        array_push($newvalarr, $country);
                        array_push($datafield, 'country');
                    }

                    if ($currentDataName) {
                        array_push($newvalarr, $currentDataName);
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

                    $query = "INSERT INTO " . $tblName . "(country,name,percentage,remark,create_by,create_date,create_time) VALUES ('$country','$currentDataName',$percentage,'$dataRemark','" . USER_ID . "',curdate(),curtime())";
                    $returnData = mysqli_query($connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    $rst = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName , $finance_connect);
                    $row = $rst->fetch_assoc();

                    if ($row['country'] != $country) {
                        array_push($oldvalarr, $row['country']);
                        array_push($chgvalarr, $country);
                        array_push($datafield, 'country');
                    }

                    if ($row['name'] != $currentDataName) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $currentDataName);
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
                        $query = "UPDATE " . $tblName  . " SET country = '$country', name = '$currentDataName', percentage ='$percentage', remark ='$remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$dataID'";
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
                echo $clearLocalStorage . ' ' . $redirectLink;
                break;
    }
}

//Function(title, subtitle, page name, ajax url path, redirect path, action)
//To show action dialog after finish certain action (eg. edit)

if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo $clearLocalStorage;
    echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
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
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php echo $pageActionTitle ?>
            </p>
        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-8 col-md-6 formWidthAdjust">
                <form id="form" method="post" novalidate>
                    <div class="form-group mb-5">
                        <h2>
                            <?php echo $pageActionTitle ?>
                        </h2>
                    </div>

                    <div class="form-group autocomplete mb-3">
                        <label class="form-label form" id="countryLabel" for="country">Country</label>
                        <?php
                        $selectedCountry = isset($row['country']) ? $row['country'] : '';
    
                        if (!empty($selectedCountry)) {

                            $country_rst = getData('*', "id = '$selectedCountry'", '', TAX_SETT, $connect);
        
                            if (!$country_rst) {
                                echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                echo "<script>location.href ='$SITEURL/dashboard.php';</script>";}
                                $country_row = $country_rst->fetch_assoc();
                            }
                            ?>
                            <input class="form-control" type="text" name="country" id="country" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($selectedCountry) ? $country_row['country'] : ''; ?>" required>
                            <input type="hidden" name="country_hidden" id="country_hidden" value="<?php echo $selectedCountry; ?>">
                        </div>


                        <div class="form-group mb-3">
                            <div class="row">
                                <div class="col-sm">
                                    <label class="form-label" for="currentDataName"><?php echo $pageTitle ?> Name</label>
                                    <input class="form-control" type="text" name="currentDataName" id="currentDataName" value="<?php if (isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off">
                                    <div id="err_msg">
                                        <span class="mt-n1" id="errorSpan"><?php if (isset($err)) echo $err; ?></span>
                                    </div>
                                </div>

                                <div class="col-sm">
                                    <label class="form-label" for="taxPercentage">Tax Percentage (%)</label>
                                    <input type="number" name="taxPercentage" id="taxPercentage" step="any" required <?php if ($act == '') echo 'readonly ' ?> value="<?php if (isset($row['tax_percentage'])) echo $row['tax_percentage'] ?>" class="form-control" style="height: 40px;">
                                </div>
                            </div>
                        </div>


                    <div class="form-group mb-3">
                        <label class="form-label" for="currentDataRemark"><?php echo $pageTitle ?> Remark</label>
                        <textarea class="form-control" name="currentDataRemark" id="currentDataRemark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    </div>

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                        switch ($act) {
                            case 'I':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addTransaction">Add Transaction</button>';
                                break;
                            case 'E':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updTransaction">Edit Transaction</button>';
                                break;
                        }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn" id="actionBtn" value="back">Back</button>
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
        setButtonColor();
        preloader(300, action);
    </script>

</body>

</html>