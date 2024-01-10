<?php
$pageTitle = "Make Order";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$pinAccess = checkCurrentPin($connect, $pageTitle);
$pageAction = 'add';

if (!isActionAllowed($pageAction, $pinAccess))
    echo $redirectLink;

if (post('bookBtn')) {
    $courier_info = json_decode(unserialize(base64_decode(post('bookBtn'))), true);
    $_SESSION['makeorder_courier_info'] = $courier_info;
} else {
    $courier_info = isset($_SESSION['makeorder_courier_info']) ? $_SESSION['makeorder_courier_info'] : '';
}

$sid = $courier_info['sid'];
$redirect_page =  $SITEURL . '/rate_checking.php';

//CourierInfo
$service_detail = $courier_info['service_detail'];
$dropoff_point = $courier_info['dropoff_point'];
$courier_id = $courier_info['courier_id'];
$courier_name = $courier_info['courier_name'];
$courier_logo = $courier_info['courier_logo'];

//Parcel Send Place
$from = $courier_info['from'];
$from_full = isset($courier_info['from_full']) ? $courier_info['from_full'] : '';
$postcode_from = $courier_info['postcode_from'];

//Parcel Receive Place
$to = $courier_info['to'];
$to_full = isset($courier_info['to_full']) ? $courier_info['to_full'] : '';
$postcode_to = $courier_info['postcode_to'];

//Parcel Info
$weight = $courier_info['weight'];
$price = $courier_info['price'];
$currency = $courier_info['currency'];

//Country Phone Code
$country_telcode_from = getCountryTelCode($from, $connect);
$country_telcode_to = getCountryTelCode($to, $connect);

?>

<style>
    .input-group-text {
        max-width: 55px;
        width: 55px;
        display: flex;
        justify-content: center;
    }

    .invalid-msg {
        color: red;
    }
</style>

<script>
    preloader(500);
