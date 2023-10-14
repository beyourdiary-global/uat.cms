<?php
$pageTitle = "Weight Unit";
include 'menuHeader.php';

$wgt_unit_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/weight_unit_table.php';
$tblname = WGT_UNIT;

// to display data to input
if($wgt_unit_id)
{
    $rst = getData('*',"id = '$wgt_unit_id'",$tblname,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($wgt_unit_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addWgtUnit': case 'updWgtUnit':
            $wgt_unit = trim(post('wgt_unit'));
            $wgt_unit_remark = trim(post('wgt_unit_remark'));

            if($wgt_unit)
            {
                if($action == 'addWgtUnit')
                {
                    try
                    {
                        $query = "INSERT INTO ".$tblname."(unit,remark,create_by,create_date,create_time) VALUES ('$wgt_unit','$wgt_unit_remark','".USER_ID."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        generateDBData($tblname, $connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($wgt_unit != '')
                            array_push($newvalarr, $wgt_unit);

                        if($wgt_unit_remark != '')
                            array_push($newvalarr, $wgt_unit_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$wgt_unit</b> into <b><i>Weight Unit Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = $tblname;
                        $log['page'] = 'Weight Unit';
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
                else
                {
                    try
                    {
                        // take old value
                        $rst = getData('*',"id = '$wgt_unit_id'",$tblname,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['unit'] != $wgt_unit)
                        {
                            array_push($oldvalarr, $row['unit']);
                            array_push($chgvalarr, $wgt_unit);
                        }

                        if($row['remark'] != $wgt_unit_remark)
                        {
                            if($row['remark'] == '')
                                $old_remark = 'Empty_Value';
                            else $old_remark = $row['remark'];

                            array_push($oldvalarr, $old_remark);

                            if($wgt_unit_remark == '')
                                $new_remark = 'Empty_Value';
                            else $new_remark = $wgt_unit_remark;
                            
                            array_push($chgvalarr, $wgt_unit_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                            // edit
                            $query = "UPDATE ".$tblname." SET unit ='$wgt_unit', remark ='$wgt_unit_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$wgt_unit_id";
                            mysqli_query($connect, $query);
                            generateDBData($tblname, $connect);

                            // audit log
                            $log = array();
                            $log['log_act'] = 'edit';
                            $log['cdate'] = $cdate;
                            $log['ctime'] = $ctime;
                            $log['uid'] = $log['cby'] = USER_ID;

                            $log['act_msg'] = USER_NAME . " edited the data";
                            for($i=0; $i<sizeof($oldvalarr); $i++)
                            {
                                if($i==0)
                                    $log['act_msg'] .= " from <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                                else
                                    $log['act_msg'] .= ", <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                            }
                            $log['act_msg'] .= " from <b><i>Weight Unit Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = $tblname;
                            $log['page'] = 'Weight Unit';
                            $log['oldval'] = $oldval;
                            $log['changes'] = $chgval;
                            $log['connect'] = $connect;
                            audit_log($log);
                        }
                        else $act = 'NC';
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Weight Unit cannot be empty.";
            break;
        case 'back':
            echo("<script>location.href = '$redirect_page';</script>");
            break;
    }
}

if(post('act') == 'D')
{
    $id = post('id');
    
    if($id)
    {
        try
        {
            // take unit
            $rst = getData('*',"id = '$id'",$tblname,$connect);
            $row = $rst->fetch_assoc();

            $wgt_unit_id = $row['id'];
            $wgt_unit = $row['unit'];

            $query = "DELETE FROM ".$tblname." WHERE id = ".$id;
            mysqli_query($connect, $query);
            generateDBData($tblname, $connect);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = USER_ID;
            $log['act_msg'] = USER_NAME . " deleted the data <b>$wgt_unit</b> from <b><i>Weight Unit Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = $tblname;
            $log['page'] = 'Weight Unit';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($wgt_unit_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $wgt_unit = isset($dataExisted) ? $row['unit'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$wgt_unit</b> from <b><i>Weight Unit Table</i></b>.";
    $log['page'] = 'Weight Unit';
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
    <p><a href="<?= $redirect_page ?>">Weight Unit</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
    switch($act)
    {
        case 'I': echo 'Add Weight Unit'; break;
        case 'E': echo 'Edit Weight Unit'; break;
        default: echo 'View Weight Unit';
    }
    ?></p>
</div>

<div id="wgtunitFormContainer" class="container d-flex justify-content-center">
    <div class="col-8 col-md-6 formWidthAdjust">
        <form id="wgtunitForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Weight Unit'; break;
                        case 'E': echo 'Edit Weight Unit'; break;
                        default: echo 'View Weight Unit';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="bank_name_lbl" for="wgt_unit">Weight Unit Name</label>
                <input class="form-control" type="text" name="wgt_unit" id="wgt_unit" value="<?php if(isset($dataExisted)) echo $row['unit'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="bank_remark_lbl" for="wgt_unit_remark">Weight Unit Remark</label>
                <textarea class="form-control" name="wgt_unit_remark" id="wgt_unit_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addWgtUnit">Add Weight Unit</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updWgtUnit">Edit Weight Unit</button>';
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
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Weight Unit","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
<script>
/**
  oufei 20231014
  common.fun.js
  function(id)
  to resize form with "centered" class
*/
centerAlignment("wgtunitFormContainer");
</script>
</body>
</html>