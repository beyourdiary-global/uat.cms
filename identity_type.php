<?php
$pageTitle = "Identity Type";
include 'menuHeader.php';

$identity_type_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/identity_type_table.php';

// to display data to input
if ($identity_type_id) {
    
    $rst = getData('*', "id = '$identity_type_id'", ID_TYPE, $connect);

    if ($rst != false) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if (!($identity_type_id) && !($act))
    echo ("<script>location.href = '$redirect_page';</script>");
 
if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addIdentity_type':
        case 'updIdentity_type':

            $identity_type_name = postSpaceFilter('identity_type_name');
            $identity_type_remark = postSpaceFilter('identity_type_remark');

            if ($identity_type_name) {
                if ($action == 'addIdentity_type') {
                    try {
                        $query = "INSERT INTO " . ID_TYPE . " (name,remark,create_by,create_date,create_time) VALUES ('$identity_type_name','$identity_type_remark','" . USER_ID . "',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if ($identity_type_name != '')
                            array_push($newvalarr, $identity_type_name);

                        // check value
                        if ($identity_type_remark != '')
                            array_push($newvalarr, $identity_type_remark);

                        $newval = implode(",", $newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$identity_type_name</b> into <b><i>$pageTitle Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = ID_TYPE;
                        $log['page'] = $pageTitle;
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch (Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                } else {
                    try {

                        $rst = getData('*', "id = '$identity_type_id'", ID_TYPE, $connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if ($row['name'] != $identity_type_name) {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $identity_type_name);
                        }

                        if ($row['remark'] != $identity_type_remark) {
                            if ($row['remark'] == '')
                                $old_remark = 'Empty_Value';
                            else $old_remark = $row['remark'];

                            array_push($oldvalarr, $old_remark);

                            if ($identity_type_remark == '')
                                $new_remark = 'Empty_Value';
                            else $new_remark = $identity_type_remark;

                            array_push($chgvalarr, $new_remark);
                        }

                        // convert into string
                        $oldval = implode(",", $oldvalarr);
                        $chgval = implode(",", $chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;

                        if ($oldval != '' && $chgval != '') {
                            // edit
                            $query = "UPDATE " . ID_TYPE . " SET name ='$identity_type_name', remark ='$identity_type_remark', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$identity_type_id'";
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

                            $log['act_msg'] .= " from <b><i>$pageTitle Table</i></b>.";
                            $log['query_rec'] = $query;
                            $log['query_table'] = ID_TYPE;
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
            } else $err = "$pageTitle name cannot be empty.";
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
            $rst = getData('*', "id = '$id'", ID_TYPE, $connect);
            $row = $rst->fetch_assoc();

            $identity_type_id = $row['id'];
            $identity_type_name = $row['name'];

            $query = "DELETE FROM " . ID_TYPE . " WHERE id = " . $id;

            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = USER_ID;
            $log['act_msg'] = USER_NAME . " deleted the data <b>$identity_type_name</b> from <b><i>Identity Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = ID_TYPE;
            $log['page'] = $pageTitle;
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if (($identity_type_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {

    $identity_type_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$identity_type_name</b> from <b><i>$pageTitle Table</i></b>.";
    $log['page'] = $pageTitle;
    $log['connect'] = $connect;
    audit_log($log);
}

?>

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
                    echo 'Add '.$pageTitle;
                    break;
                case 'E':
                    echo 'Edit '.$pageTitle;
                    break;
                default:
                    echo 'View '.$pageTitle;
            }
            ?>
        </p>
    </div>

    <div id="identityTypeFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form action="" method="POST" id="identityTypeForm">
                <div class="form-group md-5">
                    <h2>
                        <?php
                        switch ($act) {
                            case 'I':
                                echo 'Add '.$pageTitle;
                                break;
                            case 'E':
                                echo 'Edit '.$pageTitle;
                                break;
                            default:
                                echo 'View '.$pageTitle;
                        }
                        ?>
                    </h2>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" id="identity_type_name_lbl" for="identity_type_name"><?php echo $pageTitle ?> Name</label>
                    <input type="text" class="form-control" name="identity_type_name" id="identity_type_name" value="<?php if (isset($dataExisted)) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?>>

                    <div id="err_msg">
                        <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" id="identity_type_remark_lbl" for="identity_type_remark"><?php echo $pageTitle ?> Remark</label>
                    <textarea class="form-control" name="identity_type_remark" id="identity_type_remark" rows="3" <?php if ($act == '') echo 'readonly' ?>> <?php if (isset($dataExisted)) echo $row['remark'] ?> </textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addIdentity_type">Add '.$pageTitle.'</button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updIdentity_type">Edit '.$pageTitle.'</button>';
                            break;
                    }
                    ?>
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                </div>
            </form>
        </div>
    </div>

    <?php
    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo '<script>confirmationDialog("","","'.$pageTitle.'","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
        centerAlignment("identityTypeFormContainer");
    </script>
</body>

</html>