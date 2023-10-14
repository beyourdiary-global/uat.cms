<!DOCTYPE html>
<html>
<head>
<?php 
$pageTitle = "Make Order";
include 'menuHeader.php';
?>
<link rel="stylesheet" href="./css/main.css">
</head>

<?php
if(post('bookBtn'))
{
    $courier_info = json_decode(unserialize(base64_decode(post('bookBtn'))), true);
    $_SESSION['makeorder_courier_info'] = $courier_info;
} else {
    $courier_info = isset($_SESSION['makeorder_courier_info']) ? $_SESSION['makeorder_courier_info'] : '';
}

$sid = $courier_info['sid'];
/* echo $sid;  // delete */
$service_detail = $courier_info['service_detail'];
$dropoff_point = $courier_info['dropoff_point'];
$courier_id = $courier_info['courier_id'];
$courier_name = $courier_info['courier_name'];
$courier_logo = $courier_info['courier_logo'];

$from = $courier_info['from'];
$from_full = isset($courier_info['from_full']) ? $courier_info['from_full'] : '';

$area_from = $courier_info['area_from'];
$area_from_full = $courier_info['area_from_full'];

$postcode_from = $courier_info['postcode_from'];

$to = $courier_info['to'];
$to_full = isset($courier_info['to_full']) ? $courier_info['to_full'] : '';

$area_to = $courier_info['area_to'];
$area_to_full = $courier_info['area_to_full'];

$postcode_to = $courier_info['postcode_to'];

$weight = $courier_info['weight'];
$pickup_date = $courier_info['pickup_date'];
$price = $courier_info['price'];
$currency = $courier_info['currency'];
$country_telcode_from = getCountryTelCode($from);
$country_telcode_to = getCountryTelCode($to);

