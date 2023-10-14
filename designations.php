<?php
$pageTitle = "Designations";
include 'menuHeader.php';

$desig_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/designations_table.php';

// to display data to input
if($desig_id)
{
    $rst = getData('*',"id = '$desig_id'",DESIG,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($desig_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addDesig': case 'updDesig':
            $desig_name = postSpaceFilter('desig_name');
            $desig_remark = postSpaceFilter('desig_remark');

            if($desig_name)
            {
                if($action == 'addDesig')
                {
                    try
                    {
                        $query = "INSERT INTO ".DESIG."(name,remark,create_by,create_date,create_time) VALUES ('$desig_name','$desig_remark','".USER_ID."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($desig_name != '')
                            array_push($newvalarr, $desig_name);

                        if($desig_remark != '')
                            array_push($newvalarr, $desig_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$desig_name</b> into <b><i>Designations Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = DESIG;
                        $log['page'] = 'Designations';
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
                        $rst = getData('*',"id = '$desig_id'",DESIG,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $desig_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $desig_name);
                        }

                        if($row['remark'] != $desig_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $desig_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {    
                            // edit
                            $query = "UPDATE ".DESIG." SET name ='$desig_name', remark ='$desig_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$desig_id'";
                            mysqli_query($connect, $query);

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
                            $log['act_msg'] .= " from <b><i>Designations Table</i></b>.";
                            
                            $log['query_rec'] = $query;
                            $log['query_table'] = DESIG;
                            $log['page'] = 'Designations';
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
            else $err = "Designation name cannot be empty.";
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
            // take name
            $rst = getData('*',"id = '$id'",DESIG,$connect);
            $row = $rst->fetch_assoc();

            $desig_id = $row['id'];
            $desig_name = $row['name'];

            $query = "DELETE FROM ".DESIG." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = USER_ID;
            $log['act_msg'] = USER_NAME . " deleted the data <b>$desig_name</b> from <b><i>Designations Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = DESIG;
            $log['page'] = 'Designations';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($desig_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $desig_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$desig_name</b> from <b><i>Designations Table</i></b>.";
    $log['page'] = 'Designations';
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
    <p><a href="<?= $redirect_page ?>">Designation</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
    switch($act)
    {
        case 'I': echo 'Add Designation'; break;
        case 'E': echo 'Edit Designation'; break;
        default: echo 'View Designation';
    }
    ?></p>
</div>

<div id="desigFormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="desigForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Designation'; break;
                        case 'E': echo 'Edit Designation'; break;
                        default: echo 'View Designation';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="desig_name_lbl" for="desig_name">Designation Name</label>
                <input class="form-control" type="text" name="desig_name" id="desig_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="desig_remark_lbl" for="desig_remark">Designation Remark</label>
                <textarea class="form-control" name="desig_remark" id="desig_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <div class="form-group mb-3 d-flex justify-content-center flex-md-row flex-column">
                    <?php
                        switch($act)
                        {
                            case 'I':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addDesig">Add Designation</button>';
                                break;
                            case 'E':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updDesig">Edit Designation</button>';
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
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Designation","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
<script>
/**
  oufei 20231014
  common.fun.js
  function(id)
  to resize form with "centered" class
*/
centerAlignment("desigFormContainer");
</script>
</body>
</html>