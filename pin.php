<?php
include './include/common.php';
include './include/connection.php';
include "header.php";

$pin_id = input('id');
$act = input('act');

// to display data to input
if($pin_id)
{
    $query = "SELECT * FROM ".PIN." WHERE id = '".$pin_id."'";
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
        case 'addPin': case 'updPin':
            $pin_name = post('pin_name');
            $pin_remark = post('pin_remark');

            if($pin_name)
            {
                if($action == 'addPin')
                {
                    try
                    {
                        $query = "INSERT INTO ".PIN." (name, remark) VALUES ('".$pin_name."', '".$pin_remark."')";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
                else
                {
                    try
                    {
                        $query = "UPDATE ".PIN." SET name ='".$pin_name."', remark ='".$pin_remark."' WHERE id = '".$pin_id."'";
                        mysqli_query($connect, $query);
                        $_SESSION['tempValConfirmBox'] = true;
                    } catch(Exception $e) {
                        echo 'Message: ' . $e->getMessage();
                    }
                }
            }
            else $pinnameErr = "Pin name cannot be empty.";
            break;
        case 'back':
            header('Location: pin_table.php');
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
            $query = "DELETE FROM ".PIN." WHERE id = ".$id;
            mysqli_query($connect, $query);
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<?php /* include "header.php"; */ ?>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/pin.css">
</head>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        <form id="pinForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>
                    Add Pin
                </h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_name_lbl" for="pin_name">Pin Name</label>
                <input class="form-control" type="text" name="pin_name" id="pin_name" value="<?php if(isset($dataExisted)) echo $row['name'] ?>" <?php if($act == '') echo 'readonly' ?>>
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($pinnameErr)) echo $pinnameErr; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_remark_lbl" for="pin_remark">Pin Remark</label>
                <textarea class="form-control" name="pin_remark" id="pin_remark" rows="3" <?php if($act == '') echo 'readonly' ?>><?php if(isset($dataExisted)) echo $row['remark'] ?></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
            <?php
                switch($act)
                {
                    case 'I':
                        echo '<button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="addPin">Add Pin</button>';
                        break;
                    case 'E':
                        echo '<button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updPin">Edit Pin</button>';
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
    echo '<script>confirmationDialog("","","Pin","","pin_table.php","'.$act.'");</script>';
}
?>
</body>
</html>