if(post('actionBtn'))
{
    $act = post('actionBtn');
    switch($act)
    {
        case 'makeOrder':
            $dataMO = array();
            $dataMO['weight'] = $weight;
            $dataMO['content'] = postSpaceFilter('parcel_content');
            $dataMO['value'] = postSpaceFilter('parcel_value');
            $dataMO['sid'] = $sid;
            $dataMO['pick_point'] = post('sender_dropoffpoint') ? post('sender_dropoffpoint') : '';
            $dataMO['pick_name'] = postSpaceFilter('sender_name');
            $dataMO['pick_company'] = post('sender_company');
            $dataMO['pick_contact'] = post('sender_tel_code') . postSpaceFilter('sender_tel');
            $dataMO['pick_mobile'] = post('sender_alt_tel_code') . postSpaceFilter('sender_alt_tel');
            $dataMO['pick_addr1'] = postSpaceFilter('sender_addr_1');
            $dataMO['pick_addr2'] = postSpaceFilter('sender_addr_2');
            $dataMO['pick_city'] = postSpaceFilter('sender_city');
            $dataMO['pick_state'] = $area_from;
            $dataMO['pick_code'] = post('sender_postcode');
            $dataMO['pick_country'] = $from;
            $dataMO['send_point'] = '';
            $dataMO['send_name'] = postSpaceFilter('receiver_name');
            $dataMO['send_company'] = post('receiver_company');
            $dataMO['send_contact'] = post('receiver_tel_code') . postSpaceFilter('receiver_tel');
            $dataMO['send_mobile'] = post('receiver_alt_tel_code') . postSpaceFilter('receiver_alt_tel');
            $dataMO['send_addr1'] = postSpaceFilter('receiver_addr_1');
            $dataMO['send_addr2'] = postSpaceFilter('receiver_addr_2');
            $dataMO['send_city'] = postSpaceFilter('receiver_city');
            $dataMO['send_state'] = $area_to;
            $dataMO['send_code'] = post('receiver_postcode');
            $dataMO['send_country'] = $to;
            $dataMO['collect_date'] = $pickup_date;
            $dataMO['send_email'] = postSpaceFilter('receiver_email');
            $dataMO['reference'] = postSpaceFilter('parcel_reference');

            // get unit
            $currency_unit = getCurrencyUnit($from);
            $currency_unit_id = getData('*',"unit='$currency_unit'",CUR_UNIT,$connect);
            $currency_unit_id = $currency_unit_id->fetch_assoc();
            $currency_unit_id = $currency_unit_id['id'];

            $rstMakeOrder = make_order($dataMO);
            /* var_dump($rstMakeOrder);  // delete */
            if(isset($rstMakeOrder))
            {
                $dataMOP = array();
                if($rstMakeOrder['api_status'] == 'Success')
                {
                    foreach($rstMakeOrder['result'] as $result => $a)
                    {
                        if($a['status'] == 'Success')
                        {
                            $dataMOP['order_number'] = $a['order_number'];
                            $dataMOP['country'] = $from;
                            $dataMOP['price'] = $a['price'];
                            $dataMOP['remarks'] = $a['remarks'];
                            $dataMOP['collect_date'] = $a['collect_date'];
                            $pickup_time = "09:00:00";
                        }
                        
                    }
                }

                foreach($rstMakeOrder['result'] as $result => $a)
                {
                    if($a['status'] != 'fail')
                    {
                        $rstMakeOrderPayment = make_order_payment($dataMOP);
                    }
                }
            }

            if(isset($rstMakeOrderPayment))
            {
                if($rstMakeOrderPayment['api_status'] == 'Success')
                {
                    foreach($rstMakeOrderPayment['result'] as $result => $a)
                    {
                        $message = $a['messagenow'];
                        switch($from)
                        {
                            case 'MY': case 'my':
                                foreach($a['parcel'] as $parcel => $b)
                                {
                                    $parcel_no = $b['parcelno'];
                                    $awb = $b['awb'];
                                }
                                break;
                            case 'SG': case 'sg':
                                var_dump($rstMakeOrderPayment['result']);
                                var_dump($a['parcel']);
                                $b = $a['parcel'];
                                $parcel_no = $b['parcelno'];
                                $awb = $b['awb'];
                                break;
                        }
                    }
                }
            }

            if(isset($parcel_no) && isset($awb))
            {
                try
                {
                    if($courier_id)
                    {
                        // check courier id
                        $rstcourierchk = $connect->query("SELECT * FROM ".COURIER." WHERE id='$courier_id'");
                        if($rstcourierchk->num_rows == 0)
                        {
                            $insertCourierQry = "INSERT INTO ".COURIER." (id,name,create_date,create_time,create_by) VALUES ('$courier_id','$courier_name',curdate(),curtime(),'".USER_ID."')";
                            $connect->query($insertCourierQry);
                        }

                        // insert customer
                        $insertCust = "INSERT INTO ".CUST." (name,company_name,address_1,address_2,postcode,contact,alt_contact,email,state,country,create_date,create_time,create_by) VALUES ('".$dataMO['send_name']."','".$dataMO['send_company']."','".$dataMO['send_addr1']."','".$dataMO['send_addr2']."','".$dataMO['send_code']."','".$dataMO['send_contact']."','".$dataMO['send_mobile']."','".$dataMO['send_email']."','".$dataMO['send_state']."','".$dataMO['send_country']."',curdate(),curtime(),".USER_ID.")";
                        $connect->query($insertCust);
                        $cust_id = mysqli_insert_id($connect);

                        // audit log
                        $newvalarr = array();
                       
                        if($dataMO['send_name'] != '')
                            array_push($newvalarr, $dataMO['send_name']);

                        if($dataMO['send_company'] != '')
                            array_push($newvalarr, $dataMO['send_company']);

                        if($dataMO['send_addr1'] != '')
                            array_push($newvalarr, $dataMO['send_addr1']);

                        if($dataMO['send_addr2'] != '')
                            array_push($newvalarr, $dataMO['send_addr2']);

                        if($dataMO['send_code'] != '')
                            array_push($newvalarr, $dataMO['send_code']);

                        if($dataMO['send_contact'] != '')
                            array_push($newvalarr, $dataMO['send_contact']);

                        if($dataMO['send_mobile'] != '')
                            array_push($newvalarr, $dataMO['send_mobile']);

                        if($dataMO['send_email'] != '')
                            array_push($newvalarr, $dataMO['send_email']);

                        if($dataMO['send_state'] != '')
                            array_push($newvalarr, $dataMO['send_state']);

                        if($dataMO['send_country'] != '')
                            array_push($newvalarr, $dataMO['send_country']);

                        $newval = implode(",",$newvalarr);
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added [id=$cust_id] " .$dataMO['send_name'] . " into Customer Table.";
                        $log['query_rec'] = $insertCust;
                        $log['query_table'] = CUST;
                        $log['page'] = 'Make Order';
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);

                        // insert shipping request
                        $insertShipReq = "INSERT INTO ".SHIPREQ." (order_no,customer_id,courier_id,awb,shipping_cost,currency_unit,shipping_type_id,parcel_content,parcel_value,weight,pickup_date,pickup_time,create_date,create_time,create_by) VALUES ('".$dataMOP['order_number']."','$cust_id','$courier_id','$awb','".$dataMOP['price']."','$currency_unit_id','$service_detail','".$dataMO['content']."','".$dataMO['value']."','$weight','".$dataMOP['collect_date']."','$pickup_time',curdate(),curtime(),'".USER_ID."')";
                        $connect->query($insertShipReq);
                        $shipreq_id = mysqli_insert_id($connect);
                        $_SESSION['tempValConfirmBox'] = true;

                        // audit log
                        $newvalarr = array();
                       
                        if($dataMOP['order_number'] != '')
                            array_push($newvalarr, $dataMOP['order_number']);

                        if($cust_id != '')
                            array_push($newvalarr, $cust_id);

                        if($courier_id != '')
                            array_push($newvalarr, $courier_id);

                        if($awb != '')
                            array_push($newvalarr, $awb);

                        if($dataMOP['price'] != '')
                            array_push($newvalarr, $dataMOP['price']);

                        if($currency_unit_id != '')
                            array_push($newvalarr, $currency_unit_id);

                        if($service_detail != '')
                            array_push($newvalarr, $service_detail);

                        if($dataMO['content'] != '')
                            array_push($newvalarr, $dataMO['content']);

                        if($dataMO['value'] != '')
                            array_push($newvalarr, $dataMO['value']);

                        if($weight != '')
                            array_push($newvalarr, $weight);

                        if($dataMOP['collect_date'] != '')
                            array_push($newvalarr, $dataMOP['collect_date']);

                        if($pickup_time != '')
                            array_push($newvalarr, $pickup_time);

                        $newval = implode(",",$newvalarr);
                        $log = array();
                        $log['log_act'] = 'add';
                        $log['cdate'] = $cdate;
                        $log['ctime'] = $ctime;
                        $log['uid'] = $log['cby'] = USER_ID;
                        $log['act_msg'] = USER_NAME . " added [id=$shipreq_id] " .$dataMOP['order_number'] . " into Shipping Request Table.";
                        $log['query_rec'] = $insertShipReq;
                        $log['query_table'] = SHIPREQ;
                        $log['page'] = 'Make Order';
                        $log['newval'] = $newval;
                        $log['connect'] = $connect;
                        audit_log($log);
                    }
                } catch (Exception $e) {
                    echo 'Message: ' . $e->getMessage();
                }
            }
            break;
        case 'back':
            echo("<script>location.href = 'rate_checking.php?country=$from';</script>");
            break;
    }
}
?>

