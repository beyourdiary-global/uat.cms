<?php

function deleteRecord($tbl,$id,$name,$connect,$cdate,$ctime,$tblname){
    $query =  "UPDATE $tbl SET status = 'D' WHERE id = ".$id;

    mysqli_query($connect, $query);

    // audit log
    $log = array();
    $log['log_act'] = 'delete';
    $log['cdate'] = $cdate;
    $log['ctime'] = $ctime;
    $log['uid'] = $log['cby'] = USER_ID;
    $log['act_msg'] = USER_NAME . " deleted the data <b>$name</b> from <b><i>$tblname Table</i></b>.";
    $log['query_rec'] = $query;
    $log['query_table'] = $tbl;
    $log['page'] = $tblname;
    $log['connect'] = $connect;
    audit_log($log);
}

?>