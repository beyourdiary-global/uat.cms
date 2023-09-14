<?php
$pageTitle = "Employment Type Status";
include 'menuHeader.php';

$em_type_status_id = input('id');
$act = input('act');
$redirect_page = 'em_type_status_table.php';

// to display data to input
if($em_type_status_id)
{
    $rst = getData('*',"id = '$em_type_status_id'",EM_TYPE_STATUS,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($em_type_status_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addEmTypeStatus': case 'updEmTypeStatus':
            $em_type_status_name = postSpaceFilter('em_type_status_name');
            $em_type_status_remark = postSpaceFilter('em_type_status_remark');

            if($em_type_status_name)
            {
                if($action == 'addEmTypeStatus')
                {
                    try
                    {
                        $query = "INSERT INTO ".EM_TYPE_STATUS."(name,remark,create_by,create_date,create_time) VALUES ('$em_type_status_name','$em_type_status_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($em_type_status_name != '')
                            array_push($newvalarr, $em_type_status_name);

                        if($em_type_status_remark != '')
                            array_push($newvalarr, $em_type_status_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$em_type_status_name</b> into <b><i>Employment Type Status Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = EM_TYPE_STATUS;
                        $log['page'] = 'Employment Type Status';
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
                        $rst = getData('*',"id = '$em_type_status_id'",EM_TYPE_STATUS,$connect);;
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $em_type_status_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $em_type_status_name);
                        }

                        if($row['remark'] != $em_type_status_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $em_type_status_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                            // edit
                            $query = "UPDATE ".EM_TYPE_STATUS." SET name ='$em_type_status_name', remark ='$em_type_status_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '$em_type_status_id'";
                            mysqli_query($connect, $query);
                            
                            // audit log
                            $log = array();
                            $log['log_act'] = 'edit';
                            $log['cdate'] = $cdate;
                            $log['ctime'] = $ctime;
                            $log['uid'] = $log['cby'] = $_SESSION['userid'];

                            $log['act_msg'] = $_SESSION['user_name'] . " edited the data";
                            for($i=0; $i<sizeof($oldvalarr); $i++)
                            {
                                if($i==0)
                                    $log['act_msg'] .= " from <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                                else
                                    $log['act_msg'] .= ", <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                            }
                            $log['act_msg'] .= " from <b><i>Employment Type Status Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = EM_TYPE_STATUS;
                            $log['page'] = 'Employment Type Status';
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
            else $err = "Employment Type Status name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",EM_TYPE_STATUS,$connect);
            $row = $rst->fetch_assoc();

            $em_type_status_id = $row['id'];
            $em_type_status_name = $row['name'];

            $query = "DELETE FROM ".EM_TYPE_STATUS." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$em_type_status_name</b> from <b><i>Employment Type Status Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = EM_TYPE_STATUS;
            $log['page'] = 'Employment Type Status';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($em_type_status_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $em_type_status_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$em_type_status_name</b> from <b><i>Employment Type Status Table</i></b>.";
    $log['page'] = 'Employment Type Status';
    $log['connect'] = $connect;
    audit_log($log);
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/form.css">
</head>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-6 col-md-6">
        <form id="desigForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Employment Type Status'; break;
                        case 'E': echo 'Edit Employment Type Status'; break;
                        default: echo 'View Employment Type Status';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="em_type_status_name_lbl" for="em_type_status_name">Employment Type Status Name</label>
                <input class="form-control" type="text" name="em_type_status_name" id="em_type_status_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="em_type_status_remark_lbl" for="em_type_status_remark">Employment Type Status Remark</label>
                <textarea class="form-control" name="em_type_status_remark" id="em_type_status_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addEmTypeStatus">Add Employment Type Status</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updEmTypeStatus">Edit Employment Type Status</button>';
                        break;
                }
            ?>
                <button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="back">Back</button>
            </div>
        </form>
    </div>
</div>
<?php
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Employment Type Status","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
</html>