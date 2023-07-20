<?php
include "include/common.php";
include "include/connection.php";

$email = post('email-addr');
$password = md5(post('password'));

$loginquery = "SELECT * FROM ".USR_USER." WHERE email='".$email."' AND password_alt='".$password."'";
$loginresult = mysqli_query($connect, $loginquery);

if(!(mysqli_num_rows($loginresult) == 1))
{
     echo '<script type="text/javascript">alert("Wrong email or password entered.");</script>';
     return false;
}
else
{
     echo '<script type="text/javascript">alert("Matched.");</script>';
     return false;
}
?>