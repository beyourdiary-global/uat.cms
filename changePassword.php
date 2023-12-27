<?php
$pageTitle = "Change Password";

// get required file
include 'menuHeader.php';

$sendEmail = '';
$redirect_page = '';

// checking
if (input('token') && input('email'))
    $pageMode = 'emailRstPassword';
else
    $pageMode = 'userChgPassword';


if ($pageMode == 'userChgPassword') {
    // menuheader

    $redirect_page = $SITEURL . '/dashboard.php';

    if (post('actionBtn') == 'updpass') {
        $id = $_SESSION['userid'];
        $old_password = postSpaceFilter('chgoldpass');
        $new_password = postSpaceFilter('chgnewpass');
        $confirm_password = postSpaceFilter('chgconfirmpass');

        if ($id && $old_password && $new_password && $confirm_password) {
            if ($new_password == $confirm_password) {
                $rst = getData('*', "id = '$id'", USR_USER, $connect);
                if (!$rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $row = $rst->fetch_assoc();

                if (mysqli_num_rows($rst) == 1) {
                    if ($row['password_alt'] == md5($old_password)) {
                        try {
                            $_SESSION['tempValConfirmBox'] = true;
                            $query = "UPDATE " . USR_USER . " SET password_alt = '" . md5($new_password) . "' WHERE id = '" . $id . "'";
                            mysqli_query($connect, $query);
                        } catch (Exception $e) {
                            $commonErr = $e->getMessage();
                        }
                    } else $oldpassErr = 'Wrong old password entered, please try again.';
                } else $commonErr = 'No email existed in the system.';
            } else $newpassErr = $confirmpassErr = 'Password Not Match.';
        } else $commonErr = 'Field cannot be blank.';
    }
} else if ($pageMode == 'emailRstPassword') {
    $redirect_page = $SITEURL . '/index.php';

    if (post('actionBtn') == 'rstpass') {
        $email = input('email');
        $token = input('token');
        $new_password = post('rstnewpass');
        $confirm_password = post('rstconfirmpass');

        if ($email && $token && $new_password && $confirm_password) {
            if ($new_password == $confirm_password) {
                $rst = getData('*', "email = '$email'", USR_USER, $connect);
                if (!$rst) {
                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                }
                $row = $rst->fetch_assoc();

                if (mysqli_num_rows($rst) == 1) {
                    try {
                        $_SESSION['tempValConfirmBox'] = true;
                        $query = "UPDATE " . USR_USER . " SET password_alt = '" . md5($new_password) . "' WHERE email = '" . $email . "'";
                        mysqli_query($connect, $query);
                        $sendEmail = 'rstSendEmail';
                    } catch (Exception $e) {
                        $commonErr = $e->getMessage();
                    }
                } else $commonErr = 'No email existed in the system.';
            } else $newpassErr = $confirmpassErr = 'Password Not Match.';
        } else $commonErr = 'Field cannot be blank.';
    }

    if ($sendEmail == 'rstSendEmail') {
        ob_start();
        $to = $email;
        $subject = 'Password has been reset';
        $message = 'Password has been successfully reset.';
        mail($to, $subject, $message_user);
        ob_get_clean();
    }
} else {
    echo 'Error.';
}

if (isset($_SESSION['tempValConfirmBox'])) {
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","User Password","","' . $redirect_page . '","PC");</script>';
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
    <?php if ($pageMode == 'emailRstPassword') { ?>
        <link rel="stylesheet" href="./css/login.css">
    <?php } ?>
</head>

<body>
    <?php if ($pageMode == 'userChgPassword') { ?>
        <div class="d-flex flex-column my-3 ms-3">
            <div class="row">
                <p><a href="<?= $redirect_page ?>">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                    <?php
                    switch ($pageMode) {
                        case 'userChgPassword':
                            echo 'Change Password';
                            break;
                        case 'emailRstPassword':
                            echo 'Reset Password';
                            break;
                        default:
                            echo '';
                    }
                    ?></p>
            </div>
        </div>
    <?php } ?>

    <div id="passwordContainer" class="container d-flex justify-content-center mt-2">
        <?php if ($pageMode == 'emailRstPassword') { ?>
            <div class="col-12 col-md-5">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-center my-4" id="logo_element">
                            <img src="./image/logo2.png">
                        </div>
                    </div>
                </div>

                <form id="resetPasswordForm" method="post" action="">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mt-5 mb-3 d-flex flex-column align-items-center">
                                <h2>Reset Password</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center">
                        <div class="col-10">
                            <div class="form-group mb-3">
                                <label class="form-label" id="newpass_lbl" for="rstnewpass">New password</label>
                                <input class="form-control" type="password" name="rstnewpass" id="rstnewpass">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($newpassErr)) echo $newpassErr;
                                                        else echo ''; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center">
                        <div class="col-10">
                            <div class="form-group mb-3">
                                <label class="form-label" id="confirmpass_lbl" for="rstconfirmpass">Confirm password</label>
                                <input class="form-control" type="password" name="rstconfirmpass" id="rstconfirmpass">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($confirmpassErr)) echo $confirmpassErr;
                                                        else echo ''; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center">
                        <div class="col-10">
                            <div class="form-group mt-5 d-flex justify-content-center">
                                <button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="rstpass">Update Password</button>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center">
                        <div class="col-12">
                            <div class="d-flex justify-content-center my-4">
                                <div id="err_msg">
                                    <span><?php if (isset($commonErr)) echo $commonErr;
                                            else echo ''; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php } else if ($pageMode == 'userChgPassword') { ?>
                <div class="col-6 col-md-6 formWidthAdjust">
                    <form id="changePasswordForm" method="post" action="">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-5">
                                    <h2>Change Password</h2>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label class="form-label" id="oldpass_lbl" for="chgoldpass">Old password</label>
                                    <input class="form-control" type="password" name="chgoldpass" id="chgoldpass">
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php if (isset($oldpassErr)) echo $oldpassErr;
                                                            else echo ''; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label class="form-label" id="newpass_lbl" for="chgnewpass">New password</label>
                                    <input class="form-control" type="password" name="chgnewpass" id="chgnewpass">
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php if (isset($newpassErr)) echo $newpassErr;
                                                            else echo ''; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label class="form-label" id="confirmpass_lbl" for="chgconfirmpass">Confirm password</label>
                                    <input class="form-control" type="password" name="chgconfirmpass" id="chgconfirmpass">
                                    <div id="err_msg">
                                        <span class="mt-n1"><?php if (isset($confirmpassErr)) echo $confirmpassErr;
                                                            else echo ''; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mt-5 d-flex justify-content-center">
                                    <button class="btn btn-lg btn-rounded btn-primary" name="actionBtn" id="actionBtn" value="updpass">Update Password</button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-center mt-4">
                                    <div id="err_msg">
                                        <span><?php if (isset($commonErr)) echo $commonErr;
                                                else echo ''; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php } else {
                echo 'Error.';
            } ?>
                </div>
            </div>
    </div>
    <script>
        centerAlignment("passwordContainer");
    </script>
</body>

</html>