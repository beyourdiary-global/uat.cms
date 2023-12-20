<?php
$pageTitle = "Leave Type";
include 'menuHeader.php';

$leave_type_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/leave_type_table.php';
$tblname = L_TYPE;

// to display data to input
if ($leave_type_id) {
    $rst = getData('*', "id = '$leave_type_id'", '', $tblname, $connect);

    if ($rst != false) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if (!($leave_type_id) && !($act))
    echo ("<script>location.href = '$redirect_page';</script>");

if (post('actionBtn')) {
    $leave_type = postSpaceFilter('leave_type');
    $num_of_days = postSpaceFilter('num_of_days');
    $auto_assign = postSpaceFilter('auto_assign');

    $action = post('actionBtn');

    switch ($action) {
        case 'addLeaveType':
        case 'updLeaveType':

            if ($leave_type == '') {
                $err = "$pageTitle cannot be empty.";
            }

            if ($num_of_days == '') {
                $err2 = "Number of Days cannot be empty.";
            }

            if (isDuplicateRecord("name", $leave_type, $tblname, $connect, $leave_type_id) && isDuplicateRecord("num_of_days", $num_of_days, $tblname, $connect, $leave_type_id)) {
                $err = "Duplicate record found for $pageTitle record.";
                break;
            } else if ($leave_type != '' && $num_of_days != '' && $auto_assign != '') {
                if ($action == 'addLeaveType') {
                    try {
                        $query = "INSERT INTO " . $tblname . "(name,num_of_days,leave_status,auto_assign,create_date,create_time,create_by) VALUES ('$leave_type','$num_of_days','Active','$auto_assign',curdate(),curtime(),'" . USER_ID . "')";
                        mysqli_query($connect, $query);
                        generateDBData($tblname, $connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if ($leave_type != '')
                            array_push($newvalarr, $leave_type);

                        if ($num_of_days != '')
                            array_push($newvalarr, $num_of_days);

                        if ($auto_assign != '')
                            array_push($newvalarr, $auto_assign);

                        $newval = implode(",", $newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$leave_type</b> into <b><i>$pageTitle Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = $tblname;
                        $log['page'] = $pageTitle;
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch (Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                } else {
                    try {
                        // take old value
                        $rst = getData('*', "id = '$leave_type_id'", '', $tblname, $connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if ($row['name'] != $leave_type) {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $leave_type);
                        }

                        if ($row['num_of_days'] != $num_of_days) {
                            array_push($oldvalarr, $row['num_of_days']);
                            array_push($chgvalarr, $num_of_days);
                        }

                        if ($row['auto_assign'] != $auto_assign) {
                            array_push($oldvalarr, $row['auto_assign']);
                            array_push($chgvalarr, $auto_assign);
                        }


                        // convert into string
                        $oldval = implode(",", $oldvalarr);
                        $chgval = implode(",", $chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if ($oldval != '' && $chgval != '') {
                            // edit
                            $query = "UPDATE " . $tblname . " SET name = '$leave_type', num_of_days = '$num_of_days', auto_assign = '$auto_assign' ,update_date = curdate(), update_time = curtime(), update_by = '" . USER_ID . "' WHERE id = '$leave_type_id'";
                            mysqli_query($connect, $query);
                            generateDBData($tblname, $connect);

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
                            $log['act_msg'] .= " from <b><i>$pageTitle Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = $tblname;
                            $log['page'] = $pageTitle;
                            $log['oldval'] = $oldval;
                            $log['changes'] = $chgval;
                            $log['connect'] = $connect;
                            audit_log($log);
                        } else $act = 'NC';
                    } catch (Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
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
            // take unit
            $rst = getData('*', "id = '$id'", '', $tblname, $connect);
            $row = $rst->fetch_assoc();

            $leave_type_id = $row['id'];
            $leave_type = $row['name'];


            //SET the record status to 'D'
            deleteRecord($tblname, $id, $leave_type, $connect, $cdate, $ctime, $pageTitle);
            generateDBData($tblname, $connect);

            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if (($leave_type_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $leave_type = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$leave_type</b> from <b><i>$pageTitle Table</i></b>.";
    $log['page'] = $pageTitle;
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
        <p><a href="<?= $redirect_page ?>"><?php echo $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?php
            switch ($act) {
                case 'I':
                    echo 'Add ' . $pageTitle;
                    break;
                case 'E':
                    echo 'Edit ' . $pageTitle;
                    break;
                default:
                    echo 'View ' . $pageTitle;
            }
            ?></p>
    </div>

    <div id="leavetypeFormContainer" class="container d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="leavetypeForm" method="post" action="">
                <div class="row d-flex justify-content-center">
                    <div class="col-12 col-md-8">
                        <div class="form-group mb-5">
                            <h2>
                                <?php
                                switch ($act) {
                                    case 'I':
                                        echo 'Add ' . $pageTitle;
                                        break;
                                    case 'E':
                                        echo 'Edit ' . $pageTitle;
                                        break;
                                    default:
                                        echo 'View ' . $pageTitle;
                                }
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row d-flex justify-content-center">
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <label class="form-label form_lbl" for="auto_assign"><?php echo $pageTitle ?> Auto Assign</label>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-check">
                            <label class="form-check-label" for="auto_assign_yes">Yes</label>
                            <input class="form-check-input" type="radio" name="auto_assign" id="auto_assign_yes" value="yes" <?php if ($act == '') echo 'disabled';
                                                                                                                                if (isset($dataExisted, $row['auto_assign']) && $row['auto_assign'] == "yes") echo ' checked'; ?>>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-check">
                            <label class="form-check-label" for="auto_assign_no">No</label>
                            <input class="form-check-input" type="radio" name="auto_assign" id="auto_assign_no" value="no" <?php if ($act == '') echo 'disabled';
                                                                                                                            if (!isset($dataExisted, $row['auto_assign']) || $row['auto_assign'] != "yes") echo ' checked'; ?>>
                        </div>
                    </div>
                </div>

                <div class="row d-flex justify-content-center" style="margin-top: 10px;">
                    <div class="col-12 col-md-8">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="leave_type_lbl" for="leave_type"><?php echo $pageTitle ?></label>
                            <input class="form-control" type="text" name="leave_type" id="leave_type" value="<?php if (isset($leave_type)) echo $leave_type;
                                                                                                                else if (isset($dataExisted) && isset($row['name'])) echo $row['name']; ?>" <?php if ($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row d-flex justify-content-center">
                    <div class="col-12 col-md-8">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="num_of_days_lbl" for="num_of_days">Number of Days</label>
                            <input class="form-control" type="number" min="1" step="1" name="num_of_days" id="num_of_days" value="<?php if (isset($num_of_days)) echo $num_of_days;
                                                                                                                                    else if (isset($dataExisted) && isset($row['num_of_days'])) echo $row['num_of_days']; ?>" <?php if ($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex justify-content-center flex-md-row flex-column">
                            <?php
                            switch ($act) {
                                case 'I':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addLeaveType">Add ' . $pageTitle . '</button>';
                                    break;
                                case 'E':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updLeaveType">Edit ' . $pageTitle . '</button>';
                                    break;
                            }
                            ?>
                            <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                        </div>
                    </div>
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
        echo '<script>confirmationDialog("","","'.$pageTitle.'","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
</body>
<script>
    $(document).ready(function() {
        /**
          oufei 20231014
          common.fun.js
          function(id)
          to resize form with "centered" class
        */
        centerAlignment('leavetypeFormContainer')
    });
</script>

</html>