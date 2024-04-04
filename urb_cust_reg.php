<?php
$pageTitle = "Urbanism Member Registration";
$initial_page = "Facebook Customer Record (Deals)";

include_once 'menuHeader.php';
include_once 'checkCurrentPagePin.php';

$tblName = FB_CUST_DEALS;
$reg_tblName = URBAN_CUST_REG;

$dataID = input('id');
$act = input('act');


$pageAction = getPageAction($act);

$allowed_ext = array("png", "jpg", "jpeg", "svg", "pdf");


$redirect_page = $SITEURL . '/fb_cust_deals_table.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$clearLocalStorage = '<script>localStorage.clear();</script>';

$img_path = img_server . 'urbanism_member_registration/';
if (!file_exists($img_path)) {
    mkdir($img_path, 0777, true);
}

if ($dataID && $act== 'I') { //edit/remove/view
    $rst = getData('*', "id='" . $dataID . "'", 'LIMIT 1', $tblName, $connect);
 
    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } else {
        // If $rst is false or no data found ($act==null)
        $errorExist = 1;
        $_SESSION['tempValConfirmBox'] = true;
        $act = "F";
    }
}else if ($dataID) { //edit/remove/view
    $rst = getData('*', "name='" . $dataID . "'", 'LIMIT 1', $reg_tblName, $connect);
 
    if ($rst != false && $rst->num_rows > 0) {
        $dataExisted = 1;
        $row = $rst->fetch_assoc();
    } else {
        // If $rst is false or no data found ($act==null)
        $errorExist = 1;
        $_SESSION['tempValConfirmBox'] = true;
        $act = "F";
    }
}

