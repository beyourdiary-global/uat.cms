<?php
$pageTitle = "Socso Category";
include 'menuHeader.php';

$socso_cath_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/socso_category_table.php';

// to display data to input
if($socso_cath_id)
{
    $rst = getData('*',"id = '$socso_cath_id'",SOCSO_CATH,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } 
}

if(!($socso_cath_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");
 
if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addsocso_cath': case 'updsocso_cath':
            $socso_cath_name = postSpaceFilter('socso_cath_name');
            $socso_cath_remark = postSpaceFilter('socso_cath_remark');

            if($socso_cath_name)
            {
                if($action == 'addsocso_cath')
                {
                    try
                    {
                        $query = "INSERT INTO ".SOCSO_CATH."(name,remark,create_by,create_date,create_time) VALUES ('$socso_cath_name','$socso_cath_remark','".USER_ID."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($socso_cath_name != '')
                            array_push($newvalarr, $socso_cath_name);

                        if($socso_cath_remark != '')
                            array_push($newvalarr, $socso_cath_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$socso_cath_name</b> into <b><i>$pageTitle Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = SOCSO_CATH;
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
                        $rst = getData('*',"id = '$socso_cath_id'",SOCSO_CATH,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $socso_cath_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $socso_cath_name);
                        }

                        if($row['remark'] != $socso_cath_remark)
                        {
                            if($row['remark'] == '')
                                $old_remark = 'Empty_Value';
                            else $old_remark = $row['remark'];

                            array_push($oldvalarr, $old_remark);

                            if($socso_cath_remark == '')
                                $new_remark = 'Empty_Value';
                            else $new_remark = $socso_cath_remark;
                            
                            array_push($chgvalarr, $new_remark);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {   
                            // edit
                            $query = "UPDATE ".SOCSO_CATH." SET name ='$socso_cath_name', remark ='$socso_cath_remark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$socso_cath_id'";
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
                            $log['query_table'] = SOCSO_CATH;
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
            else $err = "$pageTitle name cannot be empty.";
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
            $rst = getData('*',"id = '$id'",SOCSO_CATH,$connect);
            $row = $rst->fetch_assoc();

            $socso_cath_id = $row['id'];
            $socso_cath_name = $row['name'];

            //SET the record status to 'D'
            deleteRecord(SOCSO_CATH,$id,$socso_cath_name,$connect,$cdate,$ctime,$pageTitle);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($socso_cath_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $socso_cath_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$socso_cath_name</b> from <b><i>$pageTitle Table</i></b>.";
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

<div id="socso_cath_FormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="socso_cath_Form" method="post" action="">
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
                <label class="form-label" id="dept_name_lbl" for="socso_cath_name"><?php echo $pageTitle ?> Name</label>
                <input class="form-control" type="text" name="socso_cath_name" id="socso_cath_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="dept_remark_lbl" for="socso_cath_remark"><?php echo $pageTitle ?> Remark</label>
                <textarea class="form-control" name="socso_cath_remark" id="socso_cath_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addsocso_cath">Add '.$pageTitle.' </button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updsocso_cath">Edit '.$pageTitle.' </button>';
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
centerAlignment("socso_cath_FormContainer");
</script>
</body>
</html>