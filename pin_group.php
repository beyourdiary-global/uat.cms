<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$pin_grp_id = input('id');
$act = input('act');

$pin_qry = "SELECT * FROM ".PIN;
$pin_result = mysqli_query($connect, $pin_qry);

if($pin_grp_id)
{
    $query = "SELECT * FROM ".PIN_GRP." WHERE id = '".$pin_grp_id."'";
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
        case 'addPinGrp': case 'updPinGrp':
            $pin_grp_name = post('pin_grp_name');
            $pin_grp_remark = post('pin_grp_remark');
            $pin_grp_pin_arr = post('pin_grp_pin');
            $pin_grp_pin = implode(",", $pin_grp_pin_arr);

            if($pin_grp_name)
            {
                if($action == 'addPinGrp')
                {
                    try
                    {
                        $query = "INSERT INTO ".PIN_GRP."(name,pins,remark) VALUES ('$pin_grp_name', '$pin_grp_pin', '$pin_grp_remark')";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($pin_grp_name != '')
                            array_push($newvalarr, $pin_grp_name);

                        if($pin_grp_remark != '')
                            array_push($newvalarr, $pin_grp_remark);

                        if($pin_grp_pin != '')
                            array_push($newvalarr, $pin_grp_pin);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $pin_grp_name into Pin Group Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = PIN_GRP;
                        $log['page'] = 'Pin Group';
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
                        $query = "SELECT * FROM ".PIN_GRP." WHERE id = '".$pin_grp_id."'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".PIN_GRP." SET name = '$pin_grp_name', pins = '$pin_grp_pin', remark = '$pin_grp_remark' WHERE id = '$pin_grp_id'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $pin_grp_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $pin_grp_name);
                        }

                        if($row['remark'] != $pin_grp_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $pin_grp_remark);
                        }

                        if($row['pins'] != $pin_grp_pin)
                        {
                            array_push($oldvalarr, $row['pins']);
                            array_push($chgvalarr, $pin_grp_pin);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$pin_grp_id] $pin_grp_name from Pin Group Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = PIN_GRP;
                        $log['page'] = 'Pin Group';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $pinnameErr = "Pin Group name cannot be empty.";
            break;
        case 'back':
            header('Location: pin_group_table.php');
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
            $query = "SELECT * FROM ".PIN_GRP." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $pin_grp_id = $row['id'];
            $pin_grp_name = $row['name'];

            $query = "DELETE FROM ".PIN_GRP." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$pin_grp_id] $pin_grp_name from Pin Group Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = PIN_GRP;
            $log['page'] = 'Pin Group';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($pin_grp_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $pin_grp_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$pin_grp_id] $pin_grp_name from Pin Group Table.";
    $log['page'] = 'Pin Group';
    $log['connect'] = $connect;
    audit_log($log);
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/pin.css">
</head>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        <form id="pinForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Pin Group'; break;
                        case 'E': echo 'Edit Pin Group'; break;
                        default: echo 'View Pin Group';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_grp_name_lbl" for="pin_grp_name">Pin Group Name</label>
                <input class="form-control" type="text" name="pin_grp_name" id="pin_grp_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($pinnameErr)) echo $pinnameErr; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_grp_remark_lbl" for="pin_grp_remark">Pin Group Remark</label>
                <textarea class="form-control" name="pin_grp_remark" id="pin_grp_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>
            
            <div class="form-group mb-3">
            <label class="mb-3" id="pin_lbl" for="">Pin</label>
            <?php
            while($pin_row = $pin_result->fetch_assoc())
            {
            ?>
            <div class="form-check">
                <label class="form-label" id="pin_name_lbl" for="pin_name"><?php echo $pin_row['name'] ?></label>
                <input class="form-check-input" name="pin_grp_pin[]" type="checkbox" value="<?php echo $pin_row['id']?>" id="pin_grp_pin[]" <?php
                if(isset($dataExisted))
                {
                    $pins = $row['pins'];
                    $curr_pin = $pin_row['id'];
                    if(preg_match("/$curr_pin/i", $pins))
                        echo "checked ";

                    if($act == '') 
                        echo 'disabled';
                }
                ?>>
            </div>
            <?php
            }
            ?>
            </div>

            <div class="form-group d-flex justify-content-center">
            <?php            
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addPinGrp">Add Pin Group</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updPinGrp">Edit Pin Group</button>';
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
    echo '<script>confirmationDialog("","","Pin Group","","pin_group_table.php","'.$act.'");</script>';
}
?>
</body>
</html>