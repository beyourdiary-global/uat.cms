<?php
include "include/common.php";
include "include/connection.php";

if(isset($_SESSION['userid']))
{
    // audit log

    $query = "SELECT name FROM ".USR_USER." WHERE id ='".$_SESSION['userid']."'";
    $result = mysqli_query($connect, $query);

    if (!$result) {
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
        echo "<script>location.href ='$SITEURL/index.php';</script>";
    }
    $row = $result->fetch_assoc();
    $log = [
        'log_act' => 'reset',
        'act_msg' => "{$row['name']} has reset the filter selection.",
        'cdate'   => $cdate,
        'ctime'   => $ctime,
        'uid'     => $_SESSION['userid'],
        'cby'     => $_SESSION['userid'],
        'connect' => $connect,
    ];
    
    audit_log($log);

   
    if(isset($_GET['redirect'])) {
        echo ("<script>location.href = '".$_GET['redirect']."';</script>");
    } else {
        echo ("<script>location.href = 'index.php';</script>");
    }
}
?>
