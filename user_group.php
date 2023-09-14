<?php
$pageTitle = "User Group";
include 'menuHeader.php';

$user_grp_id = input('id');
$act = input('act');
$redirect_page = 'user_group_table.php';

// pin
$pin_result = getData('*','',PIN,$connect);

// pin group
$pin_grp_result = getData('*','',PIN_GRP,$connect);

// check value
$pin_arr = array(); // store exist pin id

// to display data to input
if($user_grp_id)
{
    $rst = getData('*',"id = '$user_grp_id'",USR_GRP,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
        $permission_grp = array();

        // get pin group and pin
        $pins = explode("+", $row['pins']);
        for($i=0; $i<count($pins); $i++)
        {
            $pins[$i] = str_replace("[", "", $pins[$i]);
            $pins[$i] = str_replace("]", "", $pins[$i]);
        }

        foreach($pins as $x)
        {
            $colonpos = stripos($x, ":");
            $tmp_pingrp = substr($x, 0, $colonpos);
            $tmp_pin = substr($x, $colonpos);
            $tmp_pin = str_replace(":","",$tmp_pin);
            $tmp_pin = explode(",",$tmp_pin);
            $permission_grp[$tmp_pingrp] = $tmp_pin;
        }
        $permission_grp_keys = array_keys($permission_grp);
        $permission_grp_count = count($permission_grp);
    }
}

