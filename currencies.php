<?php
include 'menuHeader.php';

$currencies_id = input('id');
$act = input('act');

// to display data to input
if($currencies_id)
{
    $query = "SELECT * FROM ".CURRENCIES." WHERE id = '".$currencies_id."'";
    $result = mysqli_query($connect, $query);

    if(mysqli_num_rows($result) == 1)
    {
        $dataExisted = 1;
        $row = $result->fetch_assoc();
    }
}

if(!($currencies_id) && !($act))
    echo("<script>location.href = 'currencies_table.php';</script>");

// to list out the currency unit for selection 
$cur_list_qry = "SELECT * FROM ".CUR_UNIT;
$cur_list_result = $connect->query($cur_list_qry);

// currency unit
$cur_unit_arr = array();
if($cur_list_result->num_rows >= 1)
{
    while($row2 = $cur_list_result->fetch_assoc())
    {
        $x = $row2['id'];
        $y = $row2['unit'];
        $cur_unit_arr[$x] = $y;
    }
    
}

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'addCurrencies': case 'updCurrencies':
            $dflt_cur_unit = post('dflt_cur_unit');
            $dflt_cur_unit = explode(":", $dflt_cur_unit);
            $exchg_cur_rate = post('exchg_cur_rate');
            $exchg_cur_unit = post('exchg_cur_unit');
            $exchg_cur_unit = explode(":", $exchg_cur_unit);
            $currencies_remark = post('currencies_remark');

            if($exchg_cur_rate)
            {
                if($action == 'addCurrencies')
                {
                    try
                    {
                        $query = "INSERT INTO ".CURRENCIES."(default_currency_unit,exchange_currency_rate,exchange_currency_unit,remark,create_by,create_date,create_time) VALUES ('$dflt_cur_unit[0]','$exchg_cur_rate','$exchg_cur_unit[0]','$currencies_remark','".$_SESSION['userid']."',curdate(),curtime())";
                        mysqli_query($connect, $query);
                        $last_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        $newvalarr = array();

                        // check value
                        if($dflt_cur_unit != '')
                            array_push($newvalarr, $dflt_cur_unit[0]);

                        if($exchg_cur_rate != '')
                            array_push($newvalarr, $exchg_cur_rate);

                        if($exchg_cur_unit != '')
                            array_push($newvalarr, $exchg_cur_unit[0]);

                        if($currencies_remark != '')
                            array_push($newvalarr, $currencies_remark);

                        $newval = implode(",",$newvalarr);

                        // audit log
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = $_SESSION['userid'];
                        $log['act_msg'] = $_SESSION['user_name'] . " added [id=$last_id] $dflt_cur_unit[1] -> $exchg_cur_unit[1] into Currencies Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = CURRENCIES;
                        $log['page'] = 'Currencies';
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
                        $query = "SELECT * FROM ".CURRENCIES." WHERE id = '$currencies_id'";
                        $result = mysqli_query($connect, $query);
                        $row = $result->fetch_assoc();
                        $oldvalarr = $chgvalarr = array();

                        // edit
                        $query = "UPDATE ".CURRENCIES." SET default_currency_unit ='$dflt_cur_unit[0]', exchange_currency_rate ='$exchg_cur_rate', exchange_currency_unit ='$exchg_cur_unit[0]', remark ='$currencies_remark', update_date = curdate(), update_time = curtime(), update_by ='".$_SESSION['userid']."' WHERE id = '".$currencies_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;

                        // check value
                        if($row['default_currency_unit'] != $dflt_cur_unit)
                        {
                            array_push($oldvalarr, $row['default_currency_unit']);
                            array_push($chgvalarr, $dflt_cur_unit[0]);
                        }

                        if($row['exchange_currency_rate'] != $exchg_cur_rate)
                        {
                            array_push($oldvalarr, $row['exchange_currency_rate']);
                            array_push($chgvalarr, $exchg_cur_rate);
                        }

                        if($row['exchange_currency_unit'] != $exchg_cur_unit)
                        {
                            array_push($oldvalarr, $row['exchange_currency_unit']);
                            array_push($chgvalarr, $exchg_cur_unit[0]);
                        }

                        if($row['remark'] != $currencies_remark)
                        {
                            array_push($oldvalarr, $row['remark']);
                            array_push($chgvalarr, $currencies_remark);
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
                        $log['act_msg'] = $_SESSION['user_name'] . " edited the data [id=$currencies_id] $dflt_cur_unit[1] -> $exchg_cur_unit[1] from Currencies Table.";
                        $log['query_rec'] = $query;
                        $log['query_table'] = CURRENCIES;
                        $log['page'] = 'Currencies';
                        $log['oldval'] = $oldval;
                        $log['changes'] = $chgval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $err = "Exchange Currency Rate cannot be empty.";
            break;
        case 'back':
            echo("<script>location.href = 'currencies_table.php';</script>");
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
            $query = "SELECT * FROM ".CURRENCIES." WHERE id = '".$id."'";
            $result = mysqli_query($connect, $query);
            $row = $result->fetch_assoc();

            $currencies_id = $row['id'];
            $dflt_cur_unit = $row['default_currency_unit'];
            $exchg_cur_unit = $row['exchange_currency_unit'];

            $query = "DELETE FROM ".CURRENCIES." WHERE id = ".$id;
            mysqli_query($connect, $query);

            // audit log
            $log = array();
            $log['log_act'] = 'delete';
            $log['cdate'] = $cdate;
            $log['ctime'] = $ctime;
            $log['uid'] = $log['cby'] = $_SESSION['userid'];
            $log['act_msg'] = $_SESSION['user_name'] . " deleted the data [id=$currencies_id] $cur_unit_arr[$dflt_cur_unit] -> $cur_unit_arr[$exchg_cur_unit] from Currencies Table.";
            $log['query_rec'] = $query;
            $log['query_table'] = CURRENCIES;
            $log['page'] = 'Currencies';
            $log['connect'] = $connect;
            audit_log($log);

            $_SESSION['delChk'] = 1;
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if(($currencies_id != '') && ($act == '') && (isset($_SESSION['userid'])) && ($_SESSION['viewChk'] != 1) && ($_SESSION['delChk'] != 1))
{
    $dflt_cur_unit = isset($dataExisted) ? $row['default_currency_unit'] : '';
    $exchg_cur_unit = isset($dataExisted) ? $row['exchange_currency_unit'] : '';
    $_SESSION['viewChk'] = 1;

    // audit log
    $log = array();
    $log['log_act'] = 'view';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = $_SESSION['userid'];
    $log['act_msg'] = $_SESSION['user_name'] . " viewed the data [id=$currencies_id] $cur_unit_arr[$dflt_cur_unit] -> $cur_unit_arr[$exchg_cur_unit] from Currencies Table.";
    $log['page'] = 'Currencies';
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
                        case 'I': echo 'Add Currencies'; break;
                        case 'E': echo 'Edit Currencies'; break;
                        default: echo 'View Currencies';
                    }
                    ?>
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label form_lbl" id="dflt_cur_unit_lbl" for="dflt_cur_unit">Default Currency Unit</label>
                <select class="form-select" id="dflt_cur_unit" name="dflt_cur_unit" <?php if($act == '') echo 'disabled' ?>>
                    <?php
                        if($cur_list_result->num_rows >= 1)
                        {
                            $cur_list_result->data_seek(0);
                            while($row2 = $cur_list_result->fetch_assoc())
                            {
                                $selected = "";
                                if(isset($dataExisted))
                                    $selected = $row['dflt_currency_unit'] == $row2['id'] ? " selected" : "";

                                echo "<option value=\"".$row2['id'].":".$row2['unit']."\"$selected>".$row2['unit']."</option>";
                            }
                        } else {
                            echo "<option value=\"0\">None</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label form_lbl" id="exchg_cur_rate_lbl" for="exchg_cur_rate">Exchange Currency Rate</label>
                <input class="form-control" type="text" name="exchg_cur_rate" id="exchg_cur_rate" value="<?php if(isset($dataExisted)) echo $row['exchange_currency_rate'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($err)) echo $err; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label form_lbl" id="dflt_cur_unit_lbl" for="exchg_cur_unit">Exchange Currency Unit</label>
                <select class="form-select" id="exchg_cur_unit" name="exchg_cur_unit" <?php if($act == '') echo 'disabled' ?>>
                    <?php
                        if($cur_list_result->num_rows >= 1)
                        {
                            $cur_list_result->data_seek(0);
                            while($row2 = $cur_list_result->fetch_assoc())
                            {
                                $selected = "";
                                if(isset($dataExisted))
                                    $selected = $row['exchange_currency_unit'] == $row2['id'] ? " selected" : "";

                                echo "<option value=\"".$row2['id'].":".$row2['unit']."\"$selected>".$row2['unit']."</option>";
                            }
                        } else {
                            echo "<option value=\"0\">None</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label form_lbl" id="currencies_remark_lbl" for="currencies_remark">Currency Unit Remark</label>
                <textarea class="form-control" name="currencies_remark" id="currencies_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addCurrencies">Add Currencies</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updCurrencies">Edit Currencies</button>';
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
    echo '<script>confirmationDialog("","","Currencies","","currencies_table.php","'.$act.'");</script>';
}
?>
</body>
</html>