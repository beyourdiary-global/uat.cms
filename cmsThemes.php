<?php
$rst = getData('*', "id = '1'", 'projects', $connect);

if ($rst != false) {
    $dataExisted = 1;
    $row = $rst->fetch_assoc();
}

echo "

    function setButtonColor() {

        var elements = document.querySelectorAll('[id=\"actionBtn\"], [id=\"addBtn\"]');

        elements.forEach(function(element) {
            element.style.backgroundColor = '" . ($dataExisted ? $row['buttonColor'] : '') . "';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setButtonColor();
    });

";
?>
