<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$warehouse_id = input('id');
$act = input('act');

// to display data to input
if($warehouse_id)
{
    $query = "SELECT * FROM ".WHSE." WHERE id = '".$warehouse_id."'";
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
        case 'addWarehouse': case 'updWarehouse':
            $warehouse_name = post('warehouse_name');
            $warehouse_remark = post('warehouse_remark');

            if($warehouse_name)
            {
                if($action == 'addWarehouse')
                {
                    try
                    {
                        $query = "INSERT INTO ".WHSE."(name,remark,create_by,create_date,create_time) VALUES ('$warehouse_name','$warehouse_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($warehouse_name != '')
                            array_push($newvalarr, $warehouse_name);

                        if($warehouse_remark != '')
                            array_push($newvalarr, $warehouse_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $warehouse_name into Warehouse Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = WHSE;
                        $log['page'] = 'Warehouse';
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
                        $query = "SELECT * FROM ".WHSE." WHERE id = '$warehouse_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".WHSE." SET name ='$warehouse_name', remark ='$warehouse_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$warehouse_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $warehouse_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $warehouse_name);
                        }

                        if($row['remark'] != $warehouse_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $warehouse_remark);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$warehouse_id] $warehouse_name from Warehouse Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = WHSE;
                        $log['page'] = 'Warehouse';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Warehouse name cannot be empty.";
            break;
        case 'back':
            header('Location: warehouse_table.php');
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
            $query = "SELECT * FROM ".WHSE." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $warehouse_id = $row['id'];
            $warehouse_name = $row['name'];

            $query = "DELETE FROM ".WHSE." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$warehouse_id] $warehouse_name from Warehouse Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = WHSE;
            $log['page'] = 'Warehouse';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($warehouse_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $warehouse_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$warehouse_id] $warehouse_name from Warehouse Table.";
    $log['page'] = 'Warehouse';
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
                        case 'I': echo 'Add Warehouse'; break;
                        case 'E': echo 'Edit Warehouse'; break;
                        default: echo 'View Warehouse';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="warehouse_name_lbl" for="warehouse_name">Warehouse Name</label>
                <input class="form-control" type="text" name="warehouse_name" id="warehouse_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="warehouse_remark_lbl" for="warehouse_remark">Warehouse Remark</label>
                <textarea class="form-control" name="warehouse_remark" id="warehouse_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addWarehouse">Add Warehouse</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updWarehouse">Edit Warehouse</button>';
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
    echo '<script>confirmationDialog("","","Warehouse","","warehouse_table.php","'.$act.'");</script>';
}
?>
</body>
</html>