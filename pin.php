<?php
$pageTitle = "Pin";
include 'menuHeader.php';

$pin_id = input('id');
$act = input('act');
$redirect_page = 'pin_table.php';

// to display data to input
if($pin_id)
{
    $rst = getData('*',"id = '$pin_id'",PIN,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($pin_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addPin': case 'updPin':
            $pin_name = trim(post('pin_name'));
            $pin_remark = trim(post('pin_remark'));

            if($pin_name)
            {
                if($action == 'addPin')
                {
                    try
                    {
                        $query = "INSERT INTO ".PIN." (name,remark,create_by,create_date,create_time) VALUES ('$pin_name','$pin_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($pin_name != '')
                            array_push($newvalarr, $pin_name);

                        if($pin_remark != '')
                            array_push($newvalarr, $pin_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$pin_name</b> into <b><i>Pin Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = PIN;
                        $log['page'] = 'Pin';
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        echo audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
                else
                {
                    try
                    {
                        // take old value
                        $rst = getData('*',"id = '$pin_id'",PIN,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".PIN." SET name ='".$pin_name."', remark ='".$pin_remark."', update_date = 'curdate()', update_time = 'curtime()', update_by ='".$_SESSION['userid']."' WHERE id = '".$pin_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $pin_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $pin_name);
                        }

                        if($row['remark'] != $pin_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $pin_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        // audit log
                        if($oldval != '' && $chgval != '')
                        {    
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
                            $log['act_msg'] .= " from <b><i>Pin Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = PIN;
                            $log['page'] = 'Pin';
                            $log['oldval'] = $oldval;
                            $log['changes'] = $chgval;
                            $log['connect'] = $connect;
                            audit_log($log);
                        }
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $pinnameErr = "Pin name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",PIN,$connect);
            $row = $rst->fetch_assoc();

            $pin_id = $row['id'];
            $pin_name = $row['name'];

            $query = "DELETE FROM ".PIN." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$pin_name</b> from <b><i>Pin Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = PIN;
            $log['page'] = 'Pin';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($pin_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $pin_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$pin_name</b> from <b><i>Pin Table</i></b>.";
    $log['page'] = 'Pin';
    $log['connect'] = $connect;
    audit_log($log);
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/pin.css">
<link rel="stylesheet" href="./css/form.css">
</head>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-6 col-md-6">
        <form id="pinForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Pin'; break;
                        case 'E': echo 'Edit Pin'; break;
                        default: echo 'View Pin';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_name_lbl" for="pin_name">Pin Name</label>
                <input class="form-control" type="text" name="pin_name" id="pin_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($pinnameErr)) echo $pinnameErr; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_remark_lbl" for="pin_remark">Pin Remark</label>
                <textarea class="form-control" name="pin_remark" id="pin_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addPin">Add Pin</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updPin">Edit Pin</button>';
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
    echo '<script>confirmationDialog("","","Pin","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
</html>