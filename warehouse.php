<?php
$pageTitle = "Warehouse";
include 'menuHeader.php';

$warehouse_id = input('id');
$act = input('act');
$redirect_page = 'warehouse_table.php';

// to display data to input
if($warehouse_id)
{
    $rst = getData('*',"id = '$warehouse_id'",WHSE,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($warehouse_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

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
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$warehouse_name</b> into <b><i>Warehouse Table</i></b>.";
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
                        $rst = getData('*',"id = '$warehouse_id'",WHSE,$connect);
                        $row = $rst->fetch_assoc();
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
                            $log['act_msg'] .= " from <b><i>Warehouse Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = WHSE;
                            $log['page'] = 'Warehouse';
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
            else $err = "Warehouse name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",WHSE,$connect);
            $row = $rst->fetch_assoc();

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
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$warehouse_name</b> from <b><i>Warehouse Table</i></b>.";
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
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$warehouse_name</b> from <b><i>Warehouse Table</i></b>.";
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
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
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
    echo '<script>confirmationDialog("","","Warehouse","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
</html>