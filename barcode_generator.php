<?php
$pageTitle = "Barcode Generator";
include 'menuHeader.php';
include "./header/phpqrcode/qrlib.php";

$redirect_page = '';
$tblname = PKG;

//set it to writable location, a place for temp generated PNG files
$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;

//html PNG location prefix
$PNG_WEB_DIR = 'temp/';

//processing form input
$errorCorrectionLevel = 'H';
$matrixPointSize = 2;

if(post('actionBtn'))
{
    $action = post('actionBtn');

    switch($action)
    {
        case 'generate':
            $pkg_name = postSpaceFilter('pkg');
            $pkg = postSpaceFilter('pkg_hidden');
            $page_no = postSpaceFilter('page_no');
            $warehouse = postSpaceFilter('warehouse');

            if(!$pkg && $pkg == '')
                $err = 'Please select the package to generate barcode.';

            if(!$page_no || !($page_no != '0'))
                $err2 = 'Page Number cannot be empty or less than 1.';

            if($warehouse == 'noValue')
                $err3 = 'Please select the warehouse to generate barcode';

            if(($pkg && $pkg != '') && ($page_no || ($page_no != '0')) && ($warehouse != 'noValue'))
            {
                $rst_projInfo = getData("barcode_prefix,barcode_next_number","id='1'",PROJ,$connect);
                $projInfo = $rst_projInfo->fetch_assoc();

                if($projInfo)
                {
                    $barcode_prefix = $projInfo['barcode_prefix'];
                    $barcode_next_number = $projInfo['barcode_next_number'];

                    $finalBarcodeNo = $barcode_next_number + $page_no;
                    echo '<div id="printArea" class="container2">';
                    for($x=1; $x<=$page_no; $x++)
                    {
                        $qrCode_url = "stockin.php?barcode=".($barcode_next_number + $x)."&pkgid=".$pkg."&whseid=".$warehouse;
                        $filename=$PNG_TEMP_DIR.'barcode'.md5($qrCode_url.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
                        QRcode::png($qrCode_url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                        echo '<div class="column"><img src="' .$PNG_WEB_DIR.basename($filename).'" />'.'<p class="title">'.$pkg_name.' '.($barcode_next_number + $x).'
                            </p>
                        </div>';
                    }
                    $sqlupd = "UPDATE projects SET barcode_next_number = '".$finalBarcodeNo."' WHERE id = '1'";
                    $query2 = mysqli_query($connect,$sqlupd);
                    // Automatically trigger the print action using JavaScript
                    echo '<script>
                        window.onload = function() {
                            var header = document.querySelector(".sticky-top");
                            var form = document.querySelector("form");
                            header.style.display = "none";
                            form.style.display = "none";
                            window.print();
                        }
                        window.onafterprint = function() {
                            // Print page has been closed
                            // Remove the content of the container
                            var container = document.querySelector("#printArea");
                            container.innerHTML = "";
                            
                            // Show the form again
                            var header = document.querySelector(".sticky-top");
                            var form = document.querySelector("form");
                            header.style.display = "block";
                            form.style.display = "block";
                        }
                    </script>';
                    echo '</div>';
                }
            }
            break;
        case 'back':
            break;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="./css/main.css">
<link rel="stylesheet" href="./css/barcode_generator.css">
</head>

<body>

<div class="container d-flex justify-content-center mt-2">
        <div class="col-8 col-md-6">
            <form id="prodForm" method="post" action="">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group my-5">
                            <h2>
                                Generate Barcode
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="pkg_lbl" for="pkg">Package Name</label>
                            <input class="form-control" type="text" name="pkg" id="pkg" value=
                            "<?php
                                unset($echoVal);
                                if(isset($pkg) && $pkg != '')
                                    $echoVal = $pkg;

                                if(isset($echoVal))
                                {
                                    $n_rst = getData('name',"id = '$echoVal'",$tblname,$connect);
                                    $n = $n_rst->fetch_assoc();
                                    echo $n['name'];
                                }
                            ?>">
                            <input type="hidden" name="pkg_hidden" id="pkg_hidden" value="<?php
                                if(isset($pkg))
                                    echo $pkg;
                            ?>">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err)) echo $err; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group autocomplete mb-3">
                            <label class="form-label form_lbl" id="page_no_lbl" for="page_no">Page No.</label>
                            <input class="form-control" type="text" name="page_no" id="page_no" value=
                            "<?php
                                if(isset($page_no))
                                    echo $page_no;
                            ?>">
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err2)) echo $err2; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label form_lbl" id="warehouse_lbl" for="warehouse">Warehouse</label>
                            <select class="form-select" name="warehouse" id="warehouse">
                            <option value="noValue" <?php if(!isset($warehouse)) echo 'selected' ?>>--Please Choose--</option>
                            <?php
                                $rst_warehouse_list = getData("id,name",'',WHSE,$connect);
                                while($warehouse_list = $rst_warehouse_list->fetch_assoc())
                                {
                                    $whse_id = $warehouse_list['id'];
                                    $whse_name = $warehouse_list['name'];

                                    $selected = '';
                                    if(isset($warehouse))
                                        if($warehouse == $whse_id)
                                            $selected = "selected";

                                    echo "<option value=\"$whse_id\" $selected>$whse_name</option>";
                                }
                            ?>
                            </select>
                            <div id="err_msg">
                                <span class="mt-n1"><?php if (isset($err3)) echo $err3; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group mb-3 d-flex justify-content-center">
                        <button class="btn btn-lg btn-rounded btn-primary mx-2" name="actionBtn" id="actionBtn" value="generate">Generate</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
</div>
<?php
/* if(isset($_SESSION['tempValConfirmBox']))
{
    unset($_SESSION['tempValConfirmBox']);
    echo '<script>confirmationDialog("","","Product","","'.$redirect_page.'","'.$act.'");</script>';
} */
?>
</body>
<script>
$(document).ready(function(){
    var packageName = $("#pkg");

    packageName.keyup(function(e){
        var param = {
            search: $(this).val(),                              // search value
            searchType: 'name',                                  // column of the table
            elementID: $(this).attr('id'),                      // id of the input
            hiddenElementID: $(this).attr('id') + '_hidden',    // hidden input for storing the value
            dbTable: '<?= $tblname ?>'                             // json filename (generated when login)
        }
        var arr = searchInput(param);
    });
    packageName.change(function(){
        if($(this).val() == '')
            $('#'+$(this).attr('id')+'_hidden').val('');
    });
});

</script>
</html>