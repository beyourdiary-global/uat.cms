<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$cur_unit_id = input('id');
$act = input('act');

// to display data to input
if($cur_unit_id)
{
    $query = "SELECT * FROM ".CUR_UNIT." WHERE id = '".$cur_unit_id."'";
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
        case 'addCurUnit': case 'updCurUnit':
            $cur_unit = post('cur_unit');
            $cur_unit_remark = post('cur_unit_remark');

            if($cur_unit)
            {
                if($action == 'addCurUnit')
                {
                    try
                    {
                        $query = "INSERT INTO ".CUR_UNIT."(unit,remark,create_by) VALUES ('$cur_unit','$cur_unit_remark','".$_SESSION['userid']."')";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($cur_unit != '')
                            array_push($newvalarr, $cur_unit);

                        if($cur_unit_remark != '')
                            array_push($newvalarr, $cur_unit_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $cur_unit into Currency Unit Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = CUR_UNIT;
                        $log['page'] = 'Currency Unit';
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
                        $query = "SELECT * FROM ".CUR_UNIT." WHERE id = '$cur_unit_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".CUR_UNIT." SET unit ='$cur_unit', remark ='$cur_unit_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$cur_unit_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['unit'] != $cur_unit)
                        {
                            array_push($oldvalarr, $row['unit']);
                            array_push($chgvalarr, $cur_unit);
                        }

                        if($row['remark'] != $cur_unit_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $cur_unit_remark);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$cur_unit_id] $cur_unit from Currency Unit Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = CUR_UNIT;
                        $log['page'] = 'Currency Unit';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Currency Unit name cannot be empty.";
            break;
        case 'back':
            header('Location: currency_unit_table.php');
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
            $query = "SELECT * FROM ".CUR_UNIT." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $cur_unit_id = $row['id'];
            $cur_unit = $row['unit'];

            $query = "DELETE FROM ".CUR_UNIT." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$cur_unit_id] $cur_unit from Currency Unit Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = CUR_UNIT;
            $log['page'] = 'Currency Unit';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($cur_unit_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $cur_unit = isset($dataExisted) ? $row['unit'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$cur_unit_id] $cur_unit from Currency Unit Table.";
    $log['page'] = 'Currency Unit';
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
                        case 'I': echo 'Add Currency Unit'; break;
                        case 'E': echo 'Edit Currency Unit'; break;
                        default: echo 'View Currency Unit';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label form_lbl" id="cur_unit_lbl" for="cur_unit">Currency Unit</label>
                <input class="form-control" type="text" name="cur_unit" id="cur_unit" value="<?php if(isset($dataExisted)) echo $row['unit'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label form_lbl" id="cur_unit_remark_lbl" for="cur_unit_remark">Currency Unit Remark</label>
                <textarea class="form-control" name="cur_unit_remark" id="cur_unit_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addCurUnit">Add Currency Unit</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updCurUnit">Edit Currency Unit</button>';
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
    echo '<script>confirmationDialog("","","Currency Unit","","currency_unit_table.php","'.$act.'");</script>';
}
?>
</body>
</html>