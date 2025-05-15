<?php
session_start();
// $livemode = false; // true = test link, false = live link
$siteOrlocalMode = true;  //true = live site, false = localhost

date_default_timezone_set('Asia/Singapore');


$dbUser = $siteOrlocalMode ? 'beyourdi_cms' : 'root';

define('dbuser', $dbUser);
define('dbpwd', $siteOrlocalMode ? 'Byd1234@Global' : '');
define('dbhost', $siteOrlocalMode ? '127.0.0.1:3306' : 'localhost');
define('dbname', 'beyourdi_cms');
define('dbFinance', 'beyourdi_financial');
define('SITEURL', $siteOrlocalMode ? 'https://cms.beyourdiary.com' : 'http://localhost/cms');
$SITEURL = SITEURL;
define('ROOT', dirname(__FILE__));
define('email_cc', "report@beyourdiary.com	");


// //define date time
define('date_dis', date("Y-m-d"));
define('time_dis', date("G:i:s"));
define('yearMonth', strtolower(date('YM')));
define('comYMD', strtolower(date('Ymd')));
define('GlobalPin', isset($_SESSION['usr_pin']) ? $_SESSION['usr_pin'] : '');
// define('memberImportDetail', yearMonth.'_importInfo');

$email_collect = '';
$cdate = date_dis;
$ctime = time_dis;
$comYMD = comYMD;
/* $cby = $_SESSION['userid']; */

$act_1 = 'I'; //Insert/ Add
$act_2 = 'E'; //Edit/ Update
$act_3 = 'D'; //Delete

// //session define
// $displayName = $_SESSION['login_name'];
define('USER_ID', isset($_SESSION['userid']) ? $_SESSION['userid'] : '');
define('USER_NAME', isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '');
define('USER_EMAIL', isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '');
define('USER_GROUP', isset($_SESSION['user_group']) ? $_SESSION['user_group'] : '');
// //call client url //demo link 
// if($livemode==true)
// 	$curl_ship_domain = 'https://demo.connect.easyparcel.sg/?ac=';
// else
// 	$curl_ship_domain = 'https://connect.easyparcel.sg/?ac=';

// //api courier
// $api = 'EP-Mqx0IKqqS';
// if($livemode==true)
// 	$authentication = 'zKpyWplgj9'; //demo authentication
// els
// 	$authentication = 'nYgGJWc9Hq'; //live authentication

// //error message default mean
// $error_msg = array('3'=>'Required api key', '4'=>'Invalid api key', '5'=>'Unauthorized user', '0'=>'Success', '1'=>'Required authentication key', '1'=>'Invalid authentication key', '6'=>'Invalid data format');

// easyparcel demo auth & api
define('EASYPARCEL_DOMAIN_MY', 'https://demo.connect.easyparcel.my/?ac=');
define('EASYPARCEL_AUTH_MY', 'MwxHG9i3Wu');
define('EASYPARCEL_API_MY', 'EP-Jj0HYyEkp');
define('EASYPARCEL_DOMAIN_SG', 'https://demo.connect.easyparcel.sg/?ac=');
define('EASYPARCEL_AUTH_SG', 'zKpyWplgj9');
define('EASYPARCEL_API_SG', 'EP-Mqx0IKqqS');

// //table name define
define('USR_USER', 'user');
define('LANG', 'language');
define('PIN', 'pin');
define('PIN_GRP', 'pin_group');
define('AUDIT_LOG', 'audit_log');
define('USR_GRP', 'user_group');
define('DESIG', 'designation');
define('DEPT', 'department');
define('HOLIDAY', 'holiday');
define('BRAND', 'brand');
define('PLTF', 'platform');
define('PROD_STATUS', 'product_status');
define('WHSE', 'warehouse');
define('MRTL_STATUS', 'marital_status');
define('BANK', 'bank');
define('EM_TYPE_STATUS', 'em_type_status');
define('CUR_UNIT', 'currency_unit');
define('CURRENCIES', 'currencies');
define('CUST', 'customer');
define('COURIER', 'courier');
define('SHIPREQ', 'shipping_request');
define('WGT_UNIT', 'weight_unit');
define('PROD', 'product');
define('PKG', 'package');
define('PROJ', 'projects');
define('STK_REC', 'stock_record');
define('L_TYPE', 'leave_type');
define('CUR_SEGMENTATION', 'customer_segmentation');
define('RACE', 'race');
define('L_STS', 'leave_status');
define('ID_TYPE', 'identity_type');
define('SOCSO_CATH', 'socso_category');
define('EMPLOYEE_EPF', 'employee_epf_rate');
define('EMPLOYER_EPF', 'employer_epf_rate');
define('PAY_METH', 'payment_method');
define('EMPINFO', 'employee_info');
define('EMPPERSONALINFO', 'employee_personal_info');
define('TAG', 'tag');
define('EMPLEAVE', 'employee_leave');
define('CUS_INFO', 'customer_info');
define('L_PENDING', 'leave_pending');
define('BRD_SERIES', 'brand_series');
define('FB_CUST_DEALS', 'customer_facebook_deals_transaction');
define('URBAN_CUST_REG', 'urbanism_customer_register_info');
define('OFFICIAL_PROCESS_ORDER', 'official_process_order');
define('YEARLYGOAL', 'yearly_goals');