<style>
@media (max-width: 768px) {
    /* .form-width {
        width:100%;
    } */
}

.title-form {
    font-size: 28px;
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

.input-group-text {
    max-width: 55px;
    width: 55px;
    display: flex;
    justify-content: center;
}   
</style>

<body>

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

    <form class="row needs-validation" id="makrOrderForm" method="post" action="make_order.php" novalidate>
        <div class="col-12 col-md-8">
            <!-- Sender Detail -->
            <div class="col-12 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="chkRate">
                            <div class="px-4 py-4">
                                <div class="form-group mb-2">
                                    <span class="title-form">Sender details</span>
                                </div>

                                <?php
                                if(!(empty(array_filter($dropoff_point))))
                                {
                                ?>
                                <div class="row">
                                    <div class="col-12 col-md-12 mb-2">
                                        <div class="input-group has-validation">
                                            <select class="form-select" id="sender_dropoffpoint" name="sender_dropoffpoint" required>
                                                <option value="" disabled selected style="display:none;">Select the dropoff point</option>
                                                <?php
                                                    foreach($dropoff_point as $point => $a)
                                                    {
                                                        $key = $a['point_id'];
                                                        $value = $a['point_id'] . ' - ' . $a['point_name'] . ' - ' . $a['point_postcode'];
                                                        echo "<option value=\"$key\" >$value</option>";
                                                    }
                                                ?>
                                            </select>
                                            <div class="invalid-tooltip">
                                                Please select the dropoff point.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                }
                                ?>

                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id=""><i class="mdi mdi-account"></i></span>
                                            </div>
                                            <input class="form-control" type="text" name="sender_name" id="sender_name" placeholder="Sender Name" value="" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender name.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id=""><i class="mdi mdi-email"></i></span>
                                            </div>
                                            <input class="form-control" type="text" name="sender_email" id="sender_email" placeholder="Sender Email" value="<?= USER_EMAIL  ?>" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender email.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <div class="input-group-prepend">
                                                <input class="form-control" style="max-width: 55px;" type="text" name="sender_tel_code" id="sender_tel_code" value="<?= $country_telcode_from ?>" readonly>
                                            </div>
                                            <input class="form-control" type="tel" name="sender_tel" id="sender_tel" placeholder="Sender ContactNum" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender contact number.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="sender_country" id="sender_country" placeholder="Country" value="<?= isset($from_full) ? $from_full : '';?>" required readonly>
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
                                                <input class="form-control" style="max-width: 55px;" type="text" name="sender_alt_tel_code" id="sender_alt_tel_code" value="<?= $country_telcode_from ?>" readonly>
                                            </div>
                                            <input class="form-control" type="tel" name="sender_alt_tel" id="sender_alt_tel" placeholder="Sender Alt.ContactNum">
                                        </div>
                                    </div>
                                </div>

                                <?php
                                if($from == 'SG')
                                {
                                ?>
                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="sender_addr_1" id="sender_addr_1" placeholder="Unit: eg #07-222" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender unit.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="sender_postcode" id="sender_postcode" placeholder="Postcode" value="<?= isset($postcode_from) ? $postcode_from : '' ?>" required readonly>
                                            <div class="invalid-tooltip">
                                                Please enter the sender postcode.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="sender_area" id="sender_area" placeholder="<?= $from == 'SG' ? 'Zone' : 'State' ?>" value="<?= isset($area_from_full) ? $area_from_full : '' ?>" required readonly>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                }
                                else
                                {
                                ?>
                                <div class="row">
                                    <div class="col-12 col-md-8 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="sender_addr_1" id="sender_addr_1" placeholder="Address Line 1" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender address.
                                            </div>
                                        </div>
                                    </div>
                                        
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="sender_addr_2" id="sender_addr_2" placeholder="Address Line 2">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="sender_city" id="sender_city" placeholder="City" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender city.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="sender_postcode" id="sender_postcode" placeholder="Postcode" value="<?= isset($postcode_from) ? $postcode_from : '' ?>" required readonly>
                                            <div class="invalid-tooltip">
                                                Please enter the sender postcode.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="sender_area" id="sender_area" placeholder="<?= $from == 'SG' ? 'Zone' : 'State' ?>" value="<?= isset($area_from_full) ? $area_from_full : '' ?>" required readonly>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sender Detail -->

            <!-- Receiver Detail -->
            <div class="col-12 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="chkRate">
                            <div class="px-4 py-4">
                                <div class="form-group mb-2">
                                    <span class="title-form">Receiver details</span>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id=""><i class="mdi mdi-account"></i></span>
                                            </div>
                                            <input class="form-control" type="text" name="receiver_name" id="receiver_name" placeholder="Receiver Name" required>
                                            <div class="invalid-tooltip">
                                                Please enter the receiver name.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                    <div class="input-group has-validation">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id=""><i class="mdi mdi-email"></i></span>
                                            </div>
                                            <input class="form-control" type="text" name="receiver_email" id="receiver_email" placeholder="Receiver Email" required>
                                            <div class="invalid-tooltip">
                                                Please enter the receiver email.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <div class="input-group-prepend">
                                            <?php
                                                if(is_array($country_telcode_to))
                                                {
                                                ?>
                                                <select class="form-select" style="height:35px;" name="receiver_tel_code" id="receiver_tel_code">
                                                <?php
                                                for($i=0;$i<sizeof($country_telcode_to);$i++)
                                                {
                                                    echo "<option value=\"".$country_telcode_to[$i]."\">".$country_telcode_to[$i]."</option>";
                                                }
                                                ?>
                                                </select>
                                                <?php
                                                }
                                                else
                                                {
                                                ?>
                                                <input class="form-control" style="max-width: 90px;" type="text" name="receiver_tel_code" id="receiver_tel_code" value="<?= $country_telcode_to ?>" readonly>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                            <input class="form-control" type="tel" name="receiver_tel" id="receiver_tel" placeholder="Receiver ContactNum" required>
                                            <div class="invalid-tooltip">
                                                Please enter the receiver contact number.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="receiver_country" id="receiver_country" placeholder="Country" value="<?= isset($to_full) ? $to_full : '';?>" required readonly>
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
                                                <?php
                                                if(is_array($country_telcode_to))
                                                {
                                                ?>
                                                <select class="form-select" style="height:35px;" name="receiver_alt_tel_code" id="receiver_alt_tel_code">
                                                <?php
                                                for($i=0;$i<sizeof($country_telcode_to);$i++)
                                                {
                                                    echo "<option value=\"".$country_telcode_to[$i]."\">".$country_telcode_to[$i]."</option>";
                                                }
                                                ?>
                                                </select>
                                                <?php
                                                }
                                                else
                                                {
                                                ?>
                                                <input class="form-control" type="text" style="max-width: 90px;" name="receiver_alt_tel_code" id="receiver_alt_tel_code" value="<?= $country_telcode_to ?>" readonly>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                            <input class="form-control" type="tel" name="receiver_alt_tel" id="receiver_alt_tel" placeholder="Receiver Alt.ContactNum">
                                        </div>
                                    </div>
                                </div>

                                <?php
                                if($to == 'SG')
                                {
                                ?>
                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="receiver_addr_1" id="sender_unit" placeholder="Unit: eg #07-222" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender unit.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="receiver_postcode" id="receiver_postcode" placeholder="Postcode" value="<?= isset($postcode_to) ? $postcode_to : '' ?>" required>
                                            <div class="invalid-tooltip">
                                                Please enter the receiver postcode.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="tel" name="receiver_area" id="receiver_area" placeholder="<?= $from == 'SG' ? 'Zone' : 'State' ?>" value="<?= isset($area_to) ? $area_to : '' ?>" required readonly>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                }
                                else
                                {
                                ?>
                                <div class="row">
                                    <div class="col-12 col-md-8 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="receiver_addr_1" id="receiver_addr_1" placeholder="Address Line 1" required>
                                            <div class="invalid-tooltip">
                                                Please enter the sender address.
                                            </div>
                                        </div>
                                    </div>
                                        
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="receiver_addr_2" id="receiver_addr_2" placeholder="Address Line 2">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="receiver_city" id="receiver_city" placeholder="City">
                                            <div class="invalid-tooltip" required>
                                                Please enter the receiver city.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group has-validation">
                                            <input class="form-control" type="text" name="receiver_postcode" id="receiver_postcode" placeholder="Postcode" value="<?= isset($postcode_to) ? $postcode_to : '' ?>" required>
                                            <div class="invalid-tooltip">
                                                Please enter the receiver postcode.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="tel" name="receiver_area" id="receiver_area" placeholder="<?= $from == 'SG' ? 'Zone' : 'State' ?>" value="<?= isset($area_to) ? $area_to : '' ?>" required <?= $area_to == '' ? '' : 'readonly' ?>>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Receiver Detail -->

            <!-- Parcel Detail -->
            <div class="col-12 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="chkRate">
                            <div class="px-4 py-4">
                                <div class="form-group mb-2">
                                    <span class="title-form">Parcel details</span>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-md-8 mb-2">
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="parcel_content" id="parcel_content" placeholder="Parcel Content" required>
                                            <div class="invalid-tooltip">
                                                Please enter the parcel content.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="">RM</span>
                                            </div>
                                            <input class="form-control" min="0" step=".01" type="number" name="parcel_value" id="parcel_value" placeholder="Parcel Value (Ex. 9.90)" required>
                                            <div class="invalid-tooltip" id="parcel-value_err">
                                                Please enter the value of the items.
                                            </div>
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
                        </div>
                    </div>
                </div>
            </div>
            <!-- Parcel Detail -->
        </div>

        <div class="col-12 col-md-4">
            <div class="chkRate" id="">
                <div class="px-4 py-4">
                    <div class="mb-2">
                        <span class="title-form">Courier Details</span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <img src="<?= isset($courier_logo) ? $courier_logo : ''; ?>" style="width:50%">
                        </div>
                    </div>
                    <hr class="hr" />
                    <div class="row">
                        <div class="col-12">
                            <span>
                                <?php
                                    if(isset($service_detail))
                                    {
                                        switch($service_detail)
                                        {
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
                                <span><?= isset($area_from_full) ? $area_from_full : '' ?></span>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <span><i style="font-size:28px;" class="mdi mdi-ray-start-arrow"></i></span>
                            </div>
                            <div class="col-4 text-end">
                                <span><?= isset($to_full) ? $to_full : '' ?></span><br>
                                <span><?= isset($area_to_full) ? $area_to_full : '' ?></span>
                            </div>
                        </div>
                    </div>
                    <hr class="hr" />
                    <div class="row">
                        <div class="col-12 d-flex">
                            <span>Weight: <b><?= isset($weight) ? $weight : '' ?> kg</b></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex">
                            <span>Date: <span id="pickup_date"><?= isset($pickup_date) ? $pickup_date : '' ?></span></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex">
                            <span>Price: <?= isset($price) ? $currency . ' ' . $price : '' ?></span></span>
                        </div>
                    </div>
                    <hr class="hr" />
                    <div class="row">
                        <div class="col-6">
                            <div class="d-flex justify-content-center">
                                <a class="btn btn-default btn-primary" type="button" href="<?= "$SITEURL/rate_checking.php?country=" .$from; ?>" id="backBtn">Back</a>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-default btn-primary" type="submit" value="makeOrder" name="actionBtn" id="actionBtn">Book</button>
                            </div>
                        </div>
                    </div>
                </div>        
            </div>
        </div>
    </form>
</div>

<?php
/*
  oufei 20231014
  common.fun.js
  function(title, subtitle, page name, ajax url path, redirect path, action)
  to show action dialog after finish certain action (eg. edit)
*/
if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Shipping Request","",'.$SITEURL.'"/rate_checking.php?country='.$from.'","I");</script>';
}
?>
</body>

<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
    .forEach(function (form) {
        form.addEventListener('submit', function (event) {
        
        if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
        }
        form.classList.add('was-validated');
        $('submissionChk').val() = "Cont";
        }, false)
    })
})()

const weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
var d = $("#pickup_date").text();
d = new Date(d);
let day = weekday[d.getDay()];

$("#pickup_date").append(' <b>' + day + '</b>')

document.getElementById('parcel_value').addEventListener('input', function(){
	let actualValue = this.value.replace(".","");
	this.value = (parseInt(actualValue)/100).toFixed(2);

    if(this.value == '0' || this.value == '0.00')
        this.value = '';
})
</script>
</html>