<?php
$isFinance = 1;
include_once "../include/connection.php";
include_once "../include/common.php";
include_once "../include/common_variable.php";
require_once '../header/dompdf/autoload.inc.php';
ini_set("gd.jpeg_ignore_warning", 1);
//post variables 
use Dompdf\Dompdf;
use Dompdf\Options;

$tblName = CRED_NOTES_INV;
//Current Page Action And Data ID
$dataID = !empty(input('id')) ? input('id') : post('id');


//Get The Data From Database
$rst = getData('*', "id = '$dataID'", '', $tblName, $finance_connect);

//Checking Data Error When Retrieved From Database
$row = $rst->fetch_assoc();


$proj_result = getData('*', "id = '" . $row['projectID'] . "'", '', PROJ, $connect);
$curr_result = getData('*', "id = '" . $row['currency'] . "'", '', CUR_UNIT, $connect);
$mrcht_result = getData('*', "id = '" . $row['bill_nameID'] . "'", '', MERCHANT, $finance_connect);
$pay_result = getData('*', "id = '" . $row['pay_method'] . "'", '', FIN_PAY_METH, $finance_connect);
$pic_result = getData('*', "id = '" . $row['sales_pic'] . "'", '', USR_USER, $connect);

$proj_row = $proj_result->fetch_assoc();
$curr_row = $curr_result->fetch_assoc();
$mrcht_row = $mrcht_result->fetch_assoc();
$pay_row = $pay_result->fetch_assoc();
$pic_row = $pic_result->fetch_assoc();

$dompdf = new Dompdf;

$options = new Options;
$options->setChroot(ROOT);
$options->setIsRemoteEnabled(true);

$dompdf = new Dompdf($options);
$dompdf->setPaper("A4", "portrait");

$html = file_get_contents("template.html");

$html = str_replace(
    [
        "{{COMPANY NAME}}",
        "{{comp_add}}",
        "{{comp_business_no}}",
        "{{comp_email}}",
        "{{comp_ctc}}",
        "{{invoice}}",
        "{{date}}",
        "{{due}}",
        "{{name}}",
        "{{address}}",
        "{{contact}}",
        "{{email}}",
        "{{pic}}",
        "{{person in charge remark}}",
        "{{subtotal}}",
        "{{discount}}",
        "{{tax}}",
        "{{total}}",
        "{{payment terms}}",
        "{{pay method}}",
        "{{pay details}}"
    ],
    [
        isset($proj_row['company_name']) ? $proj_row['company_name'] : '',
        isset($proj_row['company_address']) ? $proj_row['company_address'] : '',
        isset($proj_row['company_business_no']) ? $proj_row['company_business_no'] : '',
        isset($proj_row['company_email']) ? $proj_row['company_email'] : '',
        isset($proj_row['company_contact']) ? $proj_row['company_contact'] : '',
        isset($row['invoice']) ? $row['invoice'] : '',
        isset($row['date']) ? $row['date'] : '',
        isset($row['due_date']) ? $row['due_date'] : '',
        isset($mrcht_row['name']) ? $mrcht_row['name'] : '',
        isset($row['bill_add']) ? $row['bill_add'] : '',
        isset($row['bill_contact']) ? $row['bill_contact'] : '',
        isset($row['bill_email']) ? $row['bill_email'] : '',
        isset($pic_row['name']) ? $pic_row['name'] : '',
        isset($row['remark']) ? $row['remark'] : '',
        isset($row['subtotal']) ? $row['subtotal'] : '',
        isset($row['discount']) ? $row['discount'] : '',
        isset($row['tax']) ? $row['tax'] : '',
        isset($row['total']) ? $row['total'] : '',
        isset($row['pay_terms']) ? $row['pay_terms'] : '',
        isset($pay_row['name']) ? $pay_row['name'] : '',
        isset($row['pay_details']) ? $row['pay_details'] : ''
    ],
    $html
);

//$html = str_replace(["{{COMPANY NAME}}","{{comp_add}}","{{comp_business_no}}","{{comp_email}}","{{comp_ctc}}","{{invoice}}","{{date}}","{{due}}","{{name}}","{{address}}","{{contact}}","{{email}}","{{pic}}","{{person in charge remark}}","{{subtotal}}","{{discount}}","{{tax}}","{{total}}","{{notes}}"], [$name, $quantity], $html);
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("invoice.pdf", ["Attachment" => 0]);

?>