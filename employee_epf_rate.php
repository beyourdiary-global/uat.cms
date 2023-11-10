<?php
$pageTitle = "Employee EPF Rate";
include 'menuHeader.php';

$employee_epf_rate_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/employee_epf_rate_table.php';

// to display data to input
if($employee_epf_rate_id)
{
    $rst = getData('*',"id = '$employee_epf_rate_id'",EMPLOYEE_EPF,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } 
}

if(!($employee_epf_rate_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addemployee_rate_epf': case 'updemployee_rate_epf':
            $employee_epf_rate = postSpaceFilter('employee_epf_rate');
            $employee_epf_rate_remark = postSpaceFilter('employee_epf_rate_remark');

            if($employee_epf_rate)
            {
                if($action == 'addemployee_rate_epf')
                {
                    try
                    {
                        $query = "INSERT INTO ".EMPLOYEE_EPF."(epf_rate,remark,create_by,create_date,create_time) VALUES ('$employee_epf_rate','$employee_epf_rate_remark','".USER_ID."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($employee_epf_rate != '')
                            array_push($newvalarr, $employee_epf_rate);

                        if($employee_epf_rate_remark != '')
                            array_push($newvalarr, $employee_epf_rate_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$employee_epf_rate</b> into <b><i>$pageTitle Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = EMPLOYEE_EPF;
                        $log['page'] = $pageTitle;
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
                        $rst = getData('*',"id = '$employee_epf_rate_id'",EMPLOYEE_EPF,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['epf_rate'] != $employee_epf_rate)
                        {
                            array_push($oldvalarr, $row['epf_rate']);
                            array_push($chgvalarr, $employee_epf_rate);
                        }

                        if($row['remark'] != $employee_epf_rate_remark)
                        {
                            if($row['remark'] == '')
                                $old_remark = 'Empty_Value';
                            else $old_remark = $row['remark'];

                            array_push($oldvalarr, $old_remark);

                            if($employee_epf_rate_remark == '')
                                $new_remark = 'Empty_Value';
                            else $new_remark = $employee_epf_rate_remark;
                            
                            array_push($chgvalarr, $new_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {   
                            // edit
                            $query = "UPDATE ".EMPLOYEE_EPF." SET epf_rate ='$employee_epf_rate', remark ='$employee_epf_rate_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$employee_epf_rate_id'";
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
                            $log['act_msg'] .= " from <b><i>$pageTitle Table</i></b>.";

                            $log['query_rec'] = $query;
                            $log['query_table'] = EMPLOYEE_EPF;
                            $log['page'] = $pageTitle;
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
            else $err = "$pageTitle Epf Rate cannot be empty.";
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
            $rst = getData('*',"id = '$id'",EMPLOYEE_EPF,$connect);
            $row = $rst->fetch_assoc();

            $employee_epf_rate_id = $row['id'];
            $employee_epf_rate = $row['epf_rate'];

            //SET the record status to 'D'
            deleteRecord(EMPLOYEE_EPF,$id,$employee_epf_rate,$connect,$cdate,$ctime,$pageTitle);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($employee_epf_rate_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $employee_epf_rate = isset($dataExisted) ? $row['epf_rate'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$employee_epf_rate</b> from <b><i>$pageTitle Table</i></b>.";
    $log['page'] = $pageTitle;
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
    <p><a href="<?= $redirect_page ?>"><?php echo $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php
    switch($act)
    {
        case 'I': echo 'Add '.$pageTitle ; break;
        case 'E': echo 'Edit '.$pageTitle ; break;
        default: echo 'View '.$pageTitle ;
    }
    ?></p>
</div>

<div id="employee_epf_rate_FormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="employee_epf_rate_Form" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add '.$pageTitle ; break;
                        case 'E': echo 'Edit '.$pageTitle ; break;
                        default: echo 'View '.$pageTitle ;
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="employee_epf_rate_name_lbl" for="employee_epf_rate"><?php echo $pageTitle ?></label>
                <input class="form-control" type="number" step="any" name="employee_epf_rate" id="employee_epf_rate" value="<?php if(isset($dataExisted) && isset($row['epf_rate'])) echo $row['epf_rate'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="employee_epf_rate_remark_lbl" for="employee_epf_rate_remark"><?php echo $pageTitle ?> Remark</label>
                <textarea class="form-control" name="employee_epf_rate_remark" id="employee_epf_rate_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addemployee_rate_epf">Add '.$pageTitle.' </button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updemployee_rate_epf">Edit '.$pageTitle.' </button>';
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
    echo '<script>confirmationDialog("","","'.$pageTitle.'","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
<script>
/**
  oufei 20231014
  common.fun.js
  function(id)
  to resize form with "centered" class
*/
centerAlignment("employee_epf_rate_FormContainer");
</script>
</body>
</html>