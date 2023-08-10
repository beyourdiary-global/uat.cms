<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$em_type_status_id = input('id');
$act = input('act');

// to display data to input
if($em_type_status_id)
{
    $query = "SELECT * FROM ".EM_TYPE_STATUS." WHERE id = '".$em_type_status_id."'";
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result) == 1)
    {
        $dataExisted = 1;
        $row = $result->fetch_assoc();
    }
}

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addEmTypeStatus': case 'updEmTypeStatus':
            $em_type_status_name = post('em_type_status_name');
            $em_type_status_remark = post('em_type_status_remark');

            if($em_type_status_name)
            {
                if($action == 'addEmTypeStatus')
                {
                    try
                    {
                        $query = "INSERT INTO ".EM_TYPE_STATUS."(name,remark,create_by) VALUES ('$em_type_status_name','$em_type_status_remark','".$_SESSION['userid']."')";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $em_type_status_name into Employment Type Status Table.";
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
                        $query = "SELECT * FROM ".EM_TYPE_STATUS." WHERE id = '$em_type_status_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".EM_TYPE_STATUS." SET name ='$em_type_status_name', remark ='$em_type_status_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$em_type_status_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

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

                        // audit log
                        $log = array();
                        $log['log_act'] = 'edit';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$em_type_status_id] $em_type_status_name from Employment Type Status Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = EM_TYPE_STATUS;
                        $log['page'] = 'Employment Type Status';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Employment Type Status name cannot be empty.";
            break;
        case 'back':
            header('Location: em_type_status_table.php');
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
            $query = "SELECT * FROM ".EM_TYPE_STATUS." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

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
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$em_type_status_id] $em_type_status_name from Employment Type Status Table.";
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
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$em_type_status_id] $em_type_status_name from Employment Type Status Table.";
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
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
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
                <label class="form-label" id="bank_name_lbl" for="em_type_status_name">Employment Type Status Name</label>
                <input class="form-control" type="text" name="em_type_status_name" id="em_type_status_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="bank_remark_lbl" for="em_type_status_remark">Employment Type Status Remark</label>
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
    echo '<script>confirmationDialog("","","Employment Type Status","","em_type_status_table.php","'.$act.'");</script>';
}
?>
</body>
</html>