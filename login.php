<?php
include "include/common.php";
include "include/connection.php";

$email = post('email-addr');
$password = md5(post('password'));

if ($email && $password) {
     $loginquery = "SELECT * FROM " . USR_USER . " WHERE email='" . $email . "'";
     $loginresult = mysqli_query($connect, $loginquery);

     if (!(mysqli_num_rows($loginresult) == 1)) {
          return header('Location: index.php?err=1');
     } else {

          $loginrows = $loginresult->fetch_assoc();
          if ($loginrows['fail_count'] == 4) {
               return header('Location: index.php?err=3');
          }

          if ($loginrows['password_alt'] != $password) {
               mysqli_query($connect, "UPDATE " . USR_USER . " SET fail_count = fail_count + 1 WHERE email = '" . $email . "'");
               return header('Location: index.php?err=2');
          } else {
               if ($loginrows['fail_count'] >= 1 || $loginrows['fail_count'] <= 3)
                    mysqli_query($connect, "UPDATE " . USR_USER . " SET fail_count = 0 WHERE email = '" . $email . "' AND password_alt = '" . $password . "'");
               $_SESSION['userid'] = $loginrows['id'];
               $_SESSION['user_name'] = $loginrows['name'];
               $_SESSION['user_email'] = $loginrows['email'];
               $_SESSION['user_group'] = $loginrows['access_id'];

               $pin_qry = "SELECT * FROM " . USR_GRP . " WHERE id ='" . $_SESSION['user_group'] . "'";
               $row = $connect->query($pin_qry)->fetch_assoc();

               if ($row['pins'] != null) {
                    // get user pin
                    $pins = explode("+", $row['pins']);
                    for ($i = 0; $i < count($pins); $i++) {
                         $pins[$i] = str_replace("[", "", $pins[$i]);
                         $pins[$i] = str_replace("]", "", $pins[$i]);
                    }

                    foreach ($pins as $x) {
                         $colonpos = stripos($x, ":");
                         $tmp_pingrp = substr($x, 0, $colonpos);
                         $tmp_pin = substr($x, $colonpos);
                         $tmp_pin = str_replace(":", "", $tmp_pin);
                         $tmp_pin = explode(",", $tmp_pin);
                         $permission_grp[$tmp_pingrp] = $tmp_pin;
                    }
                    $permission_grp_keys = array_keys($permission_grp);
                    $_SESSION['usr_pin'] = $permission_grp_keys;

                    $log = [
                         'log_act' => 'login',
                         'act_msg' => $loginrows['name'] . " has login to the system.",
                         'cdate' => $cdate,
                         'ctime' => $ctime,
                         'uid' => $loginrows['id'],
                         'cby' => $loginrows['id'],
                         'connect' => $connect,
                     ];
                     
                     audit_log($log);

                    // json file
                    generateDBData(BRAND, $connect);
                    generateDBData(WGT_UNIT, $connect);
                    generateDBData(CUR_UNIT, $connect);
                    generateDBData(PROD, $connect);
                    generateDBData(PROD_STATUS, $connect);
                    generateDBData(MERCHANT, $finance_connect);
                    generateDBData(USR_USER, $connect);
                    generateDBData(META_ADS_ACC, $finance_connect);
                    generateDBData(COUNTRIES, $connect);
                    generateDBData(COURIER, $connect);
                    generateDBData(BRD_SERIES, $connect);
                    generateDBData(PKG, $connect);
                    generateDBData(FIN_PAY_METH, $finance_connect);
                    generateDBData(PROD_CATEGORY, $connect);
                    generateDBData(AGENT, $finance_connect);
                    generateDBData(FB_PAGE_ACC, $finance_connect);
                    generateDBData(SHOPEE_ACC, $finance_connect);
                    generateDBData(CHANEL_SC_MD, $finance_connect);
                    generateDBData(SHOPEE_CUST_INFO, $finance_connect);
                    generateDBData(WEB_CUST_RCD, $connect);
                    generateDBData(LAZADA_ACC, $finance_connect);
                    generateDBData(LAZADA_CUST_RCD, $connect);

                    return header('Location: dashboard.php');
               } else {
                    return header('Location: index.php?err=4');
               }
          }
     }
}
