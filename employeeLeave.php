<?php

function employeeLeaveCheckColumn($connect, $empID)
{
    $tblName = EMPLEAVE;

    try {
        $empLeaveResult = getData('*', '', '', L_TYPE, $connect);

        if (!$empLeaveResult) {
            echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
            echo "<script>location.href ='dashboard.php';</script>";
        }

        $empLeaveFields = "employeeID";
        $empLeaveValues = $empID;

        $datafield = $newvalarr = array();

        while ($empLeaveRow = $empLeaveResult->fetch_assoc()) {
            if ($empLeaveRow['auto_assign'] == 'yes' && $empLeaveRow['leave_status'] == 'Active') {

                $columnName = "leaveType_" . $empLeaveRow['id'];
                $query = "SHOW COLUMNS FROM employee_leave LIKE '$columnName'";
                $result = mysqli_query($connect, $query);

                if (!$result || mysqli_num_rows($result) == 0) {
                    $query = "ALTER TABLE employee_leave ADD COLUMN $columnName INT AFTER employeeID";
                    mysqli_query($connect, $query);
                }

                $empLeaveFields .= ", $columnName";
                $empLeaveValues .= ", " . $empLeaveRow['num_of_days'];

                if ($empLeaveRow['num_of_days']) {
                    array_push($newvalarr, $empLeaveRow['num_of_days']);
                    array_push($datafield, $columnName);
                }
            }
        }

        // Insert into employee_leave table if new columns were added
        if (strpos($empLeaveFields, ',') !== false) {
            $query = "INSERT INTO " . $tblName . "($empLeaveFields,create_by,create_date,create_time) VALUES ($empLeaveValues,'" . USER_ID . "',curdate(),curtime())";
            $returnData = mysqli_query($connect, $query);
            $dataID = $connect->insert_id;

            if ($empID) {
                array_push($newvalarr, $empID);
                array_push($datafield, 'employeeID');
            }
        }
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }

    if (isset($query)) {

        $log = [
            'log_act'      => 'add',
            'cdate'        => date("Y-m-d"),
            'ctime'        => date("H:i:s"),
            'uid'          => USER_ID,
            'cby'          => USER_ID,
            'query_rec'    => $query,
            'query_table'  => $tblName,
            'page'         => 'employee leave',
            'connect'      => $connect,
            'newval'       => implodeWithComma($newvalarr),
            'act_msg'      => actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, 'add', (isset($returnData) ? '' : $errorMsg))
        ];

        audit_log($log);
    }
}
