<?php
$pageTitle = "Shopee Withdrawal Transactions";
$isFinance = 1;

include_once '../menuHeader.php';
include_once '../checkCurrentPagePin.php';

define('WITHDRAWAL_TRANSACTIONS', 'withdrawal_transactions_table');

$row_id = input('id');
$act = input('act');
$pageAction = getPageAction($act);
$actionBtnValue = ($act === 'I') ? 'addData' : 'updData';
$redirect_page = $SITEURL . '/finance/withdrawal_transactions_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

// to display data to input
if ($row_id) { //edit/remove/view
    $rst = getData('*', "id = '$row_id'", 'LIMIT 1', $tblName , $finance_connect);

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
if (!($row_id) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
}

//Edit And Add Data
if (post('actionBtn')) {

    $action = post('actionBtn');

    switch ($action) {
        case 'addData':
        case 'updData':

            $withdrawalDate = postSpaceFilter('withdrawal_date');
            $withdrawalID = postSpaceFilter('withdrawal_id');
            $withdrawalAmount = postSpaceFilter('withdrawal_amount');
            $withdrawalPersonInCharges = postSpaceFilter('withdrawal_person_in_charges');
            $attachment = postSpaceFilter('attachment');
            $remark = postSpaceFilter('remark');

            $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();
           $query = "INSERT INTO withdrawal_table (withdrawal_date, withdrawal_id, withdrawal_amount, withdrawal_person_in_charges, attachment, remark, create_by, create_date, create_time) VALUES ('$withdrawal_date', '$withdrawal_id', '$withdrawal_amount', '$withdrawal_person_in_charges', '$attachment', '$remark', '" . USER_ID . "', curdate(), curtime())";
            $returnData = mysqli_query($finance_connect, $query);
            $dataID = $connect->insert_id;
            if ($action == 'addData') {
                try {
                    $_SESSION['tempValConfirmBox'] = true;
            
                    if ($withdrawal_date) {
                        array_push($newvalarr, $withdrawal_date);
                        array_push($datafield, 'withdrawal_date');
                    }
            
                    if ($withdrawal_id) {
                        array_push($newvalarr, $withdrawal_id);
                        array_push($datafield, 'withdrawal_id');
                    }
            
                    if ($withdrawal_amount) {
                        array_push($newvalarr, $withdrawal_amount);
                        array_push($datafield, 'withdrawal_amount');
                    }
            
                    if ($withdrawal_person_in_charges) {
                        array_push($newvalarr, $withdrawal_person_in_charges);
                        array_push($datafield, 'withdrawal_person_in_charges');
                    }
            
                    if ($attachment) {
                        array_push($newvalarr, $attachment);
                        array_push($datafield, 'attachment');
                    }
            
                    if ($remark) {
                        array_push($newvalarr, $remark);
                        array_push($datafield, 'remark');
                    }
            
                    $query = "INSERT INTO withdrawal_table (withdrawal_date, withdrawal_id, withdrawal_amount, withdrawal_person_in_charges, attachment, remark, create_by, create_date, create_time) VALUES ('$withdrawal_date', '$withdrawal_id', '$withdrawal_amount', '$withdrawal_person_in_charges', '$attachment', '$remark', '" . USER_ID . "', curdate(), curtime())";
                    $returnData = mysqli_query($finance_connect, $query);
                    $dataID = $connect->insert_id;
                    
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    if ($row['withdrawal_date'] != $withdrawalDate) {
                        array_push($oldvalarr, $row['withdrawal_date']);
                        array_push($chgvalarr, $withdrawal_date);
                        array_push($datafield, 'withdrawal_date');
                    }

                    if ($row['withdrawal_id'] != $withdrawal_id) {
                        array_push($oldvalarr, $row['withdrawal_id']);
                        array_push($chgvalarr, $withdrawal_id);
                        array_push($datafield, 'withdrawal_id');
                    }

                    if ($row['withdrawal_amount'] != $withdrawal_amount) {
                        array_push($oldvalarr, $row['withdrawal_amount']);
                        array_push($chgvalarr, $withdrawal_amount);
                    }

                    if ($row[' withdrawal_person_in_charges'] != $withdrawal_person_in_charges) {
                        array_push($oldvalarr, $row[' withdrawal_person_in_charges']);
                        array_push($chgvalarr, $withdrawal_person_in_charges);
                    }

                    if ($row['attachment'] != $attachment) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $attachment);
                        array_push($datafield, 'attachment');
                    }

                    if ($row['remark'] != $dataRemark) {
                        array_push($oldvalarr, $row['remark'] == '' ? 'Empty Value' : $row['remark']);
                        array_push($chgvalarr, $dataRemark == '' ? 'Empty Value' : $dataRemark);
                        array_push($datafield, 'remark');
                    }
            
                    $_SESSION['tempValConfirmBox'] = true;
            
                    if ($oldvalarr && $chgvalarr) {
                        $query = "UPDATE withdrawal_table SET withdrawal_date = '$withdrawal_date', withdrawal_id = '$withdrawal_id', withdrawal_amount = '$withdrawal_amount', withdrawal_person_in_charges = '$withdrawal_person_in_charges', attachment = '$attachment', remark = '$remark', update_date = curdate(), update_time = curtime(), update_by = '" . USER_ID . "' WHERE id = '$dataID'";
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

            header("Location: withdrawal_transactions_table.php?id=$dataID");
            exit();
            break;

        case 'back':
            echo $clearLocalStorage . ' ' . $redirectLink;
            break;
    }
}

//if (post('act') == 'D') {
    $id = post('id');
    if ($id) {
        try {
            // take name
            $rst = getData('*', "id = '$id'", 'LIMIT 1', $tblName , $finance_connect);
            $row = $rst->fetch_assoc();

            $dataID = $row['id'];
            //SET the record status to 'D'
            deleteRecord($tblName , $dataID, $maa_id, $finance_connect, $connect, $cdate, $ctime, $pageTitle);
            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
//}

//view
if (($row_id) && !($act) && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $acc_id = isset($dataExisted) ? $row['accID'] : '';
    $_SESSION['viewChk'] = 1;

    if (isset($errorExist)) {
        $viewActMsg = USER_NAME . " fail to viewed the data [<b> ID = " . $dataID . "</b> ] from <b><i>$tblName Table</i></b>.";
    } else {
        $viewActMsg = USER_NAME . " viewed the data [<b> ID = " . $dataID . "</b> ] <b>" . $acc_id . "</b> from <b><i>$tblName Table</i></b>.";
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("addBtn").addEventListener("click", function() {
                // 跳转到 withdrawal_transactions_table.php
                window.location.href = 'withdrawal_transactions_table.php';
            });
        });
    </script>
</head>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
    <div class="d-flex flex-column my-3 ms-3">
    <p>
        <a href="<?= $redirect_page ?>">Shopee Withdrawal Transactions</a>
        <i class="fa-solid fa-chevron-right fa-xs"></i>
        <?php
        // Assuming $pageActionTitle should be set based on the value of $act
        if ($act == 'addData') {
            $pageActionTitle = 'Add Transaction';
        } elseif ($act == 'updData') {
            $pageActionTitle = 'Update Transaction';
        } else {
            // Set a default title or handle other cases
            $pageActionTitle = 'Add Transaction';
        }
        ?>
        <?php echo $pageActionTitle ?>
    </p>
</div>

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

                    <div id="err_msg" class="mb-3">
                        <span class="mt-n2" style="font-size : 21px"><?php if (isset($err)) echo $err; ?></span>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label" id="withdrawal_date" for="withdrawal_date">Withdrawal Date</label>
                                <input class="form-control" type="date" name="withdrawal_date" id="withdrawal_date" value="<?php echo (isset($row['withdrawal_date'])) ? $row['withdrawal_date'] : ''; ?>" <?php if ($act == '') echo 'readonly' ?> required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group autocomplete mb-3">
                                <label class="form-label" id="withdrawal_id" for="withdrawal_id">Withdrawal ID</label>
                                <input class="form-control" type="text" name="withdrawal_id" id="withdrawal_id" <?php if ($act == '') echo 'readonly' ?> value="<?php echo !empty($echoVal) ? $withdrawal_id_row['ID'] : ''  ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm">
                                <label class="form-label" for="withdrawal_amount">Withdrawal Amount (SGD)</label>
                                <input class="form-control" type="text" name="withdrawal_amount" id="withdrawal_amount" value="<?php if (isset($row['withdrawal_amount'])) echo $row['withdrawal_amount'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="off" oninput="validateNumericInput(this, 'withdrawal_amountErrorMsg')">

                                <div id="withdrawal_amountErrorMsg" class="error-message">
                                    <span class="mt-n1"></span>
                                </div>
                            </div>
                            
                            <div class="col-sm">
                                <label class="form-label" for="withdrawal_person_in_charges">Withdrawal Person In Charges</label>
                                <input class="form-control" type="text" name="withdrawal_person_in_charges" id="withdrawal_person_in_charges" value="<?php if (isset($row['withdrawal_person_in_charges'])) echo $row['withdrawal_person_in_charges'] ?>" <?php if ($act == '') echo 'readonly' ?> required autocomplete="on">

                            </div>
                        </div>
                    </div>
    
                    <div class="form-group mb-3">
                         <label class="form-label" id="attachment" for="attachment">Attachment</label>
                         <input class="form-control" type="file" name="attachment" id="attachment" <?php if ($act == '') echo 'readonly' ?>>
                        </div>

                    <div class="form-group mb-3">
                    <label class="form-label" for="Remark">Remark</label>
                    <textarea class="form-control" name="Remark" id="Remark" rows="3" <?php if ($act == '') echo 'readonly' ?>>
                    <?php if (isset($row['remark'])) echo $row['remark'] ?></textarea>
                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
        <?php
        // Assuming $actionBtnValue should be set based on the value of $act
        $actionBtnValue = ($act == 'addData') ? 'Add' : 'Update';
        ?>
        <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Add Transaction</button>
  
        <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        var action = "<?php echo isset($act) ? $act : ''; ?>";
        centerAlignment("formContainer");
        setButtonColor();
        preloader(300, action);

        
        function validateNumericInput(inputField, errorMsgId, otherErrorMsgId) {
            const inputValue = inputField.value;
            const numericValue = parseFloat(inputValue);

            if (isNaN(numericValue)) {
                inputField.value = inputValue.replace(/[^0-9.]/g, '');
            }

            const currentErrorMsg = document.getElementById(errorMsgId);
            const otherErrorMsg = document.getElementById(otherErrorMsgId);

            if (isNaN(parseFloat(document.getElementById("withdrawal_amount").value))) {
                currentErrorMsg.classList.add("error-message");
                otherErrorMsg.textContent = "";
                otherErrorMsg.classList.remove("error-message");
            } else {
                currentErrorMsg.textContent = "";
                currentErrorMsg.classList.remove("error-message");
            }
        }

    </script>

</body>

</html>
