<?php
$pageTitle = "Login";

include "./include/common.php";
include "./include/common_variable.php";
include "init.php";

$img_path = $SITEURL . '/' . img_server . 'themes/';

$tblName = PROJ;
$result =  getData('*', "id = '1'", '', $tblName, $connect);

if (!$result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/index.php';</script>";
}

$row = $result->fetch_assoc();

include "header.php";
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/login.css">
    <link rel="icon" type="image" href="<?php echo (isset($row['meta_logo'])) ? $img_path . $row['meta_logo'] : $SITEURL . '/image/logo2.png'; ?>">
</head>

<body>

    <div class="container d-flex justify-content-center mt-2">
        <div class="col-12 col-md-5">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center my-4" id="logo_element">
                        <img id="logo" style="min-height:100px; max-height : 150px; width : auto;" src="<?php echo (isset($row['logo'])) ? $img_path . $row['logo'] : $SITEURL . '/image/logo2.png'; ?>">
                    </div>
                </div>
            </div>

            <form id="loginForm" name="loginForm" method="post" action="login.php">
                <div class="row">
                    <div class="form-group mt-5 mb-3 d-flex flex-column align-items-center">
                        <h3>Login</h3>
                        <h7>Access to our dashboard</h7>
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <div class="row d-flex justify-content-center">
                        <div class="col-10">
                            <div class="form-group mb-3">
                                <label class="form-label" id="email-addr_lbl" for="email-addr">Email Address</label>
                                <input class="form-control" type="email" name="email-addr" id="email-addr">
                                <span id="email-addr_error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center">
                        <div class="col-10">
                            <div class="form-group mb-3">
                                <div class="d-flex justify-content-between" id="row-password-label">
                                    <label class="form-label" id="password_lbl" for="password">Password</label>
                                    <a id="forgot-password_link" href="forgotPassword.php">Forgot password?</a>
                                </div>
                                <div id="row-password-input">
                                    <div class="d-flex justify-content-end">
                                        <i class="fa fa-eye-slash icon"></i>
                                    </div>
                                    <input class="form-control" type="password" name="password" id="password">
                                    <span id="password_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex justify-content-center">

                        <div class="col-10">
                            <div class="form-group mb-3">
                                <button class="btn btn-block btn-primary" name="login_btn" id="login_btn" style="background-color: <?php echo (isset($row['themesColor'])) ? $row['themesColor'] : ''; ?>">Login</button>
                            </div>

                            <div id="err_msg" class="d-flex justify-content-center mb-3">
                                <span>
                                    <?php
                                    $var = numberInput('err');
                                    if ($var) {
                                        switch ($var) {
                                            case '1':
                                                echo "Email not existed.";
                                                break;
                                            case '2':
                                                echo "Wrong password entered. Please try again.";
                                                break;
                                            case '3':
                                                echo "Account is been blocked. Please reset your password.";
                                                break;
                                            case '4':
                                                echo "<p class='text-center'>You Don't Have Permission Access To " . $row['project_title'] . "</p>";
                                                break;
                                            default:
                                                echo "";
                                        }
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</body>

<script>
    checkCurrentPage('invalid');

    $(document).ready(() => {
        $("#row-password-input i").on('click', () => {
            if ($("#password").attr("type") == "password") {
                $("#password").attr("type", "text");
                $("#row-password-input i").addClass("fa-eye");
                $("#row-password-input i").removeClass("fa-eye-slash");
            } else if ($("#password").attr("type") == "text") {
                $("#password").attr("type", "password");
                $("#row-password-input i").addClass("fa-eye-slash");
                $("#row-password-input i").removeClass("fa-eye");
            }
        })
    });

    $('#email-addr').on('input', () => {
        $("#email-addr_error").text("");
        $("#password_error").text("");
    })

    $('#password').on('input', () => {
        $("#email-addr_error").text("");
        $("#password_error").text("");
    })

    $('#login_btn').on('click', () => {
        event.preventDefault();
        var email_chk = 0;
        var password_chk = 0;

        if (!(isEmail($('#email-addr').val()))) {
            email_chk = 0;
            $("#email-addr_error").text("Wrong email format!");
        } else {
            $("#email-addr_error").text("");
            email_chk = 1;
        }

        // Check sequence: Email empty? -> Email format correct?
        if (($('#email-addr').val() === '' || $('#email-addr').val() === null || $('#email-addr').val() === undefined)) {
            email_chk = 0;
            $("#email-addr_error").text("Please enter your email");
        } else {
            if (!(isEmail($('#email-addr').val()))) {
                email_chk = 0;
                $("#email-addr_error").text("Wrong email format!");
            } else {
                $("#email-addr_error").text("");
                email_chk = 1;
            }
        }

        if ($('#password').val() === '' || $('#password').val() === null || $('#password').val() === undefined) {
            password_chk = 0;
            $("#password_error").text("Please enter your password");
        } else {
            $("#password_error").text("");
            password_chk = 1;
        }

        if (email_chk == 1 && password_chk == 1)
            $("#loginForm").submit();
        else
            return false;
    })
</script>

</html>