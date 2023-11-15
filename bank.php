<?php
$pageTitle = "Bank";
include 'menuHeader.php';

$bank_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/bank_table.php';

// to display data to input
if ($bank_id) {
    $rst = getData('*', "id = '$bank_id'", BANK, $connect);

    if ($rst != false) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if (!($bank_id) && !($act))
    echo ("<script>location.href = '$redirect_page';</script>");

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addBank':
        case 'updBank':
            $bank_name = postSpaceFilter('bank_name');
            $bank_remark = postSpaceFilter('bank_remark');

            if (!$bank_name)
                $err = "Bank name cannot be empty.";

            else if(isDuplicateRecord("name", $bank_name, BANK, $connect, $bank_id))
                $err = "Duplicate record found for Bank name.";

            else if($action == 'addBank') {
                try {
                    $query = "INSERT INTO " . BANK . "(name,remark,create_by,create_date,create_time) VALUES ('$bank_name','$bank_remark','" . USER_ID . "',curdate(),curtime())";
                    mysqli_query($connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;

                    $newvalarr = array();

                    // check value
                    if ($bank_name != '')
                        array_push($newvalarr, $bank_name);

                    if ($bank_remark != '')
                        array_push($newvalarr, $bank_remark);

                    $newval = implode(",", $newvalarr);

                    // audit log
                    $log = array();
                    $log['log_act'] = 'add';
                    $log['cdate'] = $cdate;
                    $log['ctime'] = $ctime;
                    $log['uid'] = $log['cby'] = USER_ID;
                    $log['act_msg'] = USER_NAME . " added <b>$bank_name</b> into <b><i>Bank Table</i></b>.";
                    $log['query_rec'] = $query;
                    $log['query_table'] = BANK;
                    $log['page'] = 'Bank';
                    $log['newval'] = $newval;
                    $log['connect'] = $connect;
                    audit_log($log);
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "id = '$bank_id'", BANK, $connect);
                    $row = $rst->fetch_assoc();
                    $oldvalarr = $chgvalarr = array();

                    // check value
                    if ($row['name'] != $bank_name) {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $bank_name);
                    }

                    if ($row['remark'] != $bank_remark) {
                        if ($row['remark'] == '')
                            $old_remark = 'Empty_Value';
                        else $old_remark = $row['remark'];

                        array_push($oldvalarr, $old_remark);

                        if ($bank_remark == '')
                            $new_remark = 'Empty_Value';
                        else $new_remark = $bank_remark;

                        array_push($chgvalarr, $new_remark);
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);

                    $_SESSION['tempValConfirmBox'] = true;
                    if ($oldval != '' && $chgval != '') {
                        // edit
                        $query = "UPDATE " . BANK . " SET name ='$bank_name', remark ='$bank_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$bank_id'";
                        mysqli_query($connect, $query);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'edit';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;

                        $log['act_msg'] = USER_NAME . " edited the data";
                        for ($i = 0; $i < sizeof($oldvalarr); $i++) {
                            if ($i == 0)
                                $log['act_msg'] .= " from <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
                            else
                                $log['act_msg'] .= ", <b>\'" . $oldvalarr[$i] . "\'</b> to <b>\'" . $chgvalarr[$i] . "\'</b>";
                        }
                        $log['act_msg'] .= " from <b><i>Bank Table</i></b>.";

                        $log['query_rec'] = $query;
                        $log['query_table'] = BANK;
                        $log['page'] = 'Bank';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } else $act = 'NC';
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            }
            break;
        case 'back':
            echo ("<script>location.href = '$redirect_page';</script>");
            break;
    }
}

if (post('act') == 'D') {
    $id = post('id');

    if ($id) {
        try {
            // take name
            $rst = getData('*', "id = '$id'", BANK, $connect);
            $row = $rst->fetch_assoc();

            $bank_id = $row['id'];
            $bank_name = $row['name'];

            //SET the record status to 'D'
            deleteRecord(BANK, $id, $bank_name, $connect, $cdate, $ctime, $pageTitle);

            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if (($bank_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $bank_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$bank_name</b> from <b><i>Bank Table</i></b>.";
    $log['page'] = 'Bank';
    $log['connect'] = $connect;
    audit_log($log);
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
</head>

<body>

    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>">Bank</a> <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?php
            switch ($act) {
                case 'I':
                    echo 'Add Bank';
                    break;
                case 'E':
                    echo 'Edit Bank';
                    break;
                default:
                    echo 'View Bank';
            }
            ?></p>
    </div>

    <div id="bankFormContainer" class="container d-flex justify-content-center">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="bankForm" method="post" action="">
                <div class="form-group mb-5">
                    <h2>
                        <?php
                        switch ($act) {
                            case 'I':
                                echo 'Add Bank';
                                break;
                            case 'E':
                                echo 'Edit Bank';
                                break;
                            default:
                                echo 'View Bank';
                        }
                        ?>
                    </h2>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" id="bank_name_lbl" for="bank_name">Bank Name</label>
                    <input class="form-control" type="text" name="bank_name" id="bank_name" value="<?php if (isset($dataExisted) && isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?>>
                    <div id="err_msg">
                        <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" id="bank_remark_lbl" for="bank_remark">Bank Remark</label>
                    <textarea class="form-control" name="bank_remark" id="bank_remark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addBank">Add Bank</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updBank">Edit Bank</button>';
                            break;
                    }
                    ?>
                    <button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
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
        echo '<script>confirmationDialog("","","Bank","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
        /**
  oufei 20231014
  common.fun.js
  function(id)
  to resize form with "centered" class
*/
        centerAlignment("bankFormContainer");
    </script>
</body>

</html>