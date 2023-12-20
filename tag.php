<?php
$pageTitle = "Tag";
include 'menuHeader.php';

$tagID = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/tagTable.php';

// to display data to input
if($tagID)
{
    $rst = getData('*',"id = '$tagID'",'',TAG,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } 
}

if(!($tagID) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");
 
if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addsocso_cath': case 'updsocso_cath':
            $tagName = postSpaceFilter('tagName');
            $tagRemark = postSpaceFilter('tagRemark');

            if (!$tagName){
                $err = "Tag name cannot be empty.";
                break;
            }
            else if(isDuplicateRecord("name", $tagName, TAG, $connect, $tagID)){
                $err = "Duplicate record found for tag name.";
                break;
            }
            else if($action == 'addsocso_cath') {
                    
                try
                {
                    $query = "INSERT INTO ".TAG."(name,remark,create_by,create_date,create_time) VALUES ('$tagName','$tagRemark','".USER_ID."',curdate(),curtime())";
                    mysqli_query($connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;

                    $newvalarr = array();

                    // check value
                    if($tagName != '')
                        array_push($newvalarr, $tagName);

                    if($tagRemark != '')
                        array_push($newvalarr, $tagRemark);

                    $newval = implode(",",$newvalarr);

                    // audit log
                    $log = array();
                    $log['log_act'] = 'add';
                    $log['cdate'] = $cdate;
                    $log['ctime'] = $ctime;
                    $log['uid'] = $log['cby'] = USER_ID;
                    $log['act_msg'] = USER_NAME . " added <b>$tagName</b> into <b><i>$pageTitle Table</i></b>.";
                    $log['query_rec'] = $query;
                    $log['query_table'] = TAG;
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
                    $rst = getData('*',"id = '$tagID'",'',TAG,$connect);
                    $row = $rst->fetch_assoc();
                    $oldvalarr = $chgvalarr = array();

                    // check value
                    if($row['name'] != $tagName)
                    {
                        array_push($oldvalarr, $row['name']);
                        array_push($chgvalarr, $tagName);
                    }

                    if($row['remark'] != $tagRemark)
                    {
                        if($row['remark'] == '')
                            $old_remark = 'Empty_Value';
                        else $old_remark = $row['remark'];

                        array_push($oldvalarr, $old_remark);

                        if($tagRemark == '')
                            $new_remark = 'Empty_Value';
                        else $new_remark = $tagRemark;
                        
                        array_push($chgvalarr, $new_remark);
                    }

                    // convert into string
                    $oldval = implode(",",$oldvalarr);
                    $chgval = implode(",",$chgvalarr);

                    $_SESSION['tempValConfirmBox'] = true;
                    if($oldval != '' && $chgval != '')
                    {   
                        // edit
                        $query = "UPDATE ".TAG." SET name ='$tagName', remark ='$tagRemark', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$tagID'";
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
                        $log['query_table'] = TAG;
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
            $rst = getData('*',"id = '$id'",'',TAG,$connect);
            $row = $rst->fetch_assoc();

            $tagID = $row['id'];
            $tagName = $row['name'];

            //SET the record status to 'D'
            deleteRecord(TAG,$id,$tagName,$connect,$cdate,$ctime,$pageTitle);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($tagID != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $tagName = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$tagName</b> from <b><i>$pageTitle Table</i></b>.";
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

<div id="tagFormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="tagForm" method="post" action="">
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
                <label class="form-label" id="tagNameLbl" for="tagName"><?php echo $pageTitle ?> Name</label>
                <input class="form-control" type="text" name="tagName" id="tagName" value="<?php if(isset($dataExisted) && isset($row['name'])) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="tagRemarkLbl" for="tagRemark"><?php echo $pageTitle ?> Remark</label>
                <textarea class="form-control" name="tagRemark" id="tagRemark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted) && isset($row['remark'])) echo $row['remark'] ?></textarea>
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
centerAlignment("tagFormContainer");
</script>
</body>
</html>