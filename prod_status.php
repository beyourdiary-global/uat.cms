<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$prod_status_id = input('id');
$act = input('act');

// to display data to input
if($prod_status_id)
{
    $query = "SELECT * FROM ".PROD_STATUS." WHERE id = '".$prod_status_id."'";
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
        case 'addProdStatus': case 'updProdStatus':
            $prod_status_name = post('prod_status_name');
            $prod_status_remark = post('prod_status_remark');

            if($prod_status_name)
            {
                if($action == 'addProdStatus')
                {
                    try
                    {
                        $query = "INSERT INTO ".PROD_STATUS."(name,remark,create_by) VALUES ('$prod_status_name','$prod_status_remark','".$_SESSION['userid']."')";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($prod_status_name != '')
                            array_push($newvalarr, $prod_status_name);

                        if($prod_status_remark != '')
                            array_push($newvalarr, $prod_status_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $prod_status_name into Product Status Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = PROD_STATUS;
                        $log['page'] = 'Product Status';
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
                        $query = "SELECT * FROM ".PROD_STATUS." WHERE id = '$prod_status_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".PROD_STATUS." SET name ='$prod_status_name', remark ='$prod_status_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$prod_status_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $prod_status_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $prod_status_name);
                        }

                        if($row['remark'] != $prod_status_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $prod_status_remark);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$prod_status_id] $prod_status_name from Product Status Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = PROD_STATUS;
                        $log['page'] = 'Product Status';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Product Status name cannot be empty.";
            break;
        case 'back':
            header('Location: prod_status_table.php');
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
            $query = "SELECT * FROM ".PROD_STATUS." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $prod_status_id = $row['id'];
            $prod_status_name = $row['name'];

            $query = "DELETE FROM ".PROD_STATUS." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$prod_status_id] $prod_status_name from Product Status Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = PROD_STATUS;
            $log['page'] = 'Product Status';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($prod_status_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $prod_status_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$prod_status_id] $prod_status_name from Product Status Table.";
    $log['page'] = 'Product Status';
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
                        case 'I': echo 'Add Product Status'; break;
                        case 'E': echo 'Edit Product Status'; break;
                        default: echo 'View Product Status';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="prod_status_name_lbl" for="prod_status_name">Product Status Name</label>
                <input class="form-control" type="text" name="prod_status_name" id="prod_status_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="prod_status_remark_lbl" for="prod_status_remark">Product Status Remark</label>
                <textarea class="form-control" name="prod_status_remark" id="prod_status_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addProdStatus">Add Product Status</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updProdStatus">Edit Product Status</button>';
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
    echo '<script>confirmationDialog("","","Product Status","","prod_status_table.php","'.$act.'");</script>';
}
?>
</body>
</html>