if (!($dataID) && !($act)) {
    echo '<script>
    alert("Invalid action.");
    window.location.href = "' . $redirect_page . '"; // Redirect to previous page
    </script>';
} 
if (post('actionBtn')) {
    
    $action = post('actionBtn');

    $umr_name = postSpaceFilter('umr_name_hidden');
    $umr_ic = postSpaceFilter('umr_ic');
    $umr_add = postSpaceFilter('umr_add');
    $umr_date = postSpaceFilter('umr_date');
    $umr_attach = null;

    if (isset($_FILES["umr_attach"]) && $_FILES["umr_attach"]["size"] != 0) {
        $umr_attach = $_FILES["umr_attach"]["name"];
    } elseif (isset($_POST['umr_attachmentValue'])) {
        $umr_attach = $_POST['umr_attachmentValue'];
    }

    $datafield = $oldvalarr = $chgvalarr = $newvalarr = array();

    switch ($action) {
        case 'addRecord':
        case 'updRecord':

            if ($_FILES["umr_attach"]["size"] != 0) {
                // move file
                $umr_file_name = $_FILES["umr_attach"]["name"];
                $umr_file_tmp_name = $_FILES["umr_attach"]["tmp_name"];
                $img_ext = pathinfo($umr_file_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if (in_array($img_ext_lc, $allowed_ext)) {
                    $highestNumber = 0;
                    $files = glob($img_path . $umr_name . '_' . $umr_ic . '_' . $umr_date . '_*.' . $img_ext);

                    foreach ($files as $file) {
                        $filename = basename($file);

                        // Adjust the regex to match the new file naming convention
                        if (preg_match('/' . preg_quote($umr_name . '_' . $umr_ic . '_' . $umr_date, '/') . '_(\d+)\.' . preg_quote($img_ext, '/') . '$/', $filename, $matches)) {
                            $number = (int) $matches[1];
                            $highestNumber = max($highestNumber, $number);
                        }
                    }

                    $unique_id = $highestNumber + 1;
                    $new_file_name = $umr_name . '_' . $umr_ic . '_' . $umr_date . '_' . $unique_id . '.' . $img_ext_lc;

                    // Move the uploaded file
                    if (move_uploaded_file($umr_file_tmp_name, $img_path . $new_file_name)) {
                        $umr_attach = $new_file_name; // Update $umr_attach with the new filename
                    } else {
                        $err2 = "Failed to upload the file.";
                    }
                } else {
                    $err2 = "Only allow PNG, JPG, JPEG, SVG or PDF file";
                }
            }

            if (!$umr_name) {
                $name_err = "Name is required!";
                break;
            } else if (!$umr_ic) {
                $ic_err = "IC is required!";
                break;
            } else if (!$umr_date) {
                $date_err = "Date is required!";
                break;
            } else if (!$umr_add) {
                $pic_err = "Address is required!";
                break;
            } else if (!$umr_attach) {
                $attach_err = "Please attach a copy of your IC/Driving License.";
                break;
            } else if ($action == 'addRecord') {
                try {
                    //check values
                    if ($umr_name) {
                        array_push($newvalarr, $umr_name);
                        array_push($datafield, 'name');
                    }
                    if ($umr_ic) {
                        array_push($newvalarr, $umr_ic);
                        array_push($datafield, 'ic');
                    }

                    if ($umr_add) {
                        array_push($newvalarr, $umr_add);
                        array_push($datafield, 'address');
                    }

                    if ($umr_date) {
                        array_push($newvalarr, $umr_date);
                        array_push($datafield, 'date');
                    }

                    if ($umr_attach) {
                        array_push($newvalarr, $umr_attach);
                        array_push($datafield, 'attachment');
                    }

                    $query = "INSERT INTO " . $reg_tblName . "(name,ic,address,reg_date,attachment,create_by,create_date,create_time) VALUES ('$umr_name','$umr_ic','$umr_add','$umr_date','$umr_attach','" . USER_ID . "',curdate(),curtime())";
                    // Execute the query
                    $returnData = mysqli_query($connect, $query);
                    $_SESSION['tempValConfirmBox'] = true;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            } else {
                try {
                    // take old value
                    $rst = getData('*', "name = '$dataID'", 'LIMIT 1', $reg_tblName, $connect);
                    $rst2 = getData('*', "id = '$dataID'", 'LIMIT 1', $tblName, $connect);
                    $row = $rst->fetch_assoc();
                    $row2 = $rst->fetch_assoc();

                    // check value
                    if ($row2['name'] != $umr_name) {
                        array_push($oldvalarr, $row2['name']);
                        array_push($chgvalarr, $umr_name);
                        array_push($datafield, 'name');
                    }

                    if ($row['ic'] != $umr_ic) {
                        array_push($oldvalarr, $row['ic']);
                        array_push($chgvalarr, $umr_ic);
                        array_push($datafield, 'fb ic');
                    }

                    if ($row['address'] != $umr_add) {
                        array_push($oldvalarr, $row['address']);
                        array_push($chgvalarr, $umr_add);
                        array_push($datafield, 'address');
                    }

                    if ($row['reg_date'] != $umr_date) {
                        array_push($oldvalarr, $row['reg_date']);
                        array_push($chgvalarr, $umr_date);
                        array_push($datafield, 'date');
                    }

                    if ($row['attachment'] != $umr_attach) {
                        array_push($oldvalarr, $row['attachment']);
                        array_push($chgvalarr, $umr_attach);
                        array_push($datafield, 'attachment');
                    }

                    // convert into string
                    $oldval = implode(",", $oldvalarr);
                    $chgval = implode(",", $chgvalarr);
                    $_SESSION['tempValConfirmBox'] = true;

                    if (count($oldvalarr) > 0 && count($chgvalarr) > 0) {
                        $query = "UPDATE " . $reg_tblName . " SET name = '$umr_name', ic = '$umr_ic', address = '$umr_add', reg_date = '$umr_date', attachment = '$umr_attach', update_date = curdate(), update_time = curtime(), update_by ='" . USER_ID . "' WHERE name = '$dataID'";
                        $returnData = mysqli_query($connect, $query);

                    } else {
                        $act = 'NC';
                    }
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    $act = "F";
                }
            }

            // audit log
            if (isset($query)) {

                $log = [
                    'log_act' => $pageAction,
                    'cdate' => $cdate,
                    'ctime' => $ctime,
                    'uid' => USER_ID,
                    'cby' => USER_ID,
                    'query_rec' => $query,
                    'query_table' => $tblName,
                    'page' => $pageTitle,
                    'connect' => $connect,
                ];

                if ($pageAction == 'Add') {
                    $log['newval'] = implodeWithComma($newvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, $newvalarr, '', '', $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                } else if ($pageAction == 'Edit') {
                    $log['oldval'] = implodeWithComma($oldvalarr);
                    $log['changes'] = implodeWithComma($chgvalarr);
                    $log['act_msg'] = actMsgLog($dataID, $datafield, '', $oldvalarr, $chgvalarr, $tblName, $pageAction, (isset($returnData) ? '' : $errorMsg));
                }
                audit_log($log);
            }

            break;

        case 'back':
            echo $clearLocalStorage . ' ' . $redirectLink;
            break;
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/main.css">

</head>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>
    <div class="page-load-cover">
        <div class="d-flex flex-column my-3 ms-3">
            <p><a href="<?= $redirect_page ?>">
                    <?= $initial_page ?>
                </a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php
              
                echo displayPageAction($act, $pageTitle);
                ?>
            </p>

        </div>

        <div id="formContainer" class="container d-flex justify-content-center">
            <div class="col-6 col-md-6 formWidthAdjust">
                <form id="Form" method="post" action="" enctype="multipart/form-data">
                    <div class="form-group mb-5">
                        <h2>
                            <?php
                            echo displayPageAction($act, $pageTitle);
                            ?>
                        </h2>
                    </div>

                    <div id="err_msg" class="mb-3">
                        <span class="mt-n2" style="font-size: 21px;">
                            <?php if (isset($err1))
                                echo $err1; ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="umr_name_lbl" for="umr_name">Name<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="umr_name" id="umr_name" value="<?php
                                $name_rst = getData('*', "id='" . $dataID . "'", 'LIMIT 1', $tblName, $connect);
                              
                                if ($name_row = $name_rst->fetch_assoc()) {
                                    echo $name_row['name'];
                                }
                                ?>" <?php echo 'disabled' ?>>
                                <input type="hidden" name="umr_name_hidden" id="umr_name_hidden"
                                    value="<?php echo $dataID; ?>">
                                <?php if (isset($name_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $name_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="umr_ic_lbl" for="umr_ic">IC<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="umr_ic" id="umr_ic" value="<?php
                                if (isset($dataExisted) && isset($row['ic']) && !isset($umr_ic)) {
                                    echo $row['ic'];
                                } else if (isset($umr_ic)) {
                                    echo $umr_ic;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($ic_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $ic_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label form_lbl" id="umr_add_lbl" for="umr_add"> Address<span
                                        class="requireRed">*</span></label>
                                <input class="form-control" type="text" name="umr_add" id="umr_add" value="<?php
                                if (isset($dataExisted) && isset($row['address']) && !isset($umr_add)) {
                                    echo $row['address'];
                                } else if (isset($umr_add)) {
                                    echo $umr_add;
                                }
                                ?>" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($add_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $add_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form_lbl" id="umr_date_label" for="umr_date">Registration
                                    Date<span class="requireRed">*</span></label>
                                <input class="form-control" type="date" name="umr_date" id="umr_date" value="<?php
                                if (isset($dataExisted) && isset($row['reg_date']) && !isset($umr_date)) {
                                    echo $row['reg_date'];
                                } else if (isset($umr_date)) {
                                    echo $umr_date;
                                } else {
                                    echo 'dd/mm/yyyy'; // Placeholder text
                                }
                                ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" <?php if ($act == '')
                                    echo 'disabled' ?>>
                                <?php if (isset($date_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $date_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>

                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form_lbl" id="umr_attach_lbl" for="umr_attach">IC/Driving
                                    License Attachment*</label>
                                <input class="form-control" type="file" name="umr_attach" id="umr_attach" <?php if ($act == '')
                                    echo 'disabled' ?>>

                                <?php if (isset($row['attachment']) && $row['attachment']) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo "Current Attachment: " . htmlspecialchars($row['attachment']); ?>
                                        </span>
                                    </div>
                                    <input type="hidden" name="existing_attachment"
                                        value="<?php echo htmlspecialchars($row['attachment']); ?>">
                                <?php } ?>

                                <?php if (isset($attach_err)) { ?>
                                    <div id="err_msg">
                                        <span class="mt-n1">
                                            <?php echo $attach_err; ?>
                                        </span>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-center justify-content-md-end px-4">
                                    <?php
                                    $attachmentSrc = '';

                                    if (isset($dataExisted) && isset($row['attachment']) && !isset($umr_attach)) {
                                        $attachmentSrc = ($row['attachment'] == '' || $row['attachment'] == NULL) ? '' : $img_path . $row['attachment'];
                                    } else if (isset($umr_attach)) {
                                        $attachmentSrc = $img_path . $umr_attach;
                                    }
                                    ?>
                                    <img id="umr_attach_preview" name="umr_attach_preview"
                                        src="<?php echo $attachmentSrc; ?>" class="img-thumbnail"
                                        alt="Attachment Preview">
                                    <input type="hidden" name="umr_attachmentValue" id="umr_attachmentValue" value="<?php if (isset($row['attachment']))
                                        echo $row['attachment']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-5 d-flex justify-content-center flex-md-row flex-column">
                        <?php
                        switch ($act) {
                            case 'I':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="addRecord">Add Record</button>';
                                break;
                            case 'E':
                                echo '<button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 submitBtn" name="actionBtn" id="actionBtn" value="updRecord">Edit Record</button>';
                                break;
                        }
                        ?>
                        <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2 cancel" name="actionBtn"
                            id="actionBtn" value="back">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    /*
        oufei 20231014
        common.fun.js
        function(title, subtitle, page name, ajax url path, redirect path, action)
        to show action dialog after finish certain action (eg. edit)
    */
    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo $clearLocalStorage;
        echo '<script>confirmationDialog("","","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>
    <script>
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        setButtonColor();
        preloader(300, action);

        <?php
        include "./js/urb_cust_reg.js"
            ?>
    </script>

</body>

</html>