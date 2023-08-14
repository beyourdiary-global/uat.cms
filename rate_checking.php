<!DOCTYPE html>
<html>
<head>
<?php 
include "header.php";
include "./include/common.php";
?>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/form.css">
</head>

<?php
if(post('actionBtn'))
{
    $act = post('actionBtn');
    if($act == 'chkRate')
    {
        $data = array();
        $data['country'] = input('country');
        $data['from'] = post('from');
        $data['area_from'] = post('area_from');
        $data['postcode_from'] = post('postcode_from');
        $data['to'] = post('to');
        $data['area_to'] = post('area_to');
        $data['postcode_to'] = post('postcode_to');
        $data['weight'] = post('postcode_to');

        print_r($data);

        rate_checking($data);
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
    font-size: 32px;
    font-weight: 500;
    color: #000000;
}

.chkRate {
    background-color: #FFFFFF;  
    border-radius: 5px;
    box-shadow: 0px 0px 1px 1px #E4E6E6;
}

.disabledSelection{
  pointer-events: none;
}
</style>

<body>

<div class="my-3 container-fluid d-flex justify-content-center">
    <div class="chkRate col-12 col-sm-12 col-md-8 col-lg-8">
        <div class="px-4 py-4">
            <div class="mb-3">
                <div class="form-group">
                    <span class="title-form">Check Rate</span>
                </div>
            </div>

            <div id="section1">
            </div>

            <div id="section2">
            </div>
            
        </div>
    </div>
</div>

</body>

<script>
$(function() {
    // check if country value exist
    var country = getUrlParameter("country") ? getUrlParameter("country") : '';

    /* if(getUrlParameter("domestic_from"))
        var country = getUrlParameter("domestic_from"); */

    countrySelector(country, "#section1");
    deliveryOptions(country, "#section1");
});
</script>

</html>