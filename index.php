<!DOCTYPE html>
<html>
<head>
<!-- Meta Tags -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0">

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"  rel="stylesheet"/>

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"  rel="stylesheet"/>

<!-- MDB -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css"  rel="stylesheet"/>

<title>Login</title>
</head>

<style>
body {
    background-color: #F4F6F6;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

label, h3 {
    /* font-weight: bold; */
    color: black !important;
}

input {
    font-weight: bold;
    color: black !important;
}

h7 {
    color: grey !important;
}

a#forgot-password_link {
    color: grey !important;
}

.container {
    min-width: 768px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.login-title {
    text-align: center;
}

#loginForm {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0px 0px 1px 1px #E4E6E6;
}

#login_btn {
    background-image: linear-gradient(to right, #ff9b44 0%, #fc6075 100%);
}

#logo_element{
    display: block;
    margin-left: auto;
    margin-right: auto;
    width:50%;
}

.icon {
    padding: 10px;
    min-width: 40px;
}

#forgot-password_link:hover {
    text-decoration: underline;
}

#row-password-input i {
    position: absolute;
}

#row-password-input i:hover {
    cursor:pointer;
}
</style>

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
})
</script>

<body>

<div class="container d-flex justify-content-center">
    <div class="col-lg-5 col-md-5 col-ms-5 col-xs-5">
        <div class="mb-4 d-flex justify-content-center" id="logo_element">
            <img src="./image/logo2.png">
        </div>

        <form id="loginForm" name="loginForm">
        <div class="px-5 py-5 rounded">
            <div class="mb-3">
                <div class="form-group login-title">
                    <h3>Login</h3>
                    <h7>Access to our dashboard</h7>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <label class="form-label" id="email_addr_lbl" for="email_addr">Email Address</label>
                    <input class="form-control" type="text" name="email_addr" id="email_addr">
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
                    </div>
                </div>
            </div>
        
            <div class="mb-3">
                <div class="form-group">
                    <button class="btn btn-block btn-primary" name="login_btn" id="login_btn">Login</button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

</body>
</html>