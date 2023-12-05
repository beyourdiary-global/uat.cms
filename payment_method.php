<?php
$pageTitle = "Payment Method";
include 'menuHeader.php';

$payment_method_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/payment_method_table.php';

// to display data to input
if($payment_method_id)
{
    $rst = getData('*',"id = '$payment_method_id'",PAY_METH,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } 
}

if(!($payment_method_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addpayment_method': case 'updpayment_method':
            $payment_method_name = postSpaceFilter('payment_method_name');
            $payment_method_remark = postSpaceFilter('payment_method_remark');
            $payment_method_installment_period = postSpaceFilter('payment_method_installment_period');
            $payment_method_service_rate = postSpaceFilter('payment_method_service_rate');

            if (!$payment_method_name){
                $err = "Payment method name cannot be empty.";
                break;
            }
            else if(isDuplicateRecord("name", $payment_method_name, PAY_METH, $connect, $payment_method_id)){
                $err = "Duplicate record found for payment method name.";
                break;
            }
            else if($action == 'addpayment_method'){
                   
                try
                {
                    $query = "INSERT INTO ".PAY_METH."(name,installment_period,service_rate,remark,create_by,create_date,create_time) VALUES ('$payment_method_name','$payment_method_installment_period ','$payment_method_service_rate ','$payment_method_remark','".USER_ID."',curdate(),curtime())";
                    mysqli_query($connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;

                    $newvalarr = array();

                    // check value
                    if($payment_method_name != '')
                        array_push($newvalarr, $payment_method_name);

                    if($payment_method_installment_period != '')
                        array_push($newvalarr, $payment_method_installment_period);

                    if($payment_method_service_rate != '')
                        array_push($newvalarr, $payment_method_service_rate);

                    if($payment_method_remark != '')
                        array_push($newvalarr, $payment_method_remark);

                    $newval = implode(",",$newvalarr);

                    // audit log
                    $log = array();
                    $log['log_act'] = 'add';
                    $log['cdate'] = $cdate;
                    $log['ctime'] = $ctime;
                    $log['uid'] = $log['cby'] = USER_ID;
                    $log['act_msg'] = USER_NAME . " added <b>$payment_method_name</b> into <b><i>$pageTitle Table</i></b>.";
                    $log['query_rec'] = $query;
                    $log['query_table'] = PAY_METH;
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
                    $rst = getData('*',"id = '$payment_method_id'",PAY_METH,$connect);
                    $row = $rst->fetch_assoc();
                    $oldvalarr = $chgvalarr = array();

                    // check value
                    if($row['name'] != $payment_method_name)
                    {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $payment_method_name);
                    }

                    // check value
                    if($row['installment_period'] != $payment_method_installment_period)
                    {
                        array_push($oldvalarr, $row['installment_period']);
                        array_push($chgvalarr, $payment_method_installment_period);
                    }

                    // check value
                    if($row['service_rate'] != $payment_method_service_rate)
                    {
                        array_push($oldvalarr, $row['service_rate']);
                        array_push($chgvalarr, $payment_method_service_rate);
                    }

                    if($row['remark'] != $payment_method_remark)
                    {
                        if($row['remark'] == '')
                            $old_remark = 'Empty_Value';
                        else $old_remark = $row['remark'];

                        array_push($oldvalarr, $old_remark);

                        if($payment_method_remark == '')
                            $new_remark = 'Empty_Value';
                        else $new_remark = $payment_method_remark;
                        
                        array_push($chgvalarr, $new_remark);
                    }

                    // convert into string
                    $oldval = implode(",",$oldvalarr);
                    $chgval = implode(",",$chgvalarr);

                    $_SESSION['tempValConfirmBox'] = true;
                    if($oldval != '' && $chgval != '')
                    {   
                        // edit
                        $query = "UPDATE ".PAY_METH." SET name ='$payment_method_name', installment_period = '$payment_method_installment_period', service_rate = '$payment_method_service_rate' ,remark ='$payment_method_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$payment_method_id'";
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
                        $log['query_table'] = PAY_METH;
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
            $rst = getData('*',"id = '$id'",PAY_METH,$connect);
            $row = $rst->fetch_assoc();

            $payment_method_id = $row['id'];
            $payment_method_name = $row['name'];

            //SET the record status to 'D'
            deleteRecord(PAY_METH,$id,$payment_method_name,$connect,$cdate,$ctime,$pageTitle);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($payment_method_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $payment_method_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$payment_method_name</b> from <b><i>$pageTitle Table</i></b>.";
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

<div id="payment_method_FormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="payment_method_Form" method="post" action="">
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
                <label class="form-label" id="payment_method_name_lbl" for="payment_method_name"><?php echo $pageTitle ?> Name</label>
                <input class="form-control" type="text" name="payment_method_name" id="payment_method_name" value="<?php if(isset($dataExisted) && isset($row['name'])) echo $row['name'] ?>" <?php if($act == '') echo 'readonly'  ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <div class="row">
                    <div class="col-sm">
                        <label class="form-label" id="payment_method_installment_period_lbl" for="payment_method_installment_period">Installment Period</label>
                        <input class="form-control" type="number" name="payment_method_installment_period" id="payment_method_installment_period" step="any" value="<?php if (isset($dataExisted) && isset($row['installment_period'])) echo $row['installment_period'] ?>" <?php if ($act == '') echo 'readonly' ?> style="height: 40px;">
                    </div>
                    <div class="col-sm">
                        <label class=" form-label" id="payment_method_service_rate_lbl" for="payment_service _rate">Service Rate</label><br>
                        <div class="col d-flex justify-content-start align-items-center">
                            <input type="number" name="payment_method_service_rate" id="payment_method_service_rate" step="any" <?php if ($act == '') echo 'readonly ' ?> value="<?php if (isset($dataExisted) && isset($row['service_rate'])) echo $row['service_rate'] ?>" class="form-control"  style="height: 40px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="payment_method_remark_lbl" for="payment_method_remark"><?php echo $pageTitle ?> Remark</label>
                <textarea class="form-control" name="payment_method_remark" id="payment_method_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addpayment_method">Add '.$pageTitle.' </button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updpayment_method">Edit '.$pageTitle.' </button>';
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
centerAlignment("payment_method_FormContainer");
</script>
</body>
</html>