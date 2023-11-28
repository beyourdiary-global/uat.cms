<?php

function employeeLeaveCheckColumn($connect, $empID)
{
    try {
        $empLeaveResult = getData('*', '', L_TYPE, $connect);

        $empLeaveFields = "employeeID";
        $empLeaveValues = $empID;

        while ($empLeaveRow = $empLeaveResult->fetch_assoc()) {
            if ($empLeaveRow['auto_assign'] == 'yes' && $empLeaveRow['leave_status'] == 'Active') {
                $columnName = "leaveType_" . $empLeaveRow['id'];
                $query = "SHOW COLUMNS FROM employee_leave LIKE '$columnName'";
                $result = mysqli_query($connect, $query);

                if (!$result || mysqli_num_rows($result) == 0) {
                    // Column does not exist, add it
                    $query = "ALTER TABLE employee_leave ADD COLUMN $columnName INT";
                    mysqli_query($connect, $query);
                }

                // Add values for the newly added column
                $empLeaveFields .= ", $columnName";
                $empLeaveValues .= ", " . $empLeaveRow['num_of_days'];
            }
        }

        // Insert into employee_leave table if new columns were added
        if (strpos($empLeaveFields, ',') !== false) {
            $query = "INSERT INTO employee_leave($empLeaveFields) VALUES ($empLeaveValues)";
            mysqli_query($connect, $query);
        }

    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}
?>
