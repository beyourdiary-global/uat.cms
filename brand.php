<?php
$pageTitle = "Brand";
include 'menuHeader.php';

$brand_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/brand_table.php';
$tblname = BRAND;

// to display data to input
if($brand_id)
{
    $rst = getData('*',"id = '$brand_id'",$tblname,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    }
}

if(!($brand_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addBrand': case 'updBrand':
            $brand_name = postSpaceFilter('brand_name');
            $brand_remark = postSpaceFilter('brand_remark');

            if($brand_name)
            {
                if($action == 'addBrand')
                {
                    try
                    {
                        $query = "INSERT INTO ".$tblname."(name,remark,create_by,create_date,create_time) VALUES ('$brand_name','$brand_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        generateDBData($tblname, $connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($brand_name != '')
                            array_push($newvalarr, $brand_name);

                        if($brand_remark != '')
                            array_push($newvalarr, $brand_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added <b>$brand_name</b> into <b><i>Brand Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = $tblname;
                        $log['page'] = 'Brand';
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
                        $rst = getData('*',"id = '$brand_id'",$tblname,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $brand_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $brand_name);
                        }

                        if($row['remark'] != $brand_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $brand_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {
                             // edit
                            $query = "UPDATE ".$tblname." SET name ='$brand_name', remark ='$brand_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '$brand_id'";
                            mysqli_query($connect, $query);
                            generateDBData($tblname, $connect);

                            // audit log
                            $log = array();
                            $log['log_act'] = 'edit';
                            $log['cdate'] = $cdate;
                            $log['ctime'] = $ctime;
                            $log['uid'] = $log['cby'] = $_SESSION['userid'];

                            $log['act_msg'] = $_SESSION['user_name'] . " edited the data ";
                            for($i=0; $i<sizeof($oldvalarr); $i++)
                            {
                                if($i==0)
                                    $log['act_msg'] .= " from <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                                else
                                    $log['act_msg'] .= ", <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                            }
                            $log['act_msg'] .= "  under <b><i>Brand Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = $tblname;
                            $log['page'] = 'Brand';
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
            else $err = "Brand name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",$tblname,$connect);
            $row = $rst->fetch_assoc();

            $brand_id = $row['id'];
            $brand_name = $row['name'];

            $query = "DELETE FROM ".$tblname." WHERE id = ".$id;
            mysqli_query($connect, $query);
            generateDBData($tblname, $connect);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data <b>$brand_name</b> under <b><i>Brand Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = $tblname;
            $log['page'] = 'Brand';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($brand_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $brand_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data <b>$brand_name</b> from <b><i>Brand Table</i></b>.";
    $log['page'] = 'Brand';
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
    <div class="row">
        <p><a href="<?= $redirect_page ?>">Brand</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
        switch($act)
        {
            case 'I': echo 'Add Brand'; break;
            case 'E': echo 'Edit Brand'; break;
            default: echo 'View Brand';
        }
        ?></p>
    </div>
</div>

<div id="brandFormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="brandForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Brand'; break;
                        case 'E': echo 'Edit Brand'; break;
                        default: echo 'View Brand';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="brand_name_lbl" for="brand_name">Brand Name</label>
                <input class="form-control" type="text" name="brand_name" id="brand_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="brand_remark_lbl" for="brand_remark">Brand Remark</label>
                <textarea class="form-control" name="brand_remark" id="brand_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addBrand">Add Brand</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updBrand">Edit Brand</button>';
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
    echo '<script>confirmationDialog("","","Brand","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
<script>
centerAlignment("brandFormContainer");
</script>
</body>
</html>