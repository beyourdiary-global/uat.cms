<?php
session_start();
extract($_POST);
$pagePin = '0';

include 'include/connection.php'; 

$proc          = $_POST['proc'];
$user_name     = post('username');
$user_password = post('inputpassword');
if($proc == 'login')
{
    if(!empty($user_name) && !empty($user_password))
    {        
		$pws_hash = md5($user_password);
        
		$loginssql = "SELECT id, username, name, email, access_id  FROM ".USR_USER." WHERE username='$user_name' AND password_alt ='$pws_hash' LIMIT 1";
		$loginchk			= @mysqli_query($connect,$loginssql);
		$lgin				= @mysqli_fetch_array($loginchk);
		$cloginchk			= @mysqli_num_rows($loginchk);
		$userid				= $lgin['id'];
		$user_name			= $lgin['username'];
		$user_displayName	= $lgin['name'];
		$user_email			= $lgin['email'];
		$user_role			= $lgin['access_id'];
		if($cloginchk>0){	
		//user pin control
		$pinsql   = "SELECT pin FROM ".USR_GROUP." WHERE id = '$user_role'";
		$pinin    = @mysqli_fetch_array(@mysqli_query($connect,$pinsql));
		$user_pin = $pinin['pin'];
		$pin_arr  = explode(",",$user_pin);
		array_push($pin_arr, "0");

		$checking_right = $user_name == 'superadmin'? false : true;

		@$_SESSION['usr_pin']    = $pin_arr;
		@$_SESSION['username']  = $user_name;
		@$_SESSION['userid']    = $userid;
		@$_SESSION['login_name'] = $user_displayName;
		@$_SESSION['user_email'] = $user_email;
		@$_SESSION['SpCase'] = $checking_right;
        setcookie('pinCookies', $pin_arr, time() + (86400 * 7),"/");

			//login_log($checking_right);
			echo "<script>window.location ='dashboard.php';</script>";   
        }
		else{
			echo "<script>alert('You have entered an invalid username or password! Please try again');</script>";
			echo "<script>window.top.location ='index.php';</script>";      
		}
	}else{
		echo "<script>alert('You have entered an invalid username or password! Please try again');</script>";
		echo "<script>window.top.location ='index.php';</script>";      
	}
}
?>
<!DOCTYPE html>
<html dir="ltr">
<?php echo $header; ?>

<body class="bg-dark">
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="
          auth-wrapper
          d-flex
          no-block
          justify-content-center
          align-items-center
          bg-dark
        ">
            <div class="auth-box bg-dark border-top border-secondary">
                <div id="loginform" style="display: flex; justify-content: center; align-items: center;height: 100vh;">
                    <div>
                        <div class=" text-center pt-3 pb-3">
                            <span class="db"><img src="../assets/images/logo.png" alt="logo" /></span>
                        </div>
                        <!-- Form -->
                        <form class="form-horizontal mt-3" id="loginform" method="POST">
                            <div class="row pb-4">
                                <div class="col-12">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-success text-white h-100"
                                                id="basic-addon1"><i class="mdi mdi-account fs-4"></i></span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg" placeholder="Username"
                                            aria-label="Username" name="username" aria-describedby="basic-addon1"
                                            required="" autofocus value="test" />
                                    </div>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning text-white h-100"
                                                id="basic-addon2"><i class="mdi mdi-lock fs-4"></i></span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg" placeholder="Password"
                                            aria-label="Password" name="inputpassword" aria-describedby="basic-addon1"
                                            value="00000000" required="" />
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="pt-3"><input type="hidden" name="proc" value="login">
                                                <button class="btn btn-success float-end text-white" type="submit">
                                                    Login
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- Login box.scss -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Page wrapper scss in scafholding.scss -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Page wrapper scss in scafholding.scss -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Right Sidebar -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Right Sidebar -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- All Required js -->
        <!-- ============================================================== -->
        <?php include_once "include/footerJquery.php"; ?>
        <!-- ============================================================== -->
        <!-- This page plugin js -->
        <!-- ============================================================== -->
        <script>
        $(".preloader").fadeOut();
        // ==============================================================
        // Login and Recover Password
        // ==============================================================
        $("#to-recover").on("click", function() {
            $("#loginform").slideUp();
            $("#recoverform").fadeIn();
        });
        $("#to-login").click(function() {
            $("#recoverform").hide();
            $("#loginform").fadeIn();
        });
        </script>
</body>

</html>