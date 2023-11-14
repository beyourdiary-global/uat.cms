<?php
$pageTitle = "Customer Segmentation";
include 'menuHeader.php';

$cur_segmentation_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/cus_segmentation_table.php';

// to display data to input
if ($cur_segmentation_id) {
    $rst = getData('*', "id = '$cur_segmentation_id'", CUR_SEGMENTATION, $connect);

    if ($rst != false) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if (!($cur_segmentation_id) && !($act))
    echo ("<script>location.href = '$redirect_page';</script>");

if (post('actionBtn')) {
    $action = post('actionBtn');

    switch ($action) {
        case 'addcur_segmentation':
        case 'updcur_segmentation':
            $cur_segmentation_name = postSpaceFilter('cur_segmentation_name');
            $cur_segmentation_remark = postSpaceFilter('cur_segmentation_remark');
            $color_segmentation = postSpaceFilter('segmentation_color');

            if ($cur_segmentation_name) {
                if ($action == 'addcur_segmentation') {
                    try {
                        $query = "INSERT INTO " . CUR_SEGMENTATION . "(name,remark,colorCode,create_by,create_date,create_time) VALUES ('$cur_segmentation_name','$cur_segmentation_remark','$color_segmentation','" . USER_ID . "',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if ($cur_segmentation_name != '')
                            array_push($newvalarr, $cur_segmentation_name);

                        if ($cur_segmentation_remark != '')
                            array_push($newvalarr, $cur_segmentation_remark);

                        if ($color_segmentation != '')
                            array_push($newvalarr, $color_segmentation);

                        $newval = implode(",", $newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$cur_segmentation_name</b> into <b><i>$pageTitle Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = CUR_SEGMENTATION;
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
                        $rst = getData('*', "id = '$cur_segmentation_id'", CUR_SEGMENTATION, $connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if ($row['name'] != $cur_segmentation_name) {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $cur_segmentation_name);
                        }

                        if ($row['colorCode'] != $color_segmentation) {
                            array_push($oldvalarr, $row['colorCode']);
                            array_push($chgvalarr, $color_segmentation);
                        }

                        if ($row['remark'] != $cur_segmentation_remark) {
                            if ($row['remark'] == '')
                                $old_remark = 'Empty_Value';
                            else $old_remark = $row['remark'];

                            array_push($oldvalarr, $old_remark);

                            if ($cur_segmentation_remark == '')
                                $new_remark = 'Empty_Value';
                            else $new_remark = $cur_segmentation_remark;

                            array_push($chgvalarr, $new_remark);
                        }

                        // convert into string
                        $oldval = implode(",", $oldvalarr);
                        $chgval = implode(",", $chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;

                        if ($oldval != '' && $chgval != '') {
                            // edit
                            $query = "UPDATE " . CUR_SEGMENTATION . " SET name ='$cur_segmentation_name', remark ='$cur_segmentation_remark', colorCode = '$color_segmentation' ,update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE id = '$cur_segmentation_id'";
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
                            $log['query_table'] = CUR_SEGMENTATION;
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
            $rst = getData('*', "id = '$id'", CUR_SEGMENTATION, $connect);
            $row = $rst->fetch_assoc();

            $cur_segmentation_id = $row['id'];
            $cur_segmentation_name = $row['name'];

            //SET the record status to 'D'
            deleteRecord(CUR_SEGMENTATION,$id,$cur_segmentation_name,$connect,$cdate,$ctime,$pageTitle);

            $_SESSION['delChk'] = 1;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if (($cur_segmentation_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1)) {
    $cur_segmentation_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$cur_segmentation_name</b> from <b><i>$pageTitle Table</i></b>.";
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

    <div id="curSegmentationFormContainer" class="container d-flex justify-content-center">
        <div class="col-6 col-md-6 formWidthAdjust">
            <form id="curSegmentationForm" method="post" action="">
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

                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-sm">
                            <label class="form-label" id="cus_segmentation_name_lbl" for="cur_segmentation_name"><?php echo $pageTitle ?> Name</label>
                            <input class="form-control" type="text" name="cur_segmentation_name" id="cur_segmentation_name" value="<?php if (isset($dataExisted) && isset($row['name'])) echo $row['name'] ?>" <?php if ($act == '') echo 'readonly' ?> style="height: 40px;">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                            </div>
                        </div>
                        <div class="col-sm">
                            <label class=" form-label" id="cus_segmentation_color_code_lbl" for="segmentation_color"><?php echo $pageTitle ?> Color</label><br>
                            <div class="col d-flex justify-content-start align-items-center">
                                <input type="color" name="segmentation_color" id="segmentation_color" <?php if ($act == '') echo 'disabled ' ?> value="<?php if (isset($dataExisted) && isset($row['colorCode'])) echo $row['colorCode'] ?>" class="form-control"  style="height: 40px;">
                                <span id="color-display"><?php if (isset($dataExisted) && isset($row['colorCode'])) echo $row['colorCode']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" id="cus_segmentation_remark_lbl" for="cur_segmentation_remark"><?php echo $pageTitle ?> Remark</label>
                    <textarea class="form-control" name="cur_segmentation_remark" id="cur_segmentation_remark" rows="3" <?php if ($act == '') echo 'readonly' ?>><?php if (isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                    switch ($act) {
                        case 'I':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addcur_segmentation">Add ' . $pageTitle . ' </button>';
                            break;
                        case 'E':
                            echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updcur_segmentation">Edit ' . $pageTitle . ' </button>';
                            break;
                    }
                    ?>
                    <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
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
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>

        centerAlignment("curSegmentationFormContainer");

        // JavaScript code to update the color code display
        const colorInput = document.getElementById("segmentation_color");
        const colorDisplay = document.getElementById("color-display");

        // Add an event listener to the color input
        colorInput.addEventListener("input", function() {
            const selectedColor = colorInput.value;
            colorDisplay.textContent = selectedColor;
        });
    </script>
</body>

</html>