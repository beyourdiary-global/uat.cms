<?php
include "include/common.php";
include "include/common_variable.php";
include "include/connection.php";

$resetpass_btn = 1;

if($resetpass_btn == 1)
{
    $email = post('email-addr');
    $datetime_add24h = date("Y-m-d H:i:s", strtotime('+24 hours'));
    $token = md5($datetime_add24h.'forgotpassword');

    if($email)
    {
        $query = "SELECT * FROM ".USR_USER." WHERE email = '".$email."'";
        $result = mysqli_query($connect, $query);
        $row = $result->fetch_assoc();

        if(mysqli_num_rows($result) == 1)
        { 
            $name = $row['name'];

            ob_start();

            // Multiple recipients
            $to = $email; // note the comma

            // Subject
            $subject = 'Request Reset Password';

            // Message
            $message_user = '
            <html>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0">
                <head>
                    <title></title>
                </head>
                <body style="margin: 0;background-color: #FFF0E3;font-family: sans-serif;">
                    
                <div class="container" style="display: grid;gap: 5px;min-width: 350px;margin-left: 15px;margin-right: 15px;width: 550px;height: 55rem;margin: auto;">

                    <table class="header" style="border-spacing: 0;width: 100%;">
                        <tr>
                            <td class="logo" align="center" style="padding: 0;">
                                <img src="'.img.byd_logo.'" style="border: 0;">
                            </td>
                        </tr>
                    </table> <!-- End Header -->

                    <table class="middle" style="border-spacing: 0;width: 100%;height: 600px;background-color: #FFFFFF;border-radius: 18px;">
                        <tr class="resetpass_row">
                            <td class="resetpass_pic" align="center" style="padding: 0;height: 360px;">
                                <img src="'.img.'password_reset.png" style="border: 0;width: 310px;min-width: 310px;height: auto;">
                            </td>
                        </tr>
                        <tr class="content_row">
                            <td class="content" align="center" style="padding: 0;">
                                <p class="h1" style="font-size: 22;font-weight: bold;margin-top: -60px;">Forgot Your Password?</p>
                                <p class="h2" style="font-size: 12;">Hi, <b>'.$name.'</b>,</p>
                                <p class="center" style="font-size: 10;width: 60%;line-height: 1.4;margin-top: -8px;text-align: left !important;">
                                    You recently requested to reset your password for your ['.$to.'] account. Use the button below to reset it. <b>This password reset is only valid for the next 24 hours</b>.
                                </p>
                                <p class="btn_row" style="margin-top: 35px;">
                                    <a class="btn" href="changePassword.php?token='.$token.'&email='.$to.'" style="font-size: 13;border: 1px solid black;color: #FFFFFF;text-decoration: none;background-color: #000000;border-radius: 5px;padding: 10px 15px;">Reset Password</a>
                                </p>
                            </td>
                        </tr>
                    </table> <!-- End Middle -->

                    <table class="footer" style="border-spacing: 0;width: 100%;margin-top: -85px;">
                        <tr class="social-media" align="center" style="text-align: center;">
                            <td style="padding: 0;">
                                <a href="#"><img class="icon" src="'.img.facebook.'" style="border: 0;width: 15px;height: 15px;margin: 0 5px;"></a>
                                <a href="#"><img class="icon" src="'.img.twitter.'" style="border: 0;width: 15px;height: 15px;margin: 0 5px;"></a>
                                <a href="#"><img class="icon" src="'.img.linkedin.'" style="border: 0;width: 15px;height: 15px;margin: 0 5px;"></a>
                                <a href="#"><img class="icon" src="'.img.instagram.'" style="border: 0;width: 15px;height: 15px;margin: 0 5px;"></a>
                            </td>
                        </tr>
                    </table> <!-- End Footer -->
                </div> <!-- End Container -->
                </body>
            </html>
            ';
            
            $message_admin = 'Username: '.$name."\r\n".'Email: '.$to.'';

            // To send HTML mail, the Content-type header must be set
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=utf-8';

            // Additional headers
            $headers[] = 'To: <'.$to.'>';
            $headers[] = 'From: noreply <noreply@example.com>';
            $headers[] = 'Cc:'.email_cc.'';
            $headers[] = 'Bcc:';

            // Mail it
            mail($to, $subject, $message_user, implode("\r\n", $headers));
            mail(email_cc, 'User Request Reset Password Action', $message_admin);

            ob_get_clean();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<?php include "header.php"; ?>
<link rel="stylesheet" href="./css/main.css">
</head>

<body>

<div class="forgotpassContainer container d-flex justify-content-center">
    <div class="col-lg-5 col-md-5 col-ms-5 col-xs-5">
        <div class="mb-4 d-flex justify-content-center" id="logo_element">
            <img src="./image/logo2.png">
        </div>

        <form id="forgotpassForm" name="forgotpassForm" method="post" action="">
        <div class="px-5 py-5 rounded">
            <div class="mb-3">
                <div class="form-group forgotpass-title">
                    <h3>Forgot Password?</h3>
                    <h7>Enter your email to get a password reset link</h7>
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
                    <button class="btn btn-block btn-primary" name="resetpass_btn" id="resetpass_btn">Reset password</button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

</body>

<script>
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

$('#resetpass_btn').on('click', () => {
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

    if (email_chk == 1)
        $("#forgotpassForm").submit();
    else
        return false;
})
</script>

</html>