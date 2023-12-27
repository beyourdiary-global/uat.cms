<?php

function deleteRecord($tbl, $id, $name, $connect, $cdate, $ctime, $pageTitle)
{
    try {
        $query =  "UPDATE $tbl SET status = 'D' WHERE id = " . $id;
        mysqli_query($connect, $query);
    } catch (Exception $e) {
        $errorMsg =  $e->getMessage();
    }

    if (isset($errorMsg)) {
        $errorMsg = str_replace('\'', '', $errorMsg);
    }

    // audit log
    $log = [
        'log_act'       => 'delete',
        'cdate'         => $cdate,
        'ctime'         => $ctime,
        'uid'           => USER_ID,
        'cby'           => USER_ID,
        'act_msg'       => (isset($errorMsg)) ? USER_NAME . " failed to delete the data <b>$name</b> from <b><i>$tbl Table</i></b>. ( $errorMsg )" : USER_NAME . " deleted the data <b>$name</b> from <b><i>$tbl Table</i></b>.",
        'query_rec'     => $query,
        'query_table'   => $tbl,
        'page'          => $pageTitle,
        'connect'       => $connect,
    ];

    audit_log($log);
}
