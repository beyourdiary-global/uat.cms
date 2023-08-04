<?php
include "include/common.php";
include "include/connection.php";

$email = post('email-addr');
$password = md5(post('password'));

if($email && $password)
{
     $loginquery = "SELECT * FROM ".USR_USER." WHERE email='".$email."'";
     $loginresult = mysqli_query($connect, $loginquery);
     
     if(!(mysqli_num_rows($loginresult) == 1))
     {
          return header('Location: index.php?err=1');
     }
     else
     {
          $loginrows = $loginresult->fetch_assoc();
          if($loginrows['fail_count'] == 4)
          {
               return header('Location: index.php?err=3');
          }

          if($loginrows['password_alt'] != $password)
          {
               mysqli_query($connect, "UPDATE ".USR_USER." SET fail_count = fail_count + 1 WHERE email = '".$email."'");
               return header('Location: index.php?err=2');
          else 
          {
               if($loginrows['fail_count'] >= 1 || $loginrows['fail_count'] <= 3)
                    mysqli_query($connect, "UPDATE ".USR_USER." SET fail_count = 0 WHERE email = '".$email."' AND password_alt = '".$password."'");

                    $_SESSION['userid'] = $loginrows['id'];
                    $_SESSION['user_name'] = $loginrows['name'];

                    // audit log
                    $log = array();
                    $log['log_act'] = 'login';
                    $log['uid'] = $log['cby'] = $loginrows['id'];
                    $log['act_msg'] = $loginrows['name'] . " has login to the system.";
                    $log['cdate'] = $cdate;
                    $log['ctime'] = $ctime;
                    $log['connect'] = $connect;

                    audit_log($log);
                    return header('Location: dashboard.php');
          }
     }
}
?>