<?php

function convertDbNaming($key,$value){
   
    global $connect, $finance_connect;
    if($key == 'order_status'){
        if ($value == 'P') {
            return 'Processing';
        }else  if ($value == 'SP') {
           return 'Shipped';
        }else  if ($value == 'WP') {
            return 'Waiting Packing';
        }
        return;
    }else{
        $columnName='name';
        $tableName='';
        $connectDB = $connect;
        switch ($key) {
            case 'pic':
                $tableName = USR_USER;
                break;
            case 'brand':
                $tableName = BRAND;
                break;
            case 'package':
                $tableName = PKG;
                break;
            case 'shopee_acc':
                $tableName = SHOPEE_ACC;
                $connectDB = $finance_connect;
                break;
            case 'currency':
                $tableName = CUR_UNIT;
                $columnName ='unit';
                break;
            case 'buyer':
                $tableName = SHOPEE_CUST_INFO;
                $columnName = 'buyer_username';
                $connectDB = $finance_connect;
                break;
            default:
                $tableName = 'brand';
                break;
        }
        $result = getData($columnName, "id='" . $value . "'", '', $tableName, $connectDB);
        $row = mysqli_fetch_assoc($result);
        return $row[$columnName];
    }
     
    
}
?>