<?php
$pageTitle = "Marital Status";
include 'menuHeader.php';

$mrtl_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/marital_status_table.php';

// to display data to input
if($mrtl_id)
{
    $rst = getData('*',"id = '$mrtl_id'",MRTL_STATUS,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($mrtl_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addMrtlStatus': case 'updMrtlStatus':
            $mrtl_name = postSpaceFilter('mrtl_name');
            $mrtl_remark = postSpaceFilter('mrtl_remark');

            if($mrtl_name)
            {
                if($action == 'addMrtlStatus')
                {
                    try
                    {
                        $query = "INSERT INTO ".MRTL_STATUS."(name,remark,create_by,create_date,create_time) VALUES ('$mrtl_name','$mrtl_remark','".USER_ID."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($mrtl_name != '')
                            array_push($newvalarr, $mrtl_name);

                        if($mrtl_remark != '')
                            array_push($newvalarr, $mrtl_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$mrtl_name</b> into <b><i>Marital Status Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = MRTL_STATUS;
                        $log['page'] = 'Marital Status';
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
                        $rst = getData('*',"id = '$mrtl_id'",MRTL_STATUS,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $mrtl_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $mrtl_name);
                        }

                        if($row['remark'] != $mrtl_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $mrtl_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                            // edit
                            $query = "UPDATE ".MRTL_STATUS." SET name ='$mrtl_name', remark ='$mrtl_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$mrtl_id'";
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
                            $log['act_msg'] .= " from <b><i>Marital Status Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = MRTL_STATUS;
                            $log['page'] = 'Marital Status';
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
            else $err = "Marital Status name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",MRTL_STATUS,$connect);
            $row = $rst->fetch_assoc();

            $mrtl_id = $row['id'];
            $mrtl_name = $row['name'];

            $query = "DELETE FROM ".MRTL_STATUS." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = USER_ID;
            $log['act_msg'] = USER_NAME . " deleted the data <b>$mrtl_name</b> from <b><i>Marital Status Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = MRTL_STATUS;
            $log['page'] = 'Marital Status';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($mrtl_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $mrtl_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$mrtl_name</b> from <b><i>Marital Status Table</i></b>.";
    $log['page'] = 'Marital Status';
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
    <p><a href="<?= $redirect_page ?>">Marital Status</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
    switch($act)
    {
        case 'I': echo 'Add Marital Status'; break;
        case 'E': echo 'Edit Marital Status'; break;
        default: echo 'View Marital Status';
    }
    ?></p>
</div>

<div id="mrtlFormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="mrtlFormForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Marital Status'; break;
                        case 'E': echo 'Edit Marital Status'; break;
                        default: echo 'View Marital Status';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="mrtl_name_lbl" for="mrtl_name">Marital Status Name</label>
                <input class="form-control" type="text" name="mrtl_name" id="mrtl_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="mrtl_remark_lbl" for="mrtl_remark">Marital Status Remark</label>
                <textarea class="form-control" name="mrtl_remark" id="mrtl_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addMrtlStatus">Add Marital Status</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updMrtlStatus">Edit Marital Status</button>';
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
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Marital Status","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
<script>
/**
  oufei 20231014
  common.fun.js
  function(id)
  to resize form with "centered" class
*/
centerAlignment("mrtlFormContainer");
</script>
</body>
</html>