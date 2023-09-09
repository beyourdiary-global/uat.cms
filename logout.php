<?php
include "include/common.php";
include "include/connection.php";

if(isset($_SESSION['userid']))
{
    // audit log
    $log = array();
    $log['log_act'] = 'logout';
    $log['uid'] = $_SESSION['userid'];

    $query = "SELECT name FROM ".USR_USER." WHERE id ='".$_SESSION['userid']."'";
    $result = mysqli_query($connect, $query);
    $row = $result->fetch_assoc();

    $log['act_msg'] = $row['name'] . " has logout the system.";
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['cby'] = $_SESSION['userid'];
    $log['connect'] = $connect;

    audit_log($log);

    setcookie(session_name(), '', 100);
    session_unset();
    session_destroy();

    // redirect
    echo ("<script>location.href = 'index.php';</script>");
}
?>