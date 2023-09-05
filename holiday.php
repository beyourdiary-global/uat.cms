<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$holiday_id = input('id');
$act = input('act');

// to display data to input
if($holiday_id)
{
    $query = "SELECT * FROM ".HOLIDAY." WHERE id = '".$holiday_id."'";
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
        case 'addHoliday': case 'updHoliday':
            $holiday_name = post('holiday_name');
            $holiday_date = post('holiday_date');

            if(!($holiday_name))
                $err = "Holiday name cannot be empty.";
            
            if(!($holiday_date))
                $err2 = "Holiday date cannot be empty.";

            if($holiday_name && $holiday_date)
            {
                if($action == 'addHoliday')
                {
                    try
                    {
                        $query = "INSERT INTO ".HOLIDAY."(name,date,create_by,create_date,create_time) VALUES ('$holiday_name','$holiday_date','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($holiday_name != '')
                            array_push($newvalarr, $holiday_name);

                        if($holiday_date != '')
                            array_push($newvalarr, $holiday_date);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $holiday_name into Holiday Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = HOLIDAY;
                        $log['page'] = 'Holiday';
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
                        $query = "SELECT * FROM ".HOLIDAY." WHERE id = '$holiday_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".HOLIDAY." SET name ='$holiday_name', date ='$holiday_date', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$holiday_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['name'] != $holiday_name)
                        {
                            array_push($oldvalarr, $row['name']);
                            array_push($chgvalarr, $holiday_name);
                        }

                        if($row['date'] != $holiday_date)
                        {
                            array_push($oldvalarr, $row['date']);
                            array_push($chgvalarr, $holiday_date);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$holiday_id] $holiday_name from Holiday Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = HOLIDAY;
                        $log['page'] = 'Holiday';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            break;
        case 'back':
            header('Location: holiday_table.php');
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
            $query = "SELECT * FROM ".HOLIDAY." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $holiday_id = $row['id'];
            $holiday_name = $row['name'];

            $query = "DELETE FROM ".HOLIDAY." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$holiday_id] $holiday_name from Holiday Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = HOLIDAY;
            $log['page'] = 'Holiday';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($holiday_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $holiday_name = isset($dataExisted) ? $row['name'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$holiday_id] $holiday_name from Holiday Table.";
    $log['page'] = 'Holiday';
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
    <div class="col-6 col-md-6">
        <form id="desigForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Holiday'; break;
                        case 'E': echo 'Edit Holiday'; break;
                        default: echo 'View Holiday';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="holiday_name_lbl" for="holiday_name">Holiday Name</label>
                <input class="form-control" type="text" name="holiday_name" id="holiday_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="holiday_date_lbl" for="holiday_date">Holiday Date</label>
                <input class="form-control" type="date" name="holiday_date" id="holiday_date" value="<?php if(isset($dataExisted)) echo $row['date'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err2)) echo $err2; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addHoliday">Add Holiday</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updHoliday">Edit Holiday</button>';
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
    echo '<script>confirmationDialog("","","Holiday","","holiday_table.php","'.$act.'");</script>';
}
?>
</body>
</html>