if(!($user_grp_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{  
    $action = post('actionBtn');

    switch($action)
    {
        case 'addGrp': case 'updGrp':
            $user_grp_name = postSpaceFilter('user_grp_name');
            $user_grp_remark = postSpaceFilter('user_grp_remark');

            if($user_grp_name)
            {
                $arr = post('user_grp_chkbox_val');
                $storevalue = array();

                // convert all array into string
                if($arr)
                {
                    // get pin group
                    $keys = implode(",", array_keys($arr));
                    $keys_arr = explode(",",$keys);

                    foreach($keys_arr as $x)
                    {
                        $value = implode(",",$arr[$x]);
                        $temp = "[".$x.":".$value."]";  // ex. [<pingrp>:<permission>]
                        array_push($storevalue,$temp);
                    }

                    $permission_grp = implode("+", $storevalue);
                }

                if($action == 'addGrp')
                {
                    try
                    {
                        $query = "INSERT INTO ".USR_GRP."(name,pins,remark,create_by,create_date,create_time) VALUES ('$user_grp_name','$permission_grp','$user_grp_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($user_grp_name != '')
                            array_push($newvalarr, $user_grp_name);

                        if($user_grp_remark != '')
                            array_push($newvalarr, $user_grp_remark);

                        if($permission_grp != '')
                            array_push($newvalarr, $permission_grp);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$user_grp_name</b> into <b><i>User Permission Group Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = USR_GRP;
                        $log['page'] = 'User Permission Group';
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
                        $rst = getData('*',"id = '$user_grp_id'",USR_GRP,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $user_grp_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $user_grp_name);
                        }

                        if($row['remark'] != $user_grp_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $user_grp_remark);
                        }

                        if($row['pins'] != $permission_grp)
                        {
                            array_push($oldvalarr, $row['pins']);
                            array_push($chgvalarr, $permission_grp);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                            // edit
                            $query = "UPDATE ".USR_GRP." SET name = '$user_grp_name', pins = '$permission_grp', remark = '$user_grp_remark' WHERE id = '$user_grp_id'";
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
                            $log['act_msg'] = $_SESSION['user_name'] . " edited the data <b>$user_grp_name</b> from <b><i>User Permission Group Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = USR_GRP;
                            $log['page'] = 'User Permission Group';
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
            else $err = "User Group Name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",USR_GRP,$connect);
            $row = $rst->fetch_assoc();

            $user_grp_id = $row['id'];
            $user_grp_name = $row['name'];

            $query = "DELETE FROM ".USR_GRP." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$user_grp_name</b> from <b><i>User Permission Group Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = USR_GRP;
            $log['page'] = 'User Permission Group';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($user_grp_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $user_grp_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$user_grp_name</b> from <b><i>User Permission Group Table</i></b>.";
    $log['page'] = 'User Permission Group';
    $log['connect'] = $connect;
    audit_log($log);
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
</head>

<style>
thead { 
    position: sticky; top:0; z-index: 1;
}

tbody {
    
    height: 500px;
    overflow-y: auto;
    overflow-x: hidden;
}
</style>

<body>

<div id="dispTable" class="container d-flex justify-content-center">
    <div class="col-9 col-md-9">
        <form id="pinForm" method="post" action="">
            <div class="form-group mt-5 mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add User Group'; break;
                        case 'E': echo 'Edit User Group'; break;
                        default: echo 'View User Group';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="user_grp_name_lbl" for="user_grp_name">User Group Name</label>
                <input class="form-control" type="text" name="user_grp_name" id="user_grp_name" value="<?php if(isset($dataExisted)) echo $row['name']; ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="permission_table_lbl" for="permission_table">Permissions</label>
                <div class="table-responsive" style="max-height: 500px;">
                <table class="table table-striped" id="permission_table">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <?php 
                            if(mysqli_num_rows($pin_result) != 0) {
                                while($pin_row = $pin_result->fetch_assoc())
                                {
                            ?>
                            <th class="text-center" scope="col">
                                <?php 
                                    echo $pin_row['name'];
                                    array_push($pin_arr, $pin_row['id']);
                                ?>
                            </th>
                            <?php 
                                }
                                $pin_arr_num = count($pin_arr);
                            } 
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($pin_grp_result) != 0) {
                            while($pin_grp_row = $pin_grp_result->fetch_assoc())
                            {
                                // get pin
                                $pin_grp_pins = explode(",", $pin_grp_row['pins']);
                                $pin_grp_pins_count = count($pin_grp_pins);
                        ?>
                        <tr id="<?php echo $pin_grp_row['name'].'_row['.$pin_grp_row['id'].']' ?>" name="<?php echo $pin_grp_row['name'].'_row' ?>">
                            <th scope="row"><?php echo $pin_grp_row['name'] ?></th>
                            <?php
                                for($i=0; $i<$pin_arr_num; $i++)
                                {
                                    $found = 0;
                                    $checked = '';

                                    for($j=0; $j<$pin_grp_pins_count; $j++)
                                    {
                                        // check if pin exist in pin group
                                        if($pin_arr[$i] == $pin_grp_pins[$j])
                                        {
                                            // check if pin checked (act: edit/view)
                                            if((isset($act)) && ($act != 'I'))
                                            {
                                                for($k=0; $k<$permission_grp_count; $k++)
                                                {
                                                    if($permission_grp_keys[$k] == $pin_grp_row['id'])
                                                    {
                                                        if(is_array($permission_grp[$permission_grp_keys[$k]]) || is_object($permission_grp[$permission_grp_keys[$k]]))
                                                        {
                                                            foreach($permission_grp[$permission_grp_keys[$k]] as $val)
                                                            {
                                                                if($val == $pin_grp_pins[$j])
                                                                    $checked = " checked";
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if($act == '')
                                                $readonly = ' disabled';
                                            else $readonly = '';

                                            echo '<td class="text-center" scope="row"><input class="form-check-input" type="checkbox" name="user_grp_chkbox_val['.$pin_grp_row['id'].'][]" value="'.$pin_arr[$i].'"'.$checked.$readonly.'></td>';
                                            $found = 1;
                                        } 
                                    }
                                    if($found != 1)
                                        echo '<td scope="row"></td>';
                                }
                            ?>
                        </tr>
                        <?php } } ?>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="user_grp_remark_lbl" for="user_grp_remark">User Group Remark</label>
                <textarea class="form-control" name="user_grp_remark" id="user_grp_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 mb-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addGrp">Add User Group</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updGrp">Edit User Group</button>';
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
    echo '<script>confirmationDialog("","","User Permission Group","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
</html>