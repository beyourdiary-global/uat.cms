<?php
$pageTitle = "Warehouse";
include 'menuHeader.php';

$warehouse_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/warehouse_table.php';

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
            $warehouse_name = postSpaceFilter('warehouse_name');
            $warehouse_remark = postSpaceFilter('warehouse_remark');

            if($warehouse_name)
            {
                if($action == 'addWarehouse')
                {
                    try
                    {
                        $query = "INSERT INTO ".WHSE."(name,remark,create_by,create_date,create_time) VALUES ('$warehouse_name','$warehouse_remark','".USER_ID."',curdate(),curtime())";
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
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$warehouse_name</b> into <b><i>Warehouse Table</i></b>.";
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

                        // check value
                        if($row['name'] != $warehouse_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $warehouse_name);
                        }

                        if($row['remark'] != $warehouse_remark)
                        {
                            if($row['remark'] == '')
                                $old_remark = 'Empty_Value';
                            else $old_remark = $row['remark'];

                            array_push($oldvalarr, $old_remark);

                            if($warehouse_remark == '')
                                $new_remark = 'Empty_Value';
                            else $new_remark = $warehouse_remark;
                            
                            array_push($chgvalarr, $new_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {    
                            // edit
                            $query = "UPDATE ".WHSE." SET name ='$warehouse_name', remark ='$warehouse_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$warehouse_id'";
                            mysqli_query($connect, $query);
                            
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
                            $log['act_msg'] .= " from <b><i>Warehouse Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = WHSE;
                            $log['page'] = 'Warehouse';
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

            //SET the record status to 'D'
            deleteRecord(WHSE,$id,$warehouse_name,$connect,$cdate,$ctime,$pageTitle);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($warehouse_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $warehouse_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$warehouse_name</b> from <b><i>Warehouse Table</i></b>.";
    $log['page'] = 'Warehouse';
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
    <p><a href="<?= $redirect_page ?>">Warehouse</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
    switch($act)
    {
        case 'I': echo 'Add Warehouse'; break;
        case 'E': echo 'Edit Warehouse'; break;
        default: echo 'View Warehouse';
    }
    ?></p>
</div>

<div id="whseFormContainer" class="container d-flex justify-content-center">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 formWidthAdjust">
        <form id="whseForm" method="post" action="">
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
                <input class="form-control" type="text" name="warehouse_name" id="warehouse_name" value="<?php if(isset($dataExisted) && isset($row['name'])) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="warehouse_remark_lbl" for="warehouse_remark">Warehouse Remark</label>
                <textarea class="form-control" name="warehouse_remark" id="warehouse_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addWarehouse">Add Warehouse</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updWarehouse">Edit Warehouse</button>';
                        break;
                }
            ?>
                <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="back">Back</button>
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
    echo '<script>confirmationDialog("","","Warehouse","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
<script>
/**
  oufei 20231014
  common.fun.js
  function(id)
  to resize form with "centered" class
*/
centerAlignment("whseFormContainer");
</script>
</body>
</html>