</script>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div class="my-3 container-fluid">

            <div class="d-flex flex-column mb-3 ms-3">
                <div class="row">
                    <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> Make Order</p>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-between flex-wrap">
                        <h2>Make Order</h2>
                    </div>
                </div>
            </div>

            <form class="row" id="makeOrderForm" method="post" action="" novalidate>
                <div class="col-12 col-md-8">
                    <!-- Sender Detail -->
                    <div class="col-12 mb-3">
                        <div class="row">
                            <div class="col-12">
                                <fieldset class="border p-2" style="border-radius: 3px;">
                                    <legend class="float-none w-auto p-2"></b>Sender Details</b></legend>
                                    <div class="px-4 py-2">
                                        <?php
                                        if (!(empty(array_filter($dropoff_point)))) {
                                        ?>
                                            <div class="row">
                                                <div class="has-validation col-12 col-md-12 mb-2">
                                                    <div class="input-group">
                                                        <select class="form-select" id="sender_dropoffpoint" name="sender_dropoffpoint" required>
                                                            <option value="" disabled selected style="display:none;">Select the dropoff point</option>
                                                            <?php
                                                            foreach ($dropoff_point as $point => $orderInfo) {
                                                                $key = $orderInfo['point_id'];
                                                                $value = $orderInfo['point_id'] . ' - ' . $orderInfo['point_name'] . ' - ' . $orderInfo['point_postcode'];
                                                                echo "<option value=\"$key\" >$value</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="invalid-msg">
                                                        Please select the dropoff point.
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>

                                        <div class="row">

                                            <div class="has-validation col-12 col-md-4 mb-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id=""><i class="mdi mdi-account"></i></span>
                                                    </div>
                                                    <input class="form-control" type="text" name="sender_name" id="sender_name" value="" placeholder="Sender Name" required>
                                                </div>

                                                <div class="invalid-msg">
                                                    Please enter the sender name.
                                                </div>
                                            </div>

                                            <div class="has-validation col-12 col-md-4 mb-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id=""><i class="mdi mdi-email"></i></span>
                                                    </div>
                                                    <input class="form-control" type="email" name="sender_email" id="sender_email" placeholder="Sender Email" value="<?= USER_EMAIL  ?>" required>
                                                </div>
                                                <span id="emailMsg1"></span>
                                                <div class="invalid-msg">
                                                    Please enter the sender email.
                                                </div>
                                            </div>

                                            <div class="has-validation col-12 col-md-4 mb-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="form-control" style="max-width: 80px; height: 36px; border-radius: none; text-align: center; display: inline-block;"><?= $country_telcode_from ?></span>
                                                    </div>
                                                    <input class="form-control" type="tel" name="sender_tel" id="sender_tel" placeholder="Sender ContactNum" required>
                                                </div>

                                                <div class="invalid-msg">
                                                    Please enter the sender contact number.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 col-md-4 mb-2">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" name="sender_country" id="sender_country" placeholder="Country" value="<?= isset($from_full) ? $from_full : ''; ?>" required readonly>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-4 mb-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id=""><i class="mdi mdi-domain"></i></span>
                                                    </div>
                                                    <input class="form-control" type="text" name="sender_company" id="sender_company" placeholder="Sender Company">
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-4 mb-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" style="max-width: 80px; height: 36px; border-radius: none; text-align: center; display: inline-block;">
                                                            <?= $country_telcode_from ?>
                                                        </span>
                                                    </div>

                                                    <input class="form-control" type="tel" name="sender_alt_tel" id="sender_alt_tel" placeholder="Sender Alt.ContactNum">
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        if ($from == 'SG') {
                                        ?>
                                            <div class="row">
                                                <div class="col-12 col-md-8 mb-2 has-validation">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="sender_addr_1" id="sender_addr_1" placeholder="Unit: eg #07-222" required>
                                                    </div>

                                                    <div class="invalid-msg">
                                                        Please enter the sender unit.
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-4 mb-2">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="sender_postcode" id="sender_postcode" placeholder="Postcode" value="<?= isset($postcode_from) ? $postcode_from : '' ?>" required readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="row">
                                                <div class="col-12 col-md-6 mb-2 has-validation">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="sender_addr_1" id="sender_addr_1" placeholder="Address Line 1" required>
                                                    </div>

                                                    <div class="invalid-msg">
                                                        Please enter the sender address.
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6 mb-2">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="sender_addr_2" id="sender_addr_2" placeholder="Address Line 2">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 col-md-8 mb-2 has-validation">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="sender_city" id="sender_city" placeholder="City" required>
                                                    </div>

                                                    <div class="invalid-msg">
                                                        Please enter the sender city.
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4 mb-2">
                                                    <div class="input-group">
                                                        <input class="form-control" type="text" name="sender_postcode" id="sender_postcode" placeholder="Postcode" value="<?= isset($postcode_from) ? $postcode_from : '' ?>" required readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                    <!-- Sender Detail -->

                    <!-- Receiver Detail -->
                    <div class="col-12 mb-3">
                        <div class="row">
                            <div class="col-12">
                                <fieldset class="border p-2" style="border-radius: 3px;">
                                    <legend class="float-none w-auto p-2"><b>Receiver Details</b></legend>
                                    <div class="px-4 py-2">

                                        <div class="row">
                                            <div class="col-12 col-md-4 mb-2 has-validation">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id=""><i class="mdi mdi-account"></i></span>
                                                    </div>
                                                    <input class="form-control" type="text" name="receiver_name" id="receiver_name" placeholder="Receiver Name" required>
                                                </div>
                                                <div class="invalid-msg">
                                                    Please enter the receiver name.
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-4 mb-2 has-validation"">
                                            <div class=" input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id=""><i class="mdi mdi-email"></i></span>
                                                </div>
                                                <input class="form-control" type="email" name="receiver_email" id="receiver_email" placeholder="Receiver Email" required>
                                            </div>
                                            <span id="emailMsg2"></span>
                                            <div class="invalid-msg">
                                                Please enter the receiver email.
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 mb-2 has-validation">
                                            <div class=" input-group">
                                                <div class="input-group-prepend">
                                                    <?php if (is_array($country_telcode_to)) : ?>
                                                        <span class="form-control" style="height: 35px; line-height: 35px; border-radius: none; text-align: center; display: inline-block;">
                                                            <?= implode(', ', $country_telcode_to) ?>
                                                        </span>
                                                    <?php else : ?>
                                                        <span class="form-control" style="max-width: 80px; height: 36px; border-radius: none; text-align: center; display: inline-block;">
                                                            <?= $country_telcode_to ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <input class="form-control" type="tel" name="receiver_tel" id="receiver_tel" placeholder="Receiver ContactNum" required>
                                            </div>
                                            <div class="invalid-msg">
                                                Please enter the receiver contact number.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-md-4 mb-2">
                                            <div class="input-group">
                                                <input class="form-control" type="text" name="receiver_country" id="receiver_country" placeholder="Country" value="<?= isset($to_full) ? $to_full : ''; ?>" required readonly>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id=""><i class="mdi mdi-domain"></i></span>
                                                </div>
                                                <input class="form-control" type="text" name="receiver_company" id="receiver_company" placeholder="Receiver Company">
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <?php if (is_array($country_telcode_to)) : ?>
                                                        <span class="form-control" style="height: 35px; line-height: 35px; border-radius: none; text-align: center; display: inline-block;">
                                                            <?= implode(', ', $country_telcode_to) ?>
                                                        </span>
                                                    <?php else : ?>
                                                        <span class="form-control" style="max-width: 80px; height: 36px; border-radius: none; text-align: center; display: inline-block;">
                                                            <?= $country_telcode_to ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <input class="form-control" type="tel" name="receiver_alt_tel" id="receiver_alt_tel" placeholder="Receiver Alt.ContactNum">
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if ($to == 'SG') {
                                    ?>
                                        <div class="row">
                                            <div class="has-validation col-12 col-md-6 mb-2">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" name="receiver_addr_1" id="sender_unit" placeholder="Unit: eg #07-222" required>
                                                </div>

                                                <div class="invalid-msg">
                                                    Please enter the receiver unit.
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6 mb-2">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" name="receiver_postcode" id="receiver_postcode" placeholder="Postcode" value="<?= isset($postcode_to) ? $postcode_to : '' ?>" readonly required>
                                                </div>
                                            </div>
                                        </div>

                                    <?php
                                    } else {
                                    ?>

                                        <div class="row">
                                            <div class="has-validation col-12 col-md-6 mb-2">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" name="receiver_addr_1" id="receiver_addr_1" placeholder="Address Line 1" required>
                                                </div>

                                                <div class="invalid-msg">
                                                    Please enter the receiver address.
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6 mb-2">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" name="receiver_addr_2" id="receiver_addr_2" placeholder="Address Line 2">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 col-md-6 mb-2 has-validation">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" name="receiver_city" id="receiver_city" placeholder="City" required>
                                                </div>

                                                <div class="invalid-msg" for="receiver_city">
                                                    Please enter the receiver city.
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6 mb-2">
                                                <div class="input-group">
                                                    <input class="form-control" type="text" name="receiver_postcode" id="receiver_postcode" placeholder="Postcode" value="<?= isset($postcode_to) ? $postcode_to : '' ?>" readonly required>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                            </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <!-- Receiver Detail -->

                <!-- Parcel Detail -->
                <div class="col-12 mb-3">
                    <div class="row">
                        <div class="col-12">
                            <fieldset class="border p-2" style="border-radius: 3px;">
                                <legend class="float-none w-auto p-2"><b>Parcel Details</b></legend>
                                <div class="px-4 py-2">
                                    <div class="row">
                                        <div class="has-validation col-12 col-md-12 mb-2">
                                            <div class="input-group">
                                                <input class="form-control" type="text" name="parcel_content" id="parcel_content" placeholder="Parcel Content" required>
                                            </div>
                                            <div class="invalid-msg">
                                                Please enter the parcel content
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-12 col-sm-4 mb-2 has-validation">
                                            <div class="input-group">
                                                <input class="form-control" type="date" name="pickup_date" id="pickup_date" placeholder="Pickup Date" required style="border-radius:3px;height: 40px;" data-toggle="tooltip" data-placement="right" title="Pickup Date">
                                            </div>

                                            <div class="invalid-msg">
                                                Please enter the pickup date
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-4 mb-2">
                                            <div class="input-group">
                                                <select class="form-select" aria-label="Default select example" name="curUnit" id="curUnit" style="border-radius:3px;height: 40px;" required data-toggle="tooltip" data-placement="right" title="Currency Unit">
                                                    <?php
                                                    $result = getData('*', '', '', CUR_UNIT, $connect);

                                                    while ($rowCurUnit = $result->fetch_assoc()) {
                                                        $selected = isset($price) && $currency == $rowCurUnit['unit'] ? "selected" : "";
                                                        echo "<option value='{$rowCurUnit['id']}' $selected>{$rowCurUnit['unit']}</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-4 mb-2 has-validation">
                                            <div class="input-group">
                                                <input class="form-control" type="number" step="any" name="parcel_value" id="parcel_value" placeholder="Parcel Value (Ex. 9.90)" required style="border-radius:3px;height: 40px;">
                                            </div>

                                            <div class="invalid-msg">
                                                Please enter the parcel value
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-md-12 mb-2">
                                            <div class="input-group">
                                                <input class="form-control" type="text" name="parcel_reference" id="parcel_reference" placeholder="Reference (optional)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
        </div>
        <!-- Parcel Detail -->

        <!-- Courier Detail -->
        <div class="col-12 col-md-4">
            <fieldset class="border p-2" style="border-radius: 3px;">
                <legend class="float-none w-auto p-2"><b>Courier Details</b></legend>
                <div class="px-4 py-2">
                    <div class="row">
                        <div class="col-12 text-center">
                            <img src="<?= isset($courier_logo) ? $courier_logo : ''; ?>" style="width:50%">
                        </div>
                    </div>
                    <hr class="hr" />
                    <div class="row">
                        <div class="col-12">
                            <span>
                                <?php
                                if (isset($service_detail)) {
                                    switch ($service_detail) {
                                        case 'pickup':
                                            echo '<span class="mdi mdi-truck" style="font-size:20px"><span style="font-size:15px"> Pick Up</span></span>';
                                            break;
                                        case 'dropoff':
                                            echo '<span class="mdi mdi-run" style="font-size:20px"><span style="font-size:15px"> Drop Off</span></span>';
                                            break;
                                    }
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex">
                            <div class="col-4 text-start">
                                <span><?= isset($from_full) ? $from_full : '' ?></span><br>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <span><i style="font-size:28px;" class="mdi mdi-ray-start-arrow"></i></span>
                            </div>
                            <div class="col-4 text-end">
                                <span><?= isset($to_full) ? $to_full : '' ?> </span><br>
                            </div>
                        </div>
                    </div>
                    <hr class="hr" />
                    <div class="row">
                        <div class="col-12 d-flex">
                            <span>Weight : <b><?= isset($weight) ? $weight : '' ?> kg</b></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex">
                            <span id="displayedPrice">Price : <b><?= isset($price) ? $currency . ' ' . $price : '' ?></b></span>
                        </div>
                    </div>
                    <hr class="hr" />
                    <div class="row">
                        <div class="col-6">
                            <div class="d-flex justify-content-center">
                                <a class="btn btn-default btn-primary" type="button" href="<?= "$SITEURL/rate_checking.php?country=" . urlencode($from) . "&postcodefrom=" . urlencode($postcode_from) . "&postcodeto=" . urlencode($postcode_to) .  "&from_full=" . urlencode($from_full) . "&to_full=" . urlencode($to_full); ?>" id="backBtn">Back</a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-default btn-primary" type="submit" value="makeOrder" name="actionBtn" id="actionBtn">Book</button>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    </div>
    <!-- Courier Detail -->
    </form>

    <?php
    if (post('actionBtn')) {
        $act = post('actionBtn');
        switch ($act) {
            case 'makeOrder':

                //Parcel Info
                $dataMO = array();
                $dataMO['weight'] = $weight;
                $dataMO['sid'] = $sid;
                $dataMO['content'] = postSpaceFilter('parcel_content');
                $dataMO['value'] = postSpaceFilter('parcel_value');
                $dataMO['collect_date'] = post('pickup_date');
                $dataMO['send_email'] = postSpaceFilter('receiver_email');
                $dataMO['reference'] = postSpaceFilter('parcel_reference');

                //Receiver Info
                $dataMO['pick_point'] = post('sender_dropoffpoint') ? post('sender_dropoffpoint') : '';
                $dataMO['pick_name'] = postSpaceFilter('sender_name');
                $dataMO['pick_company'] = post('sender_company');
                $dataMO['pick_contact'] = post('sender_tel_code') . postSpaceFilter('sender_tel');
                $dataMO['pick_mobile'] = post('sender_alt_tel_code') . postSpaceFilter('sender_alt_tel');
                $dataMO['pick_addr1'] = postSpaceFilter('sender_addr_1');
                $dataMO['pick_addr2'] = postSpaceFilter('sender_addr_2');
                $dataMO['pick_city'] = postSpaceFilter('sender_city');
                $dataMO['pick_code'] = post('sender_postcode');
                $dataMO['pick_country'] = $from;

                //Sender Info
                $dataMO['send_point'] = '';
                $dataMO['send_name'] = postSpaceFilter('receiver_name');
                $dataMO['send_company'] = post('receiver_company');
                $dataMO['send_contact'] = post('receiver_tel_code') . postSpaceFilter('receiver_tel');
                $dataMO['send_mobile'] = post('receiver_alt_tel_code') . postSpaceFilter('receiver_alt_tel');
                $dataMO['send_addr1'] = postSpaceFilter('receiver_addr_1');
                $dataMO['send_addr2'] = postSpaceFilter('receiver_addr_2');
                $dataMO['send_city'] = postSpaceFilter('receiver_city');
                $dataMO['send_code'] = post('receiver_postcode');
                $dataMO['send_country'] = $to;

                // get unit
                $currency_unit = getCurrencyUnit($from);
                $currency_unit_id = getData('*', "unit='$currency_unit'", '', CUR_UNIT, $connect);
                $currency_unit_id = $currency_unit_id->fetch_assoc();
                $currency_unit_id = $currency_unit_id['id'];

                $rstMakeOrder = make_order($dataMO);

                if (isset($rstMakeOrder)) {
                    $dataMOP = array();
                    if ($rstMakeOrder['api_status'] == 'Success') {
                        foreach ($rstMakeOrder['result'] as $result => $orderInfo) {
                            if ($orderInfo['status'] == 'Success') {
                                $dataMOP['order_number'] = $orderInfo['order_number'];
                                $dataMOP['country'] = $from;
                                $dataMOP['price'] = $orderInfo['price'];
                                $dataMOP['remarks'] = $orderInfo['remarks'];
                                $dataMOP['collect_date'] = $orderInfo['collect_date'];
                                $pickup_time = "09:00:00";
                            }
                        }
                    }

                    foreach ($rstMakeOrder['result'] as $result => $orderInfo) {
                        if ($orderInfo['status'] != 'fail') {
                            $rstMakeOrderPayment = make_order_payment($dataMOP);
                        } else {
                            echo  '<script>
                        window.alert("' . $orderInfo['remarks'] . '");
                      </script>
                      ';
                        }
                    }
                }

                if (isset($rstMakeOrderPayment)) {
                    if ($rstMakeOrderPayment['api_status'] == 'Success') {
                        foreach ($rstMakeOrderPayment['result'] as $result => $orderInfo) {
                            $message = $orderInfo['messagenow'];
                            switch ($from) {
                                case 'MY':
                                case 'my':
                                    foreach ($orderInfo['parcel'] as $parcel => $b) {
                                        $parcel_no = $b['parcelno'];
                                        $awb = $b['awb'];
                                    }
                                    break;
                                case 'SG':
                                case 'sg':
                                    $b = $orderInfo['parcel'];
                                    $parcel_no = $b['parcelno'];
                                    $awb = $b['awb'];
                                    break;
                            }
                        }
                    }
                }

                if (isset($parcel_no) && isset($awb)) {
                    if ($courier_id) {
                        try {

                            // check courier id
                            $rstcourierchk = $connect->query("SELECT * FROM " . COURIER . " WHERE id='$courier_id'");

                            if ($rstcourierchk->num_rows == 0) {

                                $insertCourierQry = "INSERT INTO " . COURIER . " (id,name,create_date,create_time,create_by) VALUES ('$courier_id','$courier_name',curdate(),curtime(),'" . USER_ID . "')";
                                $connect->query($insertCourierQry);

                                $log = $datafield = $courierNewvalarr = array();

                                if ($courier_name) {
                                    array_push($courierNewvalarr, $courier_name);
                                    array_push($datafield, 'name');
                                }

                                $log = [
                                    'log_act'      => $pageAction,
                                    'cdate'        => $cdate,
                                    'ctime'        => $ctime,
                                    'uid'          => USER_ID,
                                    'cby'          => USER_ID,
                                    'query_rec'    => $insertCourierQry,
                                    'query_table'  => COURIER,
                                    'page'         => $pageTitle,
                                    'connect'      => $connect,
                                    'newval'       => implodeWithComma($courierNewvalarr),
                                    'act_msg'      => actMsgLog($courier_id, $datafield, $courierNewvalarr, '', '', COURIER, $pageAction, ''),
                                ];

                                audit_log($log);
                            }

                            // insert customer
                            $insertCust = "INSERT INTO " . CUST . " (name,company_name,address_1,address_2,postcode,contact,alt_contact,email,country,create_date,create_time,create_by) VALUES ('" . $dataMO['send_name'] . "','" . $dataMO['send_company'] . "','" . $dataMO['send_addr1'] . "','" . $dataMO['send_addr2'] . "','" . $dataMO['send_code'] . "','" . $dataMO['send_contact'] . "','" . $dataMO['send_mobile'] . "','" . $dataMO['send_email'] . "','" . $dataMO['send_country'] . "',curdate(),curtime()," . USER_ID . ")";
                            $connect->query($insertCust);
                            $dataID = $connect->insert_id;
                            $cust_id = $dataID;

                            // audit log
                            $log = $datafield = $custNewvalarr = array();

                            $custVariables = [
                                'name' => 'send_name',
                                'company_name' => 'send_company',
                                'address_1' => 'send_addr1',
                                'address_2' => 'send_addr2',
                                'postcode' => 'send_code',
                                'contact' => 'send_contact',
                                'alt_contact' => 'send_mobile',
                                'email' => 'send_email',
                                'country' => 'send_country',
                            ];

                            foreach ($custVariables as $variable => $value) {
                                if ($dataMO[$value]) {
                                    array_push($custNewvalarr, $dataMO[$value]);
                                    array_push($datafield, $variable);
                                }
                            }

                            $log = [
                                'log_act'      => $pageAction,
                                'cdate'        => $cdate,
                                'ctime'        => $ctime,
                                'uid'          => USER_ID,
                                'cby'          => USER_ID,
                                'query_rec'    => $insertCust,
                                'query_table'  => CUST,
                                'page'         => $pageTitle,
                                'connect'      => $connect,
                                'newval'       => implodeWithComma($custNewvalarr),
                                'act_msg'      => actMsgLog($dataID, $datafield, $custNewvalarr, '', '', CUST, $pageAction, ''),
                            ];

                            audit_log($log);

                            // insert shipping request
                            $insertShipReq = "INSERT INTO " . SHIPREQ . " (order_no,customer_id,courier_id,awb,shipping_cost,currency_unit,shipping_type_id,parcel_content,parcel_value,weight,pickup_date,pickup_time,create_date,create_time,create_by) VALUES ('" . $dataMOP['order_number'] . "','$cust_id','$courier_id','$awb','" . $dataMOP['price'] . "','$currency_unit_id','$service_detail','" . $dataMO['content'] . "','" . $dataMO['value'] . "','$weight','" . $dataMOP['collect_date'] . "','$pickup_time',curdate(),curtime(),'" . USER_ID . "')";
                            $connect->query($insertShipReq);
                            $dataID = $connect->insert_id;

                            // audit log
                            $log = $datafield = $shipNewvalarr = array();

                            $shipVariables = [
                                'order_no' => $dataMOP['order_number'],
                                'customer_id' => $cust_id,
                                'courier_id' => $courier_id,
                                'awb' => $awb,
                                'shipping_cost' => $dataMOP['price'],
                                'currency_unit' => $currency_unit_id,
                                'shipping_type_id' => $service_detail,
                                'parcel_content' => $dataMO['content'],
                                'parcel_value' => $dataMO['value'],
                                'weight' => $weight,
                                'pickup_date' => $dataMOP['collect_date'],
                                'pickup_time' => $pickup_time,
                            ];

                            foreach ($shipVariables as $variable => $value) {
                                if ($value) {
                                    array_push($shipNewvalarr, $value);
                                    array_push($datafield, $variable);
                                }
                            }

                            $log = [
                                'log_act'      => $pageAction,
                                'cdate'        => $cdate,
                                'ctime'        => $ctime,
                                'uid'          => USER_ID,
                                'cby'          => USER_ID,
                                'query_rec'    => $insertShipReq,
                                'query_table'  => SHIPREQ,
                                'page'         => $pageTitle,
                                'connect'      => $connect,
                                'newval'       => implodeWithComma($shipNewvalarr),
                                'act_msg'      => actMsgLog($dataID, $datafield, $shipNewvalarr, '', '', SHIPREQ, $pageAction, ''),
                            ];

                            audit_log($log);

                            $_SESSION['tempValConfirmBox'] = true;
                            $act = "MO";
                        } catch (Exception $e) {

                            $errorMsg = $e->getMessage();

                            $act = "F";
                            $_SESSION['tempValConfirmBox'] = true;

                            $log = [
                                'log_act'     => $pageAction,
                                'cdate'       => $cdate,
                                'ctime'       => $ctime,
                                'uid'         => USER_ID,
                                'cby'         => USER_ID,
                                'act_msg'     => USER_NAME . " fail to complete make order [" . $dataMOP['order_number'] . "] due to (" . $errorMsg . ")",
                                'query_rec'   => '',
                                'query_table' => '',
                                'page'        => $pageTitle,
                                'newval'      => '',
                                'connect'     => $connect,
                            ];

                            audit_log($log);
                        }
                    }
                }
                break;
            case 'back':
                echo ("$unsetVariable = 'tempValConfirmBox'; <script>unset($_SESSION[$unsetVariable]);</script>");
                echo ("<script>location.href = 'rate_checking.php?country=$from';</script>");
                break;
        }
    }

    if (isset($_SESSION['tempValConfirmBox'])) {
        unset($_SESSION['tempValConfirmBox']);
        echo '<script>localStorage.clear();</script>';
        echo '<script>confirmationDialog("","Shipping Request For Order Number [' . $dataMOP['order_number'] . ']","' . $pageTitle . '","","' . $redirect_page . '","' . $act . '");</script>';
    }
    ?>

    </div>
</body>

<script>
    setButtonColor();

    document.addEventListener("DOMContentLoaded", function() {

        const hasValidationElements = document.querySelectorAll('.has-validation');

        hasValidationElements.forEach(function(container) {
            const inputField = container.querySelector('input, select');
            const errorMessage = container.querySelector('.invalid-msg');

            if (inputField && inputField.hasAttribute('required')) {
                errorMessage.style.display = 'none';

                inputField.addEventListener('input', function() {
                    if (inputField.validity.valid) {
                        errorMessage.style.display = 'none';
                        inputField.style.borderColor = '';
                    } else {
                        errorMessage.style.display = 'block';
                    }
                });
            }
        });

        document.getElementById('makeOrderForm').addEventListener('submit', function(event) {
            hasValidationElements.forEach(function(container) {
                const inputField = container.querySelector('input, select');
                const errorMessage = container.querySelector('.invalid-msg');

                if (inputField && !inputField.validity.valid) {
                    errorMessage.style.display = 'block';
                    inputField.style.borderColor = 'red';
                    event.preventDefault();
                }
            });
        });
    });

    $(document).ready(function() {
        $("#sender_email").on("input", function() {
            if (!validateEmail('#sender_email')) {
                $("#emailMsg1").html("<p class='text-danger'>Invalid Email Format</p>");
            } else {
                $("#emailMsg1").html("");
            }
        });

        $("#receiver_email").on("input", function() {
            if (!validateEmail('#receiver_email')) {
                $("#emailMsg2").html("<p class='text-danger'>Invalid Email Format</p>");
            } else {
                $("#emailMsg2").html("");
            }
        });

        $("#actionBtn").on("click", function(event) {
            if (!validateEmail('#sender_email') || !validateEmail('#receiver_email')) {
                event.preventDefault();
            }
        });
    });

    function validateEmail(inputID) {
        // get value of input email
        var email = $(inputID).val();
        // use reular expression
        var reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/
        if (reg.test(email)) {
            return true;
        } else {
            return false;
        }
    }
</script>

</html>