//finance
define('MERCHANT', 'merchant');
define('CURR_BANK_TRANS', 'asset_current_bank_acc_transaction');
define('INV_TRANS', 'asset_investment_transaction');
define('INVTR_TRANS', 'asset_inventories_transaction');
define('SD_TRANS', 'asset_sundry_debtors_transactions');
define('INITCA_TRANS', 'asset_initial_capital_transaction');
define('OCR_TRANS', 'asset_other_creditor_transaction');
define('CAONHD', 'asset_cash_on_hand_transaction');
define('META_ADS_ACC', 'meta_ads_account');
define('EXPENSE_TYPE', 'expense_type');
define('FB_ADS_TOPUP', 'facebook_ads_topup_transaction');
define('COUNTRIES', 'countries');
define('MRCHT_COMM', 'merchant_commission');
define('BANK_TRANS_BACKUP', 'bank_transaction_backup');
define('FIN_PAY_METH', 'finance_payment_method');
define('PROD_CATEGORY', 'product_category');
define('TAX_SETT', 'tax_setting');
define('INTERNAL_CONSUME', 'internal_consume_ticket_credit_transaction');
define('DEL_FEES_CLAIM', 'delivery_fees_claim_transaction');
define('FIN_PAY_TERMS', 'payment_terms');
define('ITL_CSM_ITEM', 'internal_consume_item');
define('SHOPEE_WDL_TRANS', 'shopee_withdrawal_transactions');
define('DW_TOP_UP_RECORD', 'downline_top_up_record');
define('AGENT', 'agent');
define('SHOPEE_ACC', 'shopee_account');
define('FB_ORDER_REQ', 'facebook_order_request');
define('FB_PAGE_ACC', 'facebook_page_account');
define('SHOPEE_ADS_TOPUP', 'shopee_ads_topup_transaction');
define('STK_CDT_TOPUP_RCD', 'stock_credit_topup_record');
define('CHANEL_SC_MD', 'chanel_social_media');
define('PAY_MTHD_SHOPEE', 'shopee_payment_method');
define('LAZADA_ACC', 'lazada_account');
define('CRED_NOTES_INV', 'credit_notes_invoice');
define('SHOPEE_SG_SETT', 'shopee_sg_fees_setting');
define('SHOPEE_SCR_SETT', 'shopee_service_charges_rate_setting');
define('WEB_CUST_RCD', 'customer_website_deals_transaction');
define('SHOPEE_CUST_INFO', 'shopee_customer_info');
define('ATOME_TRANS_BACKUP', 'atome_transaction_backup');
define('SHOPEE_SG_ORDER_REQ', 'shopee_sg_order_request');
define('JT_TRANS_BACKUP', 'jt_transaction_backup');
define('STRIPE_TRANS_BACKUP', 'stripe_transaction_backup');
define('LAZADA_CUST_RCD', 'customer_lazada_deals_transaction');
define('WEB_ORDER_REQ', 'website_order_request');
// define('LAZADA_CUST_RCD', 'customer_lazada_deals_transaction');
define('CRED_INV_PROD', 'cred_inv_products');
define('DEBIT_NOTES_INV', 'debit_notes_invoice');
define('DEBIT_INV_PROD', 'debit_inv_products');
define('LAZADA_ORDER_REQ', 'lazada_order_request');

$connect = @mysqli_connect(dbhost, dbuser, dbpwd, dbname);
$finance_connect = @mysqli_connect(dbhost, dbuser, dbpwd, dbFinance);

//define session
?>