<?php
$pageTitle = "Race";
include 'menuHeader.php';

$race_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/race_table.php';

// to display data to input
if($race_id)
{
    $rst = getData('*',"id = '$race_id'",RACE,$connect);

    if($rst != false)
    {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } 
}

if(!($race_id) && !($act))
    echo("<script>location.href = '$redirect_page';</script>");

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addRace': case 'updRace':
            $race_name = postSpaceFilter('race_name');

            if($race_name)
            {
                if($action == 'addRace')
                {
                    try
                    {
                        $query = "INSERT INTO ".RACE."(name,create_by,create_date,create_time) VALUES ('$race_name','".USER_ID."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($race_name != '')
                            array_push($newvalarr, $race_name);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added <b>$race_name</b> into <b><i>$pageTitle Table</i></b>.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = RACE;
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
                        $rst = getData('*',"id = '$race_id'",RACE,$connect);
                        $row = $rst->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // check value
                        if($row['name'] != $race_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $race_name);
                        }

                        // convert into string
                        $oldval = implode(",",$oldvalarr);
                        $chgval = implode(",",$chgvalarr);

                        $_SESSION['tempValConfirmBox'] = true;
                        if($oldval != '' && $chgval != '')
                        {   
                            // edit
                            $query = "UPDATE ".RACE." SET name ='$race_name', update_date = curdate(), update_time = curtime(), update_by ='".USER_ID."' WHERE id = '$race_id'";
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
                            $log['query_table'] = RACE;
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
            $rst = getData('*',"id = '$id'",RACE,$connect);
            $row = $rst->fetch_assoc();

            $race_id = $row['id'];
            $race_name = $row['name'];

            //SET the record status to 'D'
            deleteRecord(RACE,$id,$race_name,$connect,$cdate,$ctime,$pageTitle);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($race_id != '') && ($act == '') && (USER_ID != '') && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $race_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " viewed the data <b>$race_name</b> from <b><i>$pageTitle Table</i></b>.";
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

<div id="raceFormContainer" class="container d-flex justify-content-center">
    <div class="col-6 col-md-6 formWidthAdjust">
        <form id="raceForm" method="post" action="">
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
                <label class="form-label" id="dept_name_lbl" for="race_name"><?php echo $pageTitle ?> Name</label>
                <input class="form-control" type="text" name="race_name" id="race_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                </div>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="addRace">Add '.$pageTitle.' </button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="updRace">Edit '.$pageTitle.' </button>';
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
centerAlignment("raceFormContainer");
</script>
</body>
</html>