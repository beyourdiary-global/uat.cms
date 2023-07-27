<?php
include './include/common.php';
include './include/connection.php';

if(post('addBtn') == 'addPin')
{
    $pin_name = post('pin_name');
    $pin_remark = post('pin_remark');

    if($pin_name)
    {
        try
        {
            $query = "INSERT INTO ".PIN." (name, remark) VALUES ('".$pin_name."', '".$pin_remark."')";
        mysqli_query($connect, $query);
        } catch(Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
    else $pinnameErr = "Pin name cannot be empty.";
}
?>

<!DOCTYPE html>
<html>
<head>
<?php include "header.php"; ?>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/pin.css">
</head>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        <form id="addPinForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>Add Pin</h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_name_lbl" for="pin_name">Pin Name</label>
                <input class="form-control" type="text" name="pin_name" id="pin_name">
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($pinnameErr)) echo $pinnameErr; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="pin_remark_lbl" for="pin_remark">Pin Remark</label>
                <textarea class="form-control" name="pin_remark" id="pin_remark" rows="3"></textarea>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
                <button class="btn btn-lg btn-rounded btn-primary" name="addBtn" id="addBtn" value="addPin">Add Pin</button>
            </div>

            <div class="d-flex justify-content-center mt-4">
            </div>
        </form>
    </div>
</div>

</body>
</html>