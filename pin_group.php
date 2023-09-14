<?php
$pageTitle = "Pin Group";
include 'menuHeader.php';

$pin_grp_id = input('id');
$act = input('act');

$redirect_page = 'pin_group_table.php';
$pin_result = getData('*','',PIN,$connect);

if($pin_grp_id)
{
    $rst = getData('*',"id = '$pin_grp_id'",PIN_GRP,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($pin_grp_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addPinGrp': case 'updPinGrp':
            $pin_grp_name = postSpaceFilter('pin_grp_name');
            $pin_grp_remark = postSpaceFilter('pin_grp_remark');
            $pin_grp_pin_arr = post('pin_grp_pin');
            $pin_grp_pin = implode(",", $pin_grp_pin_arr);

            if($pin_grp_name)
            {
                if($action == 'addPinGrp')
                {
                    try
                    {
                        $query = "INSERT INTO ".PIN_GRP."(name,pins,remark,create_by,create_date,create_time) VALUES ('$pin_grp_name','$pin_grp_pin','$pin_grp_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$pin_grp_name</b> into <b><i>Pin Group Table</i></b>.";
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
                        $rst = getData('*',"id = '$pin_grp_id'",PIN_GRP,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

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

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                             // edit
                            $query = "UPDATE ".PIN_GRP." SET name = '$pin_grp_name', pins = '$pin_grp_pin', remark = '$pin_grp_remark' WHERE id = '$pin_grp_id'";
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
                            $log['act_msg'] .= " from <b><i>Pin Group Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = PIN_GRP;
                            $log['page'] = 'Pin Group';
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
            else $pinnameErr = "Pin Group name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",PIN_GRP,$connect);
            $row = $rst->fetch_assoc();

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
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$pin_grp_name</b> from <b><i>Pin Group Table</i></b>.";
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
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$pin_grp_name</b> from <b><i>Pin Group Table</i></b>.";
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
            <div class="form-group my-5">
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
                    <span class="mt-n1"><?php if (isset($pinnameErr)) echo $pinnameErr; ?></span>
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
    echo '<script>confirmationDialog("","","Pin Group","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
</html>