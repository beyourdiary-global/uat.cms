<?php
$pageTitle = "Employee";
include 'menuHeader.php';

$ee_id = input('id');
$act = input('act');
$redirect_page = '';
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/form.css">
</head>

<body>

<div class="my-3 container-fluid">
    <form id="employeeForm" method="post">
        <!-- Title -->
        <div class="row">
            <div class="form-group mb-5 ">
                <h3>
                    <?php
                    switch($act)
                    {
                        case 'I': echo 'Add Employee'; break;
                        case 'E': echo 'Edit Employee'; break;
                        default: echo 'View Employee';
                    }
                    ?>
                </h3>
            </div>
        </div>

        <!-- Main Form -->
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <h3>Personal Information</h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="employee_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="employee_name">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="employee_surname" class="form-label">Surname</label>
                            <input type="text" class="form-control" id="employee_surname">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="employee_ic" class="form-label">IC</label>
                            <input type="text" class="form-control" id="employee_ic">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="employee_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="employee_email">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="employee_phonenum" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="employee_phonenum">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="employee_alt_phonenum" class="form-label">Alternate Phone Number</label>
                            <input type="email" class="form-control" id="employee_alt_phonenum">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                    </div>
                </div>
            </div>
        </div>

        <!-- Button -->
        <div class="row">
        </div>
    </form>
</div>

</body>
</html>