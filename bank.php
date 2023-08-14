<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$bank_id = input('id');
$act = input('act');

// to display data to input
if($bank_id)
{
    $query = "SELECT * FROM ".BANK." WHERE id = '".$bank_id."'";
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
        case 'addBank': case 'updBank':
            $bank_name = post('bank_name');
            $bank_remark = post('bank_remark');

            if($bank_name)
            {
                if($action == 'addBank')
                {
                    try
                    {
                        $query = "INSERT INTO ".BANK."(name,remark,create_by) VALUES ('$bank_name','$bank_remark','".$_SESSION['userid']."')";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($bank_name != '')
                            array_push($newvalarr, $bank_name);

                        if($bank_remark != '')
                            array_push($newvalarr, $bank_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $bank_name into Bank Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = BANK;
                        $log['page'] = 'Bank';
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
                        $query = "SELECT * FROM ".BANK." WHERE id = '$bank_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".BANK." SET name ='$bank_name', remark ='$bank_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$bank_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $bank_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $bank_name);
                        }

                        if($row['remark'] != $bank_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $bank_remark);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$bank_id] $bank_name from Bank Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = BANK;
                        $log['page'] = 'Bank';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Bank name cannot be empty.";
            break;
        case 'back':
            header('Location: bank_table.php');
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
            $query = "SELECT * FROM ".BANK." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $bank_id = $row['id'];
            $bank_name = $row['name'];

            $query = "DELETE FROM ".BANK." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$bank_id] $bank_name from Bank Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = BANK;
            $log['page'] = 'Bank';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($bank_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $bank_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$bank_id] $bank_name from Bank Table.";
    $log['page'] = 'Bank';
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
                        case 'I': echo 'Add Bank'; break;
                        case 'E': echo 'Edit Bank'; break;
                        default: echo 'View Bank';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="bank_name_lbl" for="bank_name">Bank Name</label>
                <input class="form-control" type="text" name="bank_name" id="bank_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="bank_remark_lbl" for="bank_remark">Bank Remark</label>
                <textarea class="form-control" name="bank_remark" id="bank_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addBank">Add Bank</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updBank">Edit Bank</button>';
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
    echo '<script>confirmationDialog("","","Bank","","bank_table.php","'.$act.'");</script>';
}
?>
</body>
</html>