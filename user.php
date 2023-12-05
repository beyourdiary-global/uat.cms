<?php
$pageTitle = "User";
include 'menuHeader.php';

$user_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/user_table.php';
$tblname = USR_USER;

// to display data to input
if($user_id)
{
    $rst = getData('*',"id = '$user_id'",$tblname,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

/* if(!($user_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>"); */

if(post('actionBtn'))
{
    $user_username = postSpaceFilter('user_username');
    $user_name = postSpaceFilter('user_name');
    $user_email = postSpaceFilter('user_email');
    $user_password = md5($user_username);
    $user_group = postSpaceFilter('user_group');

    $action = post('actionBtn');

    switch($action)
    {
        case 'addUser': case 'updUser':
            if($user_username == '')
            {
                $err = "Username cannot be empty.";
            }

            if($user_name == '')
            {
                $err2 = "User Name cannot be empty.";
            }

            if($user_email == '')
            {
                $err3 = "User Email cannot be empty.";
            }

            if($user_group == '')
            {
                $err5 = "User Group cannot be empty.";
            }
            
            if(isDuplicateRecord("username", $user_username, $tblname, $connect, $user_id)){
                $err = "Duplicate record found for username name.";
                break;
            }
            else if(isDuplicateRecord("name", $user_name, $tblname, $connect, $user_id)){
                $err2 = "Duplicate record found for user name.";
                break;
            }
            else if(isDuplicateRecord("email", $user_email, $tblname, $connect, $user_id)){
                $err3 = "Duplicate record found for user email.";
                break;
            }
            else if($user_username != '' && $user_name != '' && $user_email != '' && $user_group != 'noneVal')
            {
                if($action == 'addUser')
                {
                    try
                    {
                        $query = "INSERT INTO ".$tblname."(name,username,password_alt,email,access_id,status,create_date,create_time,create_by,fail_count) VALUES ('$user_name','$user_username','$user_password','$user_email','$user_group','A',curdate(),curtime(),'".USER_ID."','0')";
                        mysqli_query($connect, $query);
                        generateDBData($tblname, $connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($user_username != '')
                            array_push($newvalarr, $user_username);

                        if($user_name != '')
                            array_push($newvalarr, $user_name);

                        if($user_email != '')
                            array_push($newvalarr, $user_email);

                        if($user_password != '')
                            array_push($newvalarr, $user_password);
                        
                        if($user_group != '')
                            array_push($newvalarr, $user_group);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$user_username</b> into <b><i>User Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = $tblname;
                        $log['page'] = 'User';
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
                        $rst = getData('*',"id = '$user_id'",$tblname,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['username'] != $user_username)
                        {
                            array_push($oldvalarr, $row['username']);
                            array_push($chgvalarr, $user_username);
                        }

                        if($row['name'] != $user_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $user_name);
                        }

                        if($row['email'] != $user_email)
                        {
                            array_push($oldvalarr, $row['email']);
                            array_push($chgvalarr, $user_email);
                        }

                        if($row['access_id'] != $user_group)
                        {
                            array_push($oldvalarr, $row['access_id']);
                            array_push($chgvalarr, $user_group);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr); 

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                            // edit
                            $query = "UPDATE ".$tblname." SET name ='$user_name', username ='$user_username', email ='$user_email', access_id ='$user_group', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$user_id'";
                            mysqli_query($connect, $query);
                            generateDBData($tblname, $connect);

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
                            $log['act_msg'] .= " from <b><i>User Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = $tblname;
                            $log['page'] = 'User';
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
            // take unit
            $rst = getData('*',"id = '$id'",$tblname,$connect);
            $row = $rst->fetch_assoc();

            $user_id = $row['id'];
            $user_username = $row['name'];

            //SET the record status to 'D'
            deleteRecord($tblname,$id,$user_username,$connect,$cdate,$ctime,$pageTitle);
            
            generateDBData($tblname, $connect);


            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($user_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $user_username = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$user_username</b> from <b><i>User Table</i></b>.";
    $log['page'] = 'User';
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
    <p><a href="<?= $redirect_page ?>">User</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
    switch($act)
    {
        case 'I': echo 'Add User'; break;
        case 'E': echo 'Edit User'; break;
        default: echo 'View User';
    }
    ?></p>
</div>

<div id="userFormContainer" class="container d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="userForm" method="post" action="">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-5">
                            <h2>
                                <?php
                                switch($act)
                                {
                                    case 'I': echo 'Add User'; break;
                                    case 'E': echo 'Edit User'; break;
                                    default: echo 'View User';
                                }
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="user_name_lbl" for="user_name">Name</label>
                            <input class="form-control" type="text" name="user_name" id="user_name" value=
                            "<?php
                                if(isset($user_name))
                                    echo $user_name;
                                else if(isset($dataExisted) && isset($row['name'])) 
                                    echo $row['name'];
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="user_username_lbl" for="user_username">Username</label>
                            <input class="form-control" type="text" name="user_username" id="user_username" value=
                            "<?php
                                if(isset($user_username))
                                    echo $user_username;
                                else if(isset($dataExisted) && isset($row['username'])) 
                                    echo $row['username'];
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="user_email_lbl" for="user_email">Email</label>
                            <input class="form-control" type="text" name="user_email" id="user_email" value=
                            "<?php
                                if(isset($user_email))
                                    echo $user_email;
                                else if(isset($dataExisted) && isset($row['email'])) 
                                    echo $row['email'];
                            ?>" <?php if($act == '') echo 'readonly' ?>>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err3)) echo $err3; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="user_group_lbl" for="user_group">User Group</label>
                            <select class="form-select" id="user_group" name="user_group" <?php if($act == '') echo "disabled"?>>
                            <option value="" disabled selected style="display:none;">Select User Group</option>
                            <?php
                                $user_grp_list = getData('id,name','',USR_GRP,$connect);
                                if($user_grp_list)
                                {
                                    while($row2 = $user_grp_list->fetch_assoc())
                                    {
                                        $selected = '';
                                        $id = $row2['id'];
                                        $grpname = $row2['name'];

                                        if(isset($user_group))
                                        {
                                            if($user_group == $id)
                                                $selected = ' selected';
                                        }
                                        else if(isset($dataExisted))
                                        {
                                            if($row['access_id'] == $id)
                                                $selected = ' selected';
                                        }

                                        echo "<option value=\"$id\" $selected>$grpname</option>";
                                    }
                                }
                            ?>
                            </select>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err5)) echo $err5; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                            switch($act)
                            {
                                case 'I':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addUser">Add User</button>';
                                    break;
                                case 'E':
                                    echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updUser">Edit User</button>';
                                    break;
                            }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
                        </div>
                    </div>
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
    echo '<script>confirmationDialog("","","User","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
<script>
/**
  oufei 20231014
  common.fun.js
  function(id)
  to resize form with "centered" class
*/
centerAlignment("userFormContainer");
</script>
</html>