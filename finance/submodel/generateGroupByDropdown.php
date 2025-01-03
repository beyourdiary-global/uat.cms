<?php
function generateGroupByDropdown($id = "group", $options = [],$classStyle ="col-md-3", $selectedValue) {
    // Default options if none are provided
    if (empty($options)) {
        $options = [
            "brand" => "Brand",
            "status" => "Order Status",
            "shopee_acc" => "Shopee Account",
            "currency" => "Currency",
            "package" => "Package",
            "buyer" => "Shopee Buyer Username",
            "person" => "Person In Charge"
        ];
    }

    // Start generating the HTML for the dropdown
    $html = '<div class="'.$classStyle.'">';
    $html .= '<label class="form-label">Group by:</label>';
    $html .= '<select class="form-select" id="' . htmlspecialchars($id) . '">';

    // Loop through options and render them
    foreach ($options as $value => $label) {
        // Set "brand" as the default selected option if it exists
        $selected = ($value === $selectedValue) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
    }

    $html .= '</select>';
    $html .= '</div>';

    return $html;
}

function getGroupHeader($group) {
    switch ($group) {
        case 'status':
            return "Order Status";
        case 'shopee_acc':
            return "Shopee Account";
        case 'currency':
            return "Currency";
        case 'package':
            return "Package";
        case 'brand':
            return "Brand";
        case 'buyer':
            return "Shopee Buyer Username";
        case 'person':
            return "Person In Charge";
        default:
            return ""; // Return an empty string if no match is found
    }
}



?>