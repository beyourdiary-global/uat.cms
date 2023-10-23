<?php
$pageTitle = "Theme Setting";
include 'menuHeader.php';

$redirect_page = $SITEURL . '/dashboard.php';
$tblname = PROJ;
$allowed_ext = array("png","jpg","jpeg","svg");

$img_path = img_server.'themes/';
if(!file_exists($img_path))
{
    mkdir($img_path, 0777, true);
}

// to display data to input
$rst = getData('*',"id = '1'",$tblname,$connect);

if($rst != false)
{
    $dataExisted = 1;
    $row = $rst->fetch_assoc();
}

if(post('actionBtn'))
{

    $website_name = postSpaceFilter('website_name');
    $light_logo = post('light_logo');
    $favicon = post('favicon');

    $action = post('actionBtn');

    switch($action)
    {
        case 'save':
            $act = 'E';
            $query = "UPDATE ".PROJ." SET ";

            if($website_name != '')
            {
                $query .= "company_name = '$website_name'";
            } else $err = "Website Name cannot be empty.";

            // check image
            if($_FILES["light_logo"]["size"] != 0)
            {
                // move file
                $light_logo_name = $_FILES["light_logo"]["name"];
                $light_logo_tmp_name = $_FILES["light_logo"]["tmp_name"];
                $img_ext = pathinfo($light_logo_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if(in_array($img_ext_lc, $allowed_ext))
                {
                    move_uploaded_file($light_logo_tmp_name, $img_path . $light_logo_name);
                    $query .= ", logo = '$light_logo_name'";
                }
                else $err2 = "Only allow PNG, JPG, JPEG or SVG file";
            }

            // check image
            if($_FILES["favicon"]["size"] != 0)
            {
                // move file
                $favicon_name = $_FILES["favicon"]["name"];
                $favicon_tmp_name = $_FILES["favicon"]["tmp_name"];
                $img_ext = pathinfo($favicon_name, PATHINFO_EXTENSION);
                $img_ext_lc = strtolower($img_ext);

                if(in_array($img_ext_lc, $allowed_ext))
                {
                    move_uploaded_file($favicon_tmp_name, $img_path . $favicon_name);
                    $query .= ", meta_logo = '$favicon_name'";
                }
                else $err3 = "Only allow PNG, JPG, JPEG or SVG file";
            }
            $query .= " WHERE id = '1'";

            $oldvalarr = $chgvalarr = array();

            if($row['company_name'] != $website_name)
            {
                array_push($oldvalarr, $row['company_name']);
                array_push($chgvalarr, $website_name);
            }

            $light_logo_name = isset($light_logo_name) ? $light_logo_name : '';
            if(($row['logo'] != $light_logo_name) && ($light_logo_name != ''))
            {
                array_push($oldvalarr, $row['logo']);
                array_push($chgvalarr, $light_logo_name);
            }

            $favicon_name = isset($favicon_name) ? $favicon_name : '';
            if(($row['meta_logo'] != $favicon_name) && ($favicon_name != ''))
            {
                array_push($oldvalarr, $row['meta_logo']);
                array_push($chgvalarr, $favicon_name);
            }

            // convert into string
            $oldval = implode(",",$oldvalarr);
            $chgval = implode(",",$chgvalarr);

            // retake view data after edit
            $rst = getData('*',"id = '1'",$tblname,$connect);

            $act = 'E';
            $_SESSION['tempValConfirmBox'] = true;
            if($oldval != '' && $chgval != '')
            {
                // edit
                mysqli_query($connect, $query);
                generateDBData($tblname, $connect);

                // audit log
                $log = array();
                $log['log_act'] = 'edit';
                $log['cdate'] = $cdate;
                $log['ctime'] = $ctime;
                $log['uid'] = $log['cby'] = USER_ID;

                $log['act_msg'] = USER_NAME . " edited the data";
                for($i=0; $i<sizeof($oldvalarr); $i++)
                {
                    if($i==0)
                        $log['act_msg'] .= " from <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                    else
                        $log['act_msg'] .= ", <b>\'".$oldvalarr[$i]."\'</b> to <b>\'".$chgvalarr[$i]."\'</b>";
                }
                $log['act_msg'] .= " from <b><i>Theme Setting</i></b>.";

                $log['query_rec'] = $query;
                $log['query_table'] = $tblname;
                $log['page'] = 'Theme Setting';
                $log['oldval'] = $oldval;
                $log['changes'] = $chgval;
                $log['connect'] = $connect;
                audit_log($log);
            }

            if($rst != false)
            {
                $dataExisted = 1;
                $row = $rst->fetch_assoc();
            }
            break;
        default:
            echo 'Error.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
</head>

<body>

<div class="d-flex flex-column my-3 ms-3">
    <p><a href="<?= $redirect_page ?>">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> Theme Setting</p>
</div>

<div id="leavetypeFormContainer" class="container-fluid d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6 formWidthAdjust">
            <form id="leavetypeForm" method="post" action="" enctype="multipart/form-data">
                <div class="row d-flex justify-content-center">
                    <div class="col-12 col-md-10">
                        <div class="form-group mb-5">
                            <h2>
                                Theme Setting
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row d-flex justify-content-center mb-3">
                    <div class="col-12 col-md-10 mb-3">
                        <div class="form-group d-flex flex-md-row flex-column">
                            <div class="col-12 col-md-3">
                                <label class="form-label form_lbl" id="website_name_lbl" for="website_name">Website Name</label>
                            </div>
                            <div class="col-12 col-md-9">
                                <input class="form-control" type="text" name="website_name" id="website_name" value="<?= $row['company_name'] ?>">
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row d-flex justify-content-center mb-3">
                    <div class="col-12 col-md-10">
                        <div class="form-group d-flex flex-md-row flex-column">
                            <div class="col-12 col-md-3">
                                <label class="form-label form_lbl" id="light_logo_lbl" for="light_logo">Light Logo</label>
                            </div>
                            <div class="col-12 col-md-7">
                                <input class="form-control" type="file" name="light_logo" id="light_logo" value="">
                                <span class="mt-n1">Recommended image size is 40px x 40px</span>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="d-flex justify-content-center justify-content-md-end">
                                    <img id="light_logo_preview" name="light_logo_preview" src="<?php if($row['logo'] == '' || $row['logo'] == NULL) echo img.byd_logo; else echo $img_path . $row['logo']; ?>" width="40px" height="40px">
                                    <input type="hidden" name="light_logo_imageValue" value="<?= $row['logo'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row d-flex justify-content-center mb-3">
                    <div class="col-12 col-md-10">
                        <div class="form-group d-flex flex-md-row flex-column">
                            <div class="col-12 col-md-3">
                                <label class="form-label form_lbl" id="favicon_lbl" for="favicon">Favicon</label>
                            </div>
                            <div class="col-12 col-md-7">
                                <input class="form-control" type="file" name="favicon" id="favicon" value="">
                                <span class="mt-n1">Recommended image size is 16px x 16px</span>
                                <div id="err_msg">
                                    <span class="mt-n1"><?php if (isset($err3)) echo $err3; ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="d-flex justify-content-center justify-content-md-end">
                                    <img id="favicon_preview" name="favicon_preview" src="<?php if($row['meta_logo'] == '' || $row['meta_logo'] == NULL) echo img.byd_logo; else echo $img_path . $row['meta_logo']; ?>" width="16px" height="16px">
                                    <input type="hidden" name="favicon_imageValue" value="<?= $row['meta_logo'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex justify-content-center flex-md-row flex-column">
                            <button class="btn btn-lg btn-rounded btn-primary mx-2 mb-2" name="actionBtn" id="actionBtn" value="save">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
</div>
<?php
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Theme Setting","","'.$redirect_page.'","'.$act.'");</script>';
}
?>
</body>
<script>
centerAlignment('leavetypeFormContainer')

$('#light_logo').on('change', function() {
    previewImage(this,'light_logo_preview')
})

$('#favicon').on('change', function() {
    previewImage(this,'favicon_preview')
})
</script>
</html>