<?php

function deleteRecord($tbl, $id, $name, $connect, $cdate, $ctime, $tblname)
{
    try {
        $query =  "UPDATE $tbl SET status = 'D' WHERE id = " . $id;

        mysqli_query($connect, $query);

        // audit log
        $log = [
            'log_act'       =>'delete',
            'cdate'         => $cdate,
            'ctime'         => $ctime,
            'uid'           => USER_ID,
            'cby'           => USER_ID,
            'act_msg'       => USER_NAME . " deleted the data <b>$name</b> from <b><i>$tblname Table</i></b>.",
            'query_rec'     => $query,
            'query_table'   => $tbl,
            'page'          => $tblname,
            'connect'       => $connect,
        ];
        
        audit_log($log);

    } catch (Exception $e) {
        echo '<script>console.error("Error Message : ' . $e->getMessage() . '");</script>';
        echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    }
}
