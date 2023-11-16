<?php
$pageTitle = "Employee Details";
include 'menuHeader.php';

$employee_info_id = input('id');
$act = input('act');
$redirect_page = $SITEURL . '/employeeDetailsTable.php';
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="./css/main.css">
</head>

<style>
    .requireRed {
        color: red;
    }
</style>

<body>

    <div class="d-flex flex-column my-3 ms-3">
        <p><a href="<?= $redirect_page ?>"><?php echo $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
            <?php
            switch ($act) {
                case 'I':
                    echo 'Add ' . $pageTitle;
                    break;
                case 'E':
                    echo 'Edit ' . $pageTitle;
                    break;
                default:
                    echo 'View ' . $pageTitle;
            }
            ?></p>
    </div>

    <div id="employeeDetailsFormContainer" class="container-fluid d-flex justify-content-center">
        <div class="col-sm-12">
            <form id="employeeDetailsForm" method="post" action="">
                <div class="form-group mb-4">
                    <h2>
                        <?php
                        switch ($act) {
                            case 'I':
                                echo 'Add ' . $pageTitle;
                                break;
                            case 'E':
                                echo 'Edit ' . $pageTitle;
                                break;
                            default:
                                echo 'View ' . $pageTitle;
                        }
                        ?>
                    </h2>
                </div>

                <fieldset class="border p-2" style="border-radius: 3px;">
                    <legend class="float-none w-auto p-2">Employee Identity</legend>
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="identityType">Identity type <span class="requireRed">*</span></label>
                                <select class="form-select" aria-label="Default select example" name="identityType" id="identityType">
                                    <option value="" disabled selected>Select your employee identity type</option>
                                    <option value="">1</option>
                                </select>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="identityNum">Identity Number <span class="requireRed">*</span></label>
                                <input class="form-control" type="number" name="identityNum" id="identityNum" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-2" style="border-radius: 3px;">
                    <legend class="float-none w-auto p-2">Personal Details</legend>
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm-4">
                                <label class="form-label" id="name_lbl" for="employee_name">Full Name <span class="requireRed">*</span></label>
                                <input class="form-control " type="text" name="employee_name" id="employee_name" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" id="email_lbl" for="employee_email">Email <span class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="employee_email" id="employee_email" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" id="gender_lbl" for="employee_gender">Gender <span class="requireRed">*</span></label>
                                <select class="form-select" aria-label="Default select example" name="employee_gender" id="employee_gender">
                                    <option value="" disabled selected>Select your gender</option>
                                    <option value="Female">Female</option>
                                    <option value="Male">Male</option>
                                </select>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">

                            <div class="col-sm-3">
                                <label class="form-label" id="email_lbl" for="employee_birthday">Birthday <span class="requireRed">*</span></label>
                                <input class="form-control" type="date" name="employee_birthday" id="employee_birthday" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <label class="form-label" id="gender_lbl" for="employee_race">Race</label>
                                <select class="form-select" aria-label="Default select example" name="employee_race" id="employee_race">
                                    <option value="" disabled selected>Select your race</option>
                                    <option value="">1</option>
                                </select>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <label class="form-label" id="gender_lbl" for="employee_residence_status">Residence  <span class="requireRed">*</span></label>
                                <select class="form-select" aria-label="Default select example" name="employee_residence_status" id="employee_residence_status">
                                    <option value="" disabled selected>Select your residence status</option>
                                    <option value="Residence">Residence</option>
                                    <option value="Non-residence">Non-residence</option>
                                </select>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <label class="form-label" id="name_lbl" for="employee_race">Nationality <span class="requireRed">*</span></label>
                                <select class="form-select" aria-label="Default select example" name="employee_residence_status" id="employee_residence_status">
                                    <option value="" disabled selected>Select your nationality</option>
                                    <option value="">1</option>
                                </select>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="employee_phone">Phone Number <span class="requireRed">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="height: 40px;">+60</span>
                                    <input type="text" name="employee_phone" id="employee_phone" class="form-control" style="height: 40px;">
                                </div>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="employee_alternate_phone">Alternate Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="height: 40px;">+60</span>
                                    <input type="text" name="employee_alternate_phone" id="employee_alternate_phone" class="form-control" style="height: 40px;">
                                </div>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-2" style="border-radius: 3px;">
                    <legend class="float-none w-auto p-2">Address</legend>
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="employee_address_1">Address Line 1</label>
                                <input class="form-control " type="text" name="employee_address_1" id="employee_address_1" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="employee_address_2">Address Line 2</label>
                                <input class="form-control " type="text" name="employee_address_2" id="employee_address_2" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm-5">
                                <label class="form-label" id="name_lbl" for="employee_city_1">City</label>
                                <input class="form-control " type="text" name="employee_city_1" id="employee_city_1" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <label class="form-label" id="name_lbl" for="employee_state_1">State</label>
                                <select class="form-select" aria-label="Default select example" name="employee_gender" id="employee_gender">
                                    <option value="" disabled selected>Select your state</option>
                                    <option value="Johor">Johor</option>
                                    <option value="Kedah">Kedah</option>
                                    <option value="Kelantan">Kelantan</option>
                                    <option value="Malacca">Malacca</option>
                                    <option value="Negeri Sembilan">Negeri Sembilan</option>
                                    <option value="Pahang">Pahang</option>
                                    <option value="Penang">Penang</option>
                                    <option value="Perak">Perak</option>
                                    <option value="Perlis">Perlis</option>
                                    <option value="Sabah">Sabah</option>
                                    <option value="Sarawak">Sarawak</option>
                                    <option value="Selangor">Selangor</option>
                                    <option value="Terengganu">Terengganu</option>
                                    <option value="Kuala Lumpur">Kuala Lumpur</option>
                                    <option value="Labuan">Labuan</option>
                                    <option value="Putrajaya">Putrajaya</option>
                                </select>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <label class="form-label" id="name_lbl" for="employee_postcode_1">Postcode</label>
                                <input class="form-control " type="number" name="employee_postcode_1" id="employee_postcode_1" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-2" style="border-radius: 3px;">
                    <legend class="float-none w-auto p-2">Marital status </legend>
                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="maritalStatus">Marital status <span class="requireRed">*</span></label>
                                <select class="form-select" aria-label="Default select example" name="maritalStatus" id="maritalStatus">
                                    <option value="" disabled selected>Select your marital status</option>
                                    <option value="">1</option>
                                </select>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label" id="name_lbl" for="noOfChild">No of Children</label>
                                <input class="form-control " type="number" name="noOfChild" id="noOfChild" value="">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="float-end">
                    <button type="" class="btn btn-outline-primary ml-auto mt-2 pull-right">Next</button>
                </div>
                
            </form>
        </div>
    </div>
</body>

</html>