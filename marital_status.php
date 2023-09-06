<?php
include 'menuHeader.php';

$mrtl_id = input('id');
$act = input('act');

// to display data to input
if($mrtl_id)
{
    $query = "SELECT * FROM ".MRTL_STATUS." WHERE id = '".$mrtl_id."'";
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result) == 1)
    {
        $dataExisted = 1;
        $row = $result->fetch_assoc();
    }
}

if(!($mrtl_id) && !($act))
    echo("<script>location.href = 'marital_status_table.php';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addMrtlStatus': case 'updMrtlStatus':
            $mrtl_name = post('mrtl_name');
            $mrtl_remark = post('mrtl_remark');

            if($mrtl_name)
            {
                if($action == 'addMrtlStatus')
                {
                    try
                    {
                        $query = "INSERT INTO ".MRTL_STATUS."(name,remark,create_by,create_date,create_time) VALUES ('$mrtl_name','$mrtl_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($mrtl_name != '')
                            array_push($newvalarr, $mrtl_name);

                        if($mrtl_remark != '')
                            array_push($newvalarr, $mrtl_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $mrtl_name into Marital Status Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = MRTL_STATUS;
                        $log['page'] = 'Marital Status';
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
                        $query = "SELECT * FROM ".MRTL_STATUS." WHERE id = '$mrtl_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".MRTL_STATUS." SET name ='$mrtl_name', remark ='$mrtl_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$mrtl_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $mrtl_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $mrtl_name);
                        }

                        if($row['remark'] != $mrtl_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $mrtl_remark);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$mrtl_id] $mrtl_name from Marital Status Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = MRTL_STATUS;
                        $log['page'] = 'Marital Status';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Marital Status name cannot be empty.";
            break;
        case 'back':
            echo("<script>location.href = 'marital_status_table.php';</script>");
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
            $query = "SELECT * FROM ".MRTL_STATUS." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $mrtl_id = $row['id'];
            $mrtl_name = $row['name'];

            $query = "DELETE FROM ".MRTL_STATUS." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$mrtl_id] $mrtl_name from Marital Status Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = MRTL_STATUS;
            $log['page'] = 'Marital Status';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($mrtl_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $mrtl_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$mrtl_id] $mrtl_name from Marital Status Table.";
    $log['page'] = 'Marital Status';
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
                        case 'I': echo 'Add Marital Status'; break;
                        case 'E': echo 'Edit Marital Status'; break;
                        default: echo 'View Marital Status';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="mrtl_name_lbl" for="mrtl_name">Marital Status Name</label>
                <input class="form-control" type="text" name="mrtl_name" id="mrtl_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="mrtl_remark_lbl" for="mrtl_remark">Marital Status Remark</label>
                <textarea class="form-control" name="mrtl_remark" id="mrtl_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addMrtlStatus">Add Marital Status</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updMrtlStatus">Edit Marital Status</button>';
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
    echo '<script>confirmationDialog("","","Marital Status","","marital_status_table.php","'.$act.'");</script>';
}
?>
</body>
</html>