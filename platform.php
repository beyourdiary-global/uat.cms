<?php
$pageTitle = "Platform";
include 'menuHeader.php';

$pltf_id = input('id');
$act = input('act');
$redirect_page = 'platform_table.php';

// to display data to input
if($pltf_id)
{
    $rst = getData('*',"id = '$pltf_id'",PLTF,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($pltf_id) && !($act))
    echo("<script>location.href = ''.$redirect_page.'';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addPltf': case 'updPltf':
            $pltf_name = postEmptyFilter('pltf_name');
            $pltf_remark = postEmptyFilter('pltf_remark');

            if($pltf_name)
            {
                if($action == 'addPltf')
                {
                    try
                    {
                        $query = "INSERT INTO ".PLTF."(name,remark,create_by,create_date,create_time) VALUES ('$pltf_name','$pltf_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($pltf_name != '')
                            array_push($newvalarr, $pltf_name);

                        if($pltf_remark != '')
                            array_push($newvalarr, $pltf_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$pltf_name</b> into <b><i>Platform Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = PLTF;
                        $log['page'] = 'Platform';
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
                        $rst = getData('*',"id = '$pltf_id'",PLTF,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".PLTF." SET name ='$pltf_name', remark ='$pltf_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$pltf_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $pltf_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $pltf_name);
                        }

                        if($row['remark'] != $pltf_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $pltf_remark);
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
                            $log['act_msg'] .= " from <b><i>Platform Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = PLTF;
                            $log['page'] = 'Platform';
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
            else $err = "Platform name cannot be empty.";
            break;
        case 'back':
            echo("<script>location.href = ''.$redirect_page.'';</script>");
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
            $rst = getData('*',"id = '$id'",PLTF,$connect);
            $row = $rst->fetch_assoc();

            $pltf_id = $row['id'];
            $pltf_name = $row['name'];

            $query = "DELETE FROM ".PLTF." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$pltf_name</b> from <b><i>Platform Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = PLTF;
            $log['page'] = 'Platform';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($pltf_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $pltf_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$pltf_name</b> from <b><i>Platform Table</i></b>.";
    $log['page'] = 'Platform';
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
                        case 'I': echo 'Add Platform'; break;
                        case 'E': echo 'Edit Platform'; break;
                        default: echo 'View Platform';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pltf_name_lbl" for="pltf_name">Platform Name</label>
                <input class="form-control" type="text" name="pltf_name" id="pltf_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pltf_remark_lbl" for="pltf_remark">Platform Remark</label>
                <textarea class="form-control" name="pltf_remark" id="pltf_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addPltf">Add Platform</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updPltf">Edit Platform</button>';
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
    echo '<script>confirmationDialog("","","Platform","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
</html>