<?php
$pageTitle = "Rate Checking";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$redirect_page = $SITEURL . '/dashboard.php';
$redirectLink = ("<script>location.href = '$redirect_page';</script>");
$pinAccess = checkCurrentPin($connect, $pageTitle);

if (!isActionAllowed('check', $pinAccess))
    echo $redirectLink;

$country = input('country') ? input('country') : input('from');
$dispCountrySel = "";
$dispDeliverOpt = "";
$active_d = "active";
$active_i = "";
$selected_d = "true";
$selected_i = "false";
$showActive_d = "show active";
$showActive_i = "";
$area = "D";

if ($country) {
    $dispCountrySel = "style=\"display: none;\"";
    $dispDeliverOpt = "";
    $country2 = getCountry($country, $connect);
    $domestic = "<option value=\"$country|$country2\" selected style=\"display:none;\">$country2</option>";
} else {
    $dispCountrySel = "";
    $dispDeliverOpt = "style=\"display: none;\"";
}

if (post('to') && post('from')) {
    if (post('to') != post('from')) {
        $active_d = "";
        $active_i = "active";
        $selected_d = 'false';
        $selected_i = 'true';
        $showActive_d = "";
        $showActive_i = "show active";
    }
} else if (isset($_GET['to_full'], $_GET['from_full'])) {
    if ($_GET['from_full'] != $_GET['to_full']) {
        $active_d = "";
        $active_i = "active";
        $selected_d = 'false';
        $selected_i = 'true';
        $showActive_d = "";
        $showActive_i = "show active";
        $area = "I";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<script>
    $(document).ready(() => {
        createSortingTable('rateCheckTable');
    });
    preloader(500);
</script>

<style>
    .title-form {
        font-size: 32px;
        font-weight: 500;
        color: #000000;
    }

    .chkRate {
        background-color: #FFFFFF;
        border-radius: 5px;
        box-shadow: 0px 0px 1px 1px #E4E6E6;
    }

    .disabledSelection {
        pointer-events: none;
    }

    p{
        margin-bottom: 0;
    }
</style>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div class="my-3 container-fluid d-flex justify-content-center">
            <div class="chkRate col-12 col-sm-12 col-md-8 col-lg-8">
                <div class="px-4 py-4">
                    <div class="mb-3">
                        <div class="row">
                            <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> Check Rate</p>
                        </div>

                        <div class="row">
                            <div class="col-12 d-flex justify-content-between flex-wrap">
                                <h2>Check Rate</h2>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="country_selector" <?= $dispCountrySel ?>>
                        <form id="c_selectorForm">
                            <div class="row d-flex flex-md-row flex-column">
                                <div class="col-12 col-md-9">
                                    <label class="form-label form_lbl" id="country_lbl" for="country">From Which Country</label>
                                    <select class="form-select mb-3" id="country" name="country" placeholder="State">
                                        <option value="MY">Malaysia</option>
                                        <option value="SG">Singapore</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 d-flex justify-content-center">
                                    <button class="btn btn-rounded btn-primary mx-auto my-auto" id="actionBtn" type="submit">Confirm</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="deliveryOption" <?= $dispDeliverOpt ?>>
                        <div id="nav-tabs_options">
                            <!-- Tabs navs -->
                            <ul class="nav nav-tabs nav-fill mb-3" id="dest-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link <?= $active_d ?>" id="domestic-tab" data-bs-toggle="tab" href="#domestic-section" role="tab" aria-controls="domestic-section" aria-selected="<?= $selected_d ?>">Domestic</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link <?= $active_i ?>" id="international-tab" data-bs-toggle="tab" href="#international-section" role="tab" aria-controls="international-section" aria-selected="<?= $selected_i ?>">International</a>
                                </li>
                            </ul>
                            <!-- Tabs navs -->

                            <!-- Tabs content -->
                            <div class="tab-content" id="dest-form">
                                <div class="tab-pane fade <?= $showActive_d ?>" id="domestic-section" role="tabpanel" aria-labelledby="domestic-tab">
                                    <form id="domestic" method="post">
                                        <div class="form-group">
                                            <label class="form-label form_lbl" id="" for="from">From:</label>
                                            <div class="row">
                                                <div class="col-6 col-md-6">
                                                    <select class="disabledSelection form-select mb-3" id="from" name="from">
                                                        <?= isset($domestic) ? $domestic : ''; ?>
                                                    </select>
                                                </div>

                                                <div class="col-6 col-md-6">
                                                    <input class="form-control" type="text" id="postcode_from" name="postcode_from" placeholder="Postcode" style="line-height:30px;" value="<?php echo (($area == 'D') ? (isset($_GET['postcodefrom']) ? htmlspecialchars($_GET['postcodefrom']) : '') : ''); ?>">
                                                    <span id="errorMsg1"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label form_lbl" id="" for="to">To:</label>
                                            <div class="row">
                                                <div class="col-6 col-md-6">
                                                    <select class="disabledSelection form-select mb-3" id="to" name="to">
                                                        <?= isset($domestic) ? $domestic : ''; ?>
                                                    </select>
                                                </div>

                                                <div class="col-6 col-md-6">
                                                    <input class="form-control" type="text" id="postcode_to"  name="postcode_to" placeholder="Postcode" style=";line-height:30px;" value="<?php echo (($area == 'D') ? (isset($_GET['postcodeto']) ? htmlspecialchars($_GET['postcodeto']) : '') : ''); ?>">
                                                    <span id="errorMsg2"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="hr" />

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                                    <input class="form-control" type="number" min="0" step=".01" id="weight" name="weight" placeholder="Weight (kg)" style="line-height:30px;" value="<?= post('weight') ? post('weight') : '' ?>">
                                                    <span id="errorMsg3"></span>
                                                </div>
                                                <div class="col-12 col-md-6 d-flex">
                                                    <button class="btn btn-sm btn-primary mb-auto domestic_btn" name="actionBtn" id="actionBtn" value="chkRate_d" style="width:100%">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade <?= $showActive_i ?>" id="international-section" role="tabpanel" aria-labelledby="international-tab">
                                    <form id="international" method="post">
                                        <div class="form-group">
                                            <label class="form-label form_lbl " id="international_from" for="from">From:</label>
                                            <div class="row">
                                                <div class="col-6 col-md-6">
                                                    <select class="disabledSelection form-select mb-3" id="from" name="from">
                                                        <?= isset($domestic) ? $domestic : ''; ?>
                                                    </select>
                                                </div>

                                                <div class="col-6 col-md-6">
                                                    <input class="form-control" type="text" id="international_postcode_from" name="postcode_from" placeholder="Postcode" style="line-height:30px;" value="<?php echo (($area == 'I') ? (isset($_GET['postcodefrom']) ? htmlspecialchars($_GET['postcodefrom']) : '') : ''); ?>">
                                                    <span id="errorMsg4"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label form_lbl" id="" for="to">To:</label>
                                            <div class="row">
                                                <div class="col-6 col-md-6">
                                                    <select class="form-select mb-3" id="international_to" name="to">
                                                        <?php
                                                        $all_country = getCountry('all', $connect);
                                                        asort($all_country);
                                                        foreach ($all_country as $key => $val) {

                                                            if (strcasecmp($key, $country) == 0)
                                                                continue;

                                                            $selected = '';
                                                            if (isset($data['to'])) {
                                                                if ($data['to'] == $key)
                                                                    $selected = "selected";
                                                            } else if (isset($_GET['to_full'])) {
                                                                if ($_GET['to_full'] == $val) {
                                                                    $selected = "selected";
                                                                }
                                                            }

                                                            echo "<option value=\"$key|$val\" $selected>$val</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <div class="col-6 col-md-6">
                                                    <input class="form-control" type="text" id="international_postcode_to" name="postcode_to" placeholder="Postcode" style="line-height:30px;" value="<?php echo (($area == 'I') ? (isset($_GET['postcodeto']) ? htmlspecialchars($_GET['postcodeto']) : '') : ''); ?>">
                                                    <span id="errorMsg5"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="hr" />

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                                    <input class="form-control" type="number" min="0" step=".01" id="international_weight" name="weight" placeholder="Weight (kg)" style="line-height:30px;" value="<?= post('weight') ? post('weight') : '' ?>">
                                                    <span id="errorMsg6"></span>
                                                </div>
                                                <div class="col-12 col-md-6 d-flex">
                                                    <button class="btn btn-sm btn-primary mb-auto international_btn" name="actionBtn" id="actionBtn" value="chkRate_i" style="width:100%">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Tabs content -->
                        </div>
                    </div>
                </div>

                <?php
                if (post('actionBtn')) {
                    $act = post('actionBtn');
                    switch ($act) {
                        case 'chkRate_d':
                            $active_d = "active";
                            $active_i = "";
                            $selected_d = "true";
                            $selected_i = "false";
                            $showActive_d = "show active";
                            $showActive_i = "";

                            $data = array();
                            $data['country'] = input('country');

                            $from_arr = post('from');

                            if ($from_arr) {
                                $from_arr = explode('|', $from_arr);
                                $data['from'] = $from_arr[0] ? $from_arr[0] : '';
                                $data['from_full'] = $from_arr[1] ? $from_arr[1] : '';
                            }

                            $data['postcode_from'] = post('postcode_from');

                            $to_arr = post('to');
                            if ($to_arr) {
                                $to_arr = explode('|', $to_arr);
                                $data['to'] = $to_arr[0] ? $to_arr[0] : '';
                                $data['to_full'] = $to_arr[1] ? $to_arr[1] : '';
                            }

                            $data['postcode_to'] = post('postcode_to');
                            $data['weight'] = post('weight');

                            if ($data['postcode_from'] && $data['postcode_to'] && $data['weight']) {
                                $rstRateCheck = rate_checking($data);
                                // audit log
                                $log = array();
                                $log['log_act'] = 'check';
                                $log['cdate'] = $cdate;
                                $log['ctime'] = $ctime;
                                $log['uid'] = $log['cby'] = USER_ID;
                                $log['act_msg'] = USER_NAME . " checking the price <b>" . $data['from_full'] . " to " . $data['to_full'] . "</b> from <b><i>" . $pageTitle . "</i></b>.";
                                $log['page'] = $pageTitle;
                                $log['connect'] = $connect;
                                audit_log($log);
                            }
                            break;

                        case 'chkRate_i':
                            $active_i = "active";
                            $active_d = "";
                            $selected_i = "true";
                            $selected_d = "false";
                            $showActive_i = "show active";
                            $showActive_d = "";

                            $data = array();
                            $data['country'] = input('country');

                            $from_arr = post('from');
                            if ($from_arr) {
                                $from_arr = explode('|', $from_arr);
                                $data['from'] = $from_arr[0] ? $from_arr[0] : '';
                                $data['from_full'] = $from_arr[1] ? $from_arr[1] : '';
                            }

                            $data['postcode_from'] = post('postcode_from');

                            $to_arr = post('to');
                            if ($to_arr) {
                                $to_arr = explode('|', $to_arr);
                                $data['to'] = $to_arr[0] ? $to_arr[0] : '';
                                $data['to_full'] = $to_arr[1] ? $to_arr[1] : '';
                            }

                            $data['postcode_to'] = post('postcode_to');
                            $data['weight'] = post('weight');

                            if ($data['postcode_from'] && $data['postcode_to'] && $data['weight']) {
                                $rstRateCheck = rate_checking($data);

                                // audit log
                                $log = array();
                                $log['log_act'] = 'check';
                                $log['cdate'] = $cdate;
                                $log['ctime'] = $ctime;
                                $log['uid'] = $log['cby'] = USER_ID;
                                $log['act_msg'] = USER_NAME . " checking the price <b>" . $data['from_full'] . " to " . $data['to_full'] . "</b> from <b><i>" . $pageTitle . "</i></b>.";
                                $log['page'] = $pageTitle;
                                $log['connect'] = $connect;
                                audit_log($log);
                            }
                            break;
                        default:
                    }
                }

                if (isset($rstRateCheck)) {
                    if ($rstRateCheck['api_status'] == 'Success') {
                        ob_start();
                ?>
                        <form id="courier_table" method="post" action="make_order.php">
                            <div class="px-4 py-4">
                                <div class="mb-3">
                                    <div class="form-group">
                                        <span class="title-form">Table</span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="rateCheckTable" class="table table-striped" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Courier Company</th>
                                                <th>Services</th>
                                                <th>Estimate Delivery Time</th>
                                                <th>Rates</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        <?php
                                        $num = 1;
                                        foreach ($rstRateCheck['result'] as $result => $a) {
                                            foreach ($a['rates'] as $rates => $b) {
                                                // filter
                                                if (isset($b['require_min_order']))
                                                    if ($b['require_min_order'] != 0)
                                                        continue;

                                                if (($b['service_detail'] == 'dropoff') && (empty($b['dropoff_point'])))
                                                    continue;

                                                // filter MY
                                                if ($data['from'] == 'MY' || $data['from'] == 'my') {
                                                    if (stripos($b['service_name'], "DHL") !== false)
                                                        continue;

                                                    if (stripos($b['service_name'], "Pgeon") !== false)
                                                        continue;
                                                }

                                                // filter SG
                                                if ($data['from'] == 'SG' || $data['from'] == 'sg') {
                                                    $SG_courier = ['J&T', 'Qxpress', 'Ninja', 'Singapore Post', 'Janio', 'Aramex'];
                                                    $exist = 0;

                                                    foreach ($SG_courier as $c) {
                                                        if (stripos($b['courier_name'], $c) !== false) {
                                                            $exist = 'found';
                                                            break;
                                                        }
                                                    }

                                                    if ($exist != 'found')
                                                        continue;
                                                }

                                                // take required value
                                                $tmp_arr = array();
                                                $tmp_arr['sid'] = $b['service_id'];
                                                $tmp_arr['service_detail'] = $b['service_detail'];
                                                $tmp_arr['courier_id'] = $b['courier_id'];
                                                $tmp_arr['courier_name'] = $b['courier_name'];
                                                $tmp_arr['courier_logo'] = $b['courier_logo'];
                                                $tmp_arr['dropoff_point'] = $b['dropoff_point'];
                                                $tmp_arr['pickup_point'] = $b['pickup_point'];
                                                $tmp_arr['from'] = $data['from'];
                                                $tmp_arr['from_full'] = $data['from_full'];
                                                $tmp_arr['postcode_from'] = $data['postcode_from'];
                                                $tmp_arr['to'] = $data['to'];
                                                $tmp_arr['to_full'] = $data['to_full'];
                                                $tmp_arr['postcode_to'] = $data['postcode_to'];
                                                $tmp_arr['weight'] = $data['weight'];
                                                $tmp_arr['price'] = $b['price'];
                                                $tmp_arr['pickup_date'] = $b['pickup_date'];
                                                $tmp_arr['currency'] = getCurrencyUnit($tmp_arr['from']);

                                                $service_detail = $b['service_detail'] == 'pickup' ? "Pickup" : "Dropoff";

                                                $row = '<tr>';
                                                $row .= '<td>' . $num . '</td>';
                                                $row .= '<td style="width:25%; white-space:break-spaces;"><img src="' . $b['courier_logo'] . '" name="' . $b['courier_name'] . '" style="width:75%;"><br>' . $b['courier_name'] . '</td>';
                                                $row .= '<td>' . $service_detail . '<br>(' . $b['service_name'] . ')' . '</td>';
                                                $row .= '<td>' . $b['delivery'] . '</td>';
                                                $row .= '<td>' . $tmp_arr['currency'] . ' ' . $b['price'] . '</td>';
                                                /* $row .= '<td><a class="btn btn-default btn-primary" href="make_order.php?sid='.$b['service_id'].'&courierName='.$b['courier_name'].'&fromCountry='.$data['from'].'&fromAreaFrom='.$data['area_from'].'&fromPostcode='.$data['postcode_from'].'&toCountry='.$data['to'].'&toAreaFrom='.$data['area_to'].'&toPostcode='.$data['postcode_to'].'&weight='.$data['weight'].'&pickupDate='.$b['pickup_date'].'&" id="actionBtn" name="actionBtn" style="color:#FFFFFF;">Book</a></td>'; */
                                                $row .= '<td><button class="btn btn-default btn-primary" type="submit" value="' . base64_encode(serialize(json_encode($tmp_arr))) . '" name="bookBtn" id="actionBtn">Book</button></td>';
                                                echo $row;
                                                $num++;
                                            }
                                        }
                                        echo ob_get_clean();
                                    }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    <?php
                }
                    ?>
            </div>
        </div>
    </div>
</body>

<script>
    //Initial Page And Action Value
    var page = "<?= $pageTitle ?>";
    var action = "<?php echo isset($act) ? $act : ''; ?>";

    checkCurrentPage(page, action);
    setButtonColor();


    $(".domestic_btn").on("click", function() {
        var err;

        if (!$("#postcode_from").val()) {
            $("#errorMsg1").html("<p style='color:red'>Please enter a postcode</p>");
            $("#errorMsg1").css("border-color", "red");
            err = 1;
        } else {
            $("#errorMsg").html("");
            $("#errorMsg1").css("border-color", "");
        }

        if (!$("#postcode_to").val()) {
            $("#errorMsg2").html("<p style='color:red'>Please enter a postcode</p>");
            $("#errorMsg2").css("border-color", "red");
            err = 1;
        } else {
            $("#errorMsg2").html("");
            $("#errorMsg2").css("border-color", "");
        }

        if (!$("#weight").val()) {
            $("#errorMsg3").html("<p style='color:red'>Please enter a weight</p>");
            $("#errorMsg3").css("border-color", "red");
            err = 1;
        } else {
            $("#errorMsg3").html("");
            $("#errorMsg3").css("border-color", "");
        }

        if (err) {
            event.preventDefault();
            $("#courier_table").hide();
        }
    });

    $(".international_btn").on("click", function() {
        var err;

        if (!$("#international_postcode_from").val()) {
            $("#errorMsg4").html("<p style='color:red'>Please enter a postcode</p>");
            $("#errorMsg4").css("border-color", "red");
            err = 1;
        } else {
            $("#errorMsg4").html("");
            $("#errorMsg4").css("border-color", "");
        }

        if (!$("#international_postcode_to").val()) {
            $("#errorMsg5").html("<p style='color:red'>Please enter a postcode</p>");
            $("#errorMsg5").css("border-color", "red");
            err = 1;
        } else {
            $("#errorMsg5").html("");
            $("#errorMsg5").css("border-color", "");
        }

        if (!$("#international_weight").val()) {
            $("#errorMsg6").html("<p style='color:red'>Please enter a weight</p>");
            $("#errorMsg6").css("border-color", "red");
            err = 1;
        } else {
            $("#errorMsg6").html("");
            $("#errorMsg6").css("border-color", "");
        }

        if (err) {
            event.preventDefault();
            $("#courier_table").hide();
        }
    });
</script>

</html>