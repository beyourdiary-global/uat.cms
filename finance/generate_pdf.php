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
$isDebit = !empty(input('isDebit')) ? input('isDebit') : post('isDebit');

// Now you can use $isDebit in your logic
if ($isDebit) {
    $tblName = DEBIT_NOTES_INV;
    $tblName2 = DEBIT_INV_PROD;
} else {
    $tblName = CRED_NOTES_INV;
    $tblName2 = CRED_INV_PROD;
}

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
$term_result = getData('*', "id = '" . $row['pay_terms'] . "'", '', FIN_PAY_TERMS, $finance_connect);

$proj_row = $proj_result->fetch_assoc() ?: [];
$curr_row = $curr_result->fetch_assoc() ?: [];
$mrcht_row = $mrcht_result->fetch_assoc() ?: [];
$pay_row = $pay_result->fetch_assoc() ?: [];
$pic_row = $pic_result->fetch_assoc() ?: [];
$term_row = $term_result->fetch_assoc() ?: [];

$payColour = '';
if (isset($row['payment_status'])) {
    if ($row['payment_status'] == 'Paid') {
        $payColour = 'color:#008000;';
    } else if ($row['payment_status'] == 'Cancelled') {
        $payColour = 'color:#ff0000;';
    } else {
        $payColour = 'color:#F17FB5;';
    }
}

$productRows = '';
$productIDs = explode(',', $row['products']);
$num = 1;
foreach ($productIDs as $productID) {
    // Fetch product details from the database
    $query = "SELECT * FROM " . CRED_INV_PROD . " WHERE id = '$productID'";
    $result = mysqli_query($finance_connect, $query);

    // Check if product exists
    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        // Generate HTML row for the product
        $productRows .= '<tr>';
        $productRows .= '<td>' . $num++ . '</td>';
        $productRows .= '<td colspan="3">' . $product['description'] . '</td>';
        $productRows .= '<td>' . $product['price'] . '</td>';
        $productRows .= '<td>' . $product['quantity'] . '</td>';
        $productRows .= '<td>' . $product['amount'] . '</td>';
        $productRows .= '</tr>';
    }
}

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
        "{{pay_colour}}",
        "{{pay_status}}",
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
        "{{pay details}}",
        "{{product rows}}",
        "{{curr}}"
    ],
    [
        $proj_row['company_name'],
        $proj_row['company_address'],
        $proj_row['company_business_no'],
        $proj_row['company_email'],
        $proj_row['company_contact'],
        $row['invoice'],
        $row['date'],
        $payColour,
        $row['payment_status'] = isset($row['payment_status']) && $row['payment_status'] > 0 ? $row['payment_status'] : 'PENDING',
        $row['due_date'] = isset($row['due_date']) && $row['due_date'] > 0 ? $row['due_date'] : '',
        $mrcht_row['name'] = isset($row['bill_nameID']) && $row['bill_nameID'] > 0 ? $mrcht_row['name'] : '',
        $row['bill_add'] = isset($row['bill_add']) && $row['bill_add'] > 0 ? $row['bill_add'] : '',
        $row['bill_contact'] = isset($row['bill_contact']) && $row['bill_contact'] > 0 ? $row['bill_contact'] : '',
        $row['bill_email'] = isset($row['bill_email']) && $row['bill_email'] > 0 ? $row['bill_email'] : '',
        $pic_row['name'] = isset($row['sales_pic']) && $row['sales_pic'] > 0 ? $pic_row['name'] : 'NA',
        $row['remark'] = isset($row['remark']) ? $row['remark'] : 'NA',
        $row['subtotal'] = isset($row['subtotal']) && $row['subtotal'] > 0 ? $row['subtotal'] : '00.00',
        $row['discount'] = isset($row['discount']) && $row['discount'] > 0 ? $row['discount'] : '00.00',
        $row['tax'] = isset($row['tax']) && $row['tax'] > 0 ? $row['tax'] : '00.00',
        isset($row['total']) && $row['total'] > 0 ? $row['total'] : '00.00',
        $term_row['name'] = isset($row['pay_terms']) && $row['pay_terms'] > 0 ? $term_row['name'] : 'NA',
        $pay_row['name'] = isset($row['pay_method']) && $row['pay_method'] > 0 ? $pay_row['name'] : 'NA',
        $row['pay_details'] = isset($row['pay_details']) && $row['pay_details'] > 0 ? $row['pay_details'] : 'NA',
        $productRows,
        $curr_row['unit'] = isset($row['currency']) && $row['currency'] > 0 ? $curr_row['unit'] : '',

    ],
    $html
);

$dompdf->loadHtml($html);
$dompdf->render();
if ($isDebit) {
    $pdfName = "DebitNote_". $row['invoice'] .".pdf";
} else {
    $pdfName = "CreditNote_". $row['invoice'] .".pdf";}
$dompdf->stream($pdfName, ["Attachment" => 0]);

?>