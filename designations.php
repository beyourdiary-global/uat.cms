<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$desig_id = input('id');
$act = input('act');

// to display data to input
if($desig_id)
{
    $query = "SELECT * FROM ".DESIG." WHERE id = '".$desig_id."'";
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
        case 'addDesig': case 'updDesig':
            $desig_name = post('desig_name');
            $desig_remark = post('desig_remark');

            if($desig_name)
            {
                if($action == 'addDesig')
                {
                    try
                    {
                        $query = "INSERT INTO ".DESIG."(name,remark,create_by) VALUES ('$desig_name','$desig_remark','".$_SESSION['userid']."')";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
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
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $desig_name into Designations Table.";
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
                        $query = "SELECT * FROM ".DESIG." WHERE id = '$desig_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".DESIG." SET name ='$desig_name', remark ='$desig_remark', update_date = 'curdate()', update_time = 'curtime()', update_by ='".$_SESSION['userid']."' WHERE id = '".$desig_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

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

                        // audit log
                        $log = array();
                        $log['log_act'] = 'edit';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$desig_id] $desig_name from Designations Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = DESIG;
                        $log['page'] = 'Designations';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Designation name cannot be empty.";
            break;
        case 'back':
            header('Location: designations_table.php');
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
            $query = "SELECT * FROM ".DESIG." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $desig_id = $row['id'];
            $desig_name = $row['name'];

            $query = "DELETE FROM ".DESIG." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$desig_id] $desig_name from Designations Table.";
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

if(($desig_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $desig_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$desig_id] $desig_name from Designations Table.";
    $log['page'] = 'Designations';
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
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="desig_remark_lbl" for="desig_remark">Designation Remark</label>
                <textarea class="form-control" name="desig_remark" id="desig_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addDesig">Add Designation</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updDesig">Edit Designation</button>';
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
    echo '<script>confirmationDialog("","","Designation","","designations_table.php","'.$act.'");</script>';
}
?>
</body>
</html>