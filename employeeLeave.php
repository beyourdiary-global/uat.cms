<?php

function employeeLeaveCheckColumn($connect, $empID)
{
    try {
        $empLeaveResult = getData('*', '', L_TYPE, $connect);

        $empLeaveFields = "employeeID";
        $empLeaveValues = $empID;

        $employeeLeaveDays = array();

        $employeeID = '';

        while ($empLeaveRow = $empLeaveResult->fetch_assoc()) {
            if ($empLeaveRow['auto_assign'] == 'yes' && $empLeaveRow['leave_status'] == 'Active') {
                $columnName = "leaveType_" . $empLeaveRow['id'];
                $employeeID = $empLeaveRow['id'];
                $query = "SHOW COLUMNS FROM employee_leave LIKE '$columnName'";
                $result = mysqli_query($connect, $query);

                if (!$result || mysqli_num_rows($result) == 0) {
                    $query = "ALTER TABLE employee_leave ADD COLUMN $columnName INT AFTER employeeID";
                    mysqli_query($connect, $query);
                }

                $empLeaveFields .= ", $columnName";
                $empLeaveValues .= ", " . $empLeaveRow['num_of_days'];

                $employeeLeaveDays[$columnName] = $empLeaveRow['num_of_days'];
            }
        }

        // Insert into employee_leave table if new columns were added
        if (strpos($empLeaveFields, ',') !== false) {
            $query = "INSERT INTO employee_leave($empLeaveFields,create_by,create_date,create_time) VALUES ($empLeaveValues,'" . USER_ID . "',curdate(),curtime())";
            mysqli_query($connect, $query);

            $newvalarr = array();

            // check value
            if ($employeeID != '')
                array_push($newvalarr, $employeeID);

            $newval = implode(",", $newvalarr);

            $employeeLeaveDays = implode(",", $employeeLeaveDays);

            // audit log
            $log = array();
            $log['log_act'] = 'add';
            $log['cdate'] = date("Y-m-d");
            $log['ctime'] = date("H:i:s");
            $log['uid'] = $log['cby'] = USER_ID;
            $log['act_msg'] = USER_NAME . " added <b> " . $employeeID . "</b> into <b><i>Employee Leave Table</i></b>.";
            $log['query_rec'] = $query;
            $log['query_table'] = 'employee_leave';
            $log['page'] = 'employee leave';
            $log['newval'] = $newval .  $employeeLeaveDays;
            $log['connect'] = $connect;
            audit_log($log);
        }
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}
