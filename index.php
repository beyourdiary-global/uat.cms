<!DOCTYPE html>
<html>
<head>
<?php include "header.php"; ?>
<link rel="stylesheet" href="./css/login.css">
</head>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-lg-5 col-md-5 col-ms-5 col-xs-5">
        <div class="mb-4 d-flex justify-content-center" id="logo_element">
            <img src="./image/logo2.png">
        </div>

        <form id="loginForm" name="loginForm" method="post" action="login.php">
        <div class="px-5 py-5 rounded">
            <div class="mb-3">
                <div class="form-group login-title">
                    <h3>Login</h3>
                    <h7>Access to our dashboard</h7>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <label class="form-label" id="email-addr_lbl" for="email-addr">Email Address</label>
                    <input class="form-control" type="email" name="email-addr" id="email-addr">
                    <span id="email-addr_error"></span>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <div class="d-flex justify-content-between" id="row-password-label">
                        <label class="form-label" id="password_lbl" for="password">Password</label>
                        <a id="forgot-password_link" href="#">Forgot password?</a>
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
        
            <div class="mb-3">
                <div class="form-group">
                    <button class="btn btn-block btn-primary" name="login_btn" id="login_btn">Login</button>
                </div>
                <div id="err_msg">
                    <span>
                        <?php 
                            switch(isset($_GET['err']) ? $_GET['err'] : '')
                            {
                                case '1':
                                    echo "Email not existed.";
                                    break;
                                case '2':
                                    echo "Wrong password entered. Please try again.";
                                    break;
                                case '3':
                                    echo "Account is been blocked. Please reset your password.";
                                    break;
                                default:
                                    echo "";
                            }
                        ?>
                    </span>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

</body>

<script>
$(document).ready(() => {
    $("#row-password-input i").on('click', () => {
        if($("#password").attr("type") == "password") {
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
    if(!(isEmail($('#email-addr').val()))) {
        $("#email-addr_error").text("Wrong email format!");
    } else {
        $("#email-addr_error").text("");
    }

    if($('#email-addr').val() === '' || $('#email-addr').val() === null || $('#email-addr').val() === undefined) {
        $("#email-addr_error").text("");
    }
})

$('#login_btn').on('click', () => {
    event.preventDefault();
    var email_chk = 0;
    var password_chk = 0;

    if(!(isEmail($('#email-addr').val()))) {
        email_chk = 0;
        $("#email-addr_error").text("Wrong email format!");
    } else { 
        $("#email-addr_error").text("");
        email_chk = 1; 
    }

    // Check sequence: Email empty? -> Email format correct?
    if(($('#email-addr').val() === '' || $('#email-addr').val() === null || $('#email-addr').val() === undefined)) {
        email_chk = 0;
        $("#email-addr_error").text("Please enter your email");
    } else { 
        if(!(isEmail($('#email-addr').val()))) {
            email_chk = 0;
            $("#email-addr_error").text("Wrong email format!");
        } else {
            $("#email-addr_error").text("");
            email_chk = 1; 
        }
    }

    if($('#password').val() === '' || $('#password').val() === null || $('#password').val() === undefined) {
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