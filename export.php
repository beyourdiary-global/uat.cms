<?php
include "include/common.php";
include "include/connection.php";



if (isset($_POST['ids'])) {
        $ids = $_POST['ids'];
        $tblName = $_POST['tblName'];
        echo($ids);
        $log = [
        'log_act' => 'export',
        'act_msg' => USER_NAME . ' exported data with [<b>ID = ' . (is_array($ids) ? implode(', ', $ids) : $ids) . '</b>] from <b><i>' . $tblName . ' Table</i></b>.',
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