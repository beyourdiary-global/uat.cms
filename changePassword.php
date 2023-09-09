<?php
include "./include/common.php";
include "./include/connection.php";

$sendEmail = '';

if(post('updBtn') == 'updpass')
{
    $id = $_SESSION['userid'];
    $token = input('token');
    $old_password = post('chgoldpass');
    $new_password = post('chgnewpass');
    $confirm_password = post('chgconfirmpass');

    if($email && $old_password && $new_password && $confirm_password)
    {
        if($new_password == $confirm_password)
        {
            $rst = getData('*',"id = '$id'",USR_USER,$connect);
            $row = $rst->fetch_assoc();

            if(mysqli_num_rows($result) == 1)
            {
                if($row['password'] == $old_password && $row['password_alt'] == md5($old_password))
                {
                    try
                    {
                        $query = "UPDATE ".USR_USER." SET password = '".$new_password."', password_alt = '".md5($new_password)."' WHERE id = '".$id."'";
                        mysqli_query($connect, $query);
                    } catch(Exception $e) {
                        $commonErr = $e->getMessage();
                    }
                }
                else $oldpassErr = 'Wrong old password entered, please try again.';
            }
            else $commonErr = 'No email existed in the system.';
        }
        else $newpassErr = $confirmpassErr = 'Password Not Match.';
    }
    else $commonErr = 'Field cannot be blank.';
}

if(post('updBtn') == 'rstpass')
{
    $email = input('email');
    $token = input('token');
    $new_password = post('rstnewpass');
    $confirm_password = post('rstconfirmpass');

    if($email && $token && $new_password && $confirm_password)
    {
        if($new_password == $confirm_password)
        {
            $rst = getData('*',"email = '$email'",USR_USER,$connect);
            $row = $rst->fetch_assoc();

            if(mysqli_num_rows($result) == 1)
            {
                if($row['fail_count'] == 4)
                {
                    try
                    {
                        $query = "UPDATE ".USR_USER." SET password = '".$new_password."', password_alt = '".md5($new_password)."' WHERE email = '".$email."'";
                        mysqli_query($connect, $query);
                        $sendEmail = 'rstSendEmail';
                    } catch(Exception $e) {
                        $commonErr = $e->getMessage();
                    }
                }
                else $commonErr = '';
            }
            else $commonErr = 'No email existed in the system.';
        }
        else $newpassErr = $confirmpassErr = 'Password Not Match.';
    }
    else $commonErr = 'Field cannot be blank.';
}

if($sendEmail == 'rstSendEmail')
{
    ob_start();
    $to = $email;
    $subject = 'Password has been reset';
    $message = 'Password has been successfully reset.';
    mail($to, $subject, $message_user);
    ob_get_clean();
}
?>

<!DOCTYPE html>
<html>
<head>
<?php include "header.php"; ?>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/changePassword.css">
</head>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
<?php if(input('token') && input('email')) { ?>
        <form id="resetPasswordForm" method="post" action="">
            <div class="form-group mb-5">
                <h2>Reset Password</h2>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="newpass_lbl" for="rstnewpass">New password</label>
                <input class="form-control" type="password" name="rstnewpass" id="rstnewpass">
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($newpassErr)) echo $newpassErr; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" id="confirmpass_lbl" for="rstconfirmpass">Confirm password</label>
                <input class="form-control" type="password" name="rstconfirmpass" id="rstconfirmpass">
                <div id="err_msg">
                    <span class="mt-n1"><?php if (isset($confirmpassErr)) echo $confirmpassErr; else echo ''; ?></span>
                </div>
            </div>

            <div class="form-group mt-5 d-flex justify-content-center">
                <button class="btn btn-lg btn-rounded btn-primary" name="updBtn" id="updBtn" value="rstpass">Update Password</button>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <div id="err_msg">
                    <span><?php if (isset($commonErr)) echo $commonErr; else echo ''; ?></span>
                </div>
            </div>
        </form>
<?php } else { ?>
        <form id="changePasswordForm" method="post" action="">
                <div class="form-group mb-5">
                    <h2>Change Password</h2>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" id="oldpass_lbl" for="chgoldpass">Old password</label>
                    <input class="form-control" type="password" name="chgoldpass" id="chgoldpass">
                    <div id="err_msg">
                        <span class="mt-n1"><?php if (isset($oldpassErr)) echo $oldpassErr; else echo ''; ?></span>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label" id="newpass_lbl" for="chgnewpass">New password</label>
                    <input class="form-control" type="password" name="chgnewpass" id="chgnewpass">
                    <div id="err_msg">
                        <span class="mt-n1"><?php if (isset($newpassErr)) echo $newpassErr; else echo ''; ?></span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" id="confirmpass_lbl" for="chgconfirmpass">Confirm password</label>
                    <input class="form-control" type="password" name="chgconfirmpass" id="chgconfirmpass">
                    <div id="err_msg">
                        <span class="mt-n1"><?php if (isset($confirmpassErr)) echo $confirmpassErr; else echo ''; ?></span>
                    </div>
                </div>

                <div class="form-group mt-5 d-flex justify-content-center">
                    <button class="btn btn-lg btn-rounded btn-primary" name="updBtn" id="updBtn" value="updpass">Update Password</button>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <div id="err_msg">
                        <span><?php if (isset($commonErr)) echo $commonErr; else echo ''; ?></span>
                    </div>
                </div>
            </form>
<?php } ?>
    </div>
</div>

</body>
</html>