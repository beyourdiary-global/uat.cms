<?php
function generateDashboard($year, $data, $containerClass, $lastNodeBody = false)
{
    echo '<div class="row mt-5 ' . $containerClass . '">';
    echo '<div class="col-lg-12 col-12 dashboardSubtitle">' . htmlspecialchars($year) . '</div>';
    $colClass = count($data) > 3 ? 'col-lg-3' : 'col-lg-4';

    foreach ($data as $key => $item) {
        echo '<div class="' . $colClass . '  col-12 mb-3 mb-lg-0">';
        echo '<div class="card h-100">';
        echo '<div class="card-body cardBody">';
        // Check if the current item is the last one
        $isLastItem = $key === array_key_last($data);
        $h3Class = $isLastItem && $lastNodeBody ? 'mb-0 fw-bold endNode' : '';

        echo '<span class="' . htmlspecialchars($h3Class) . '">' . htmlspecialchars($item['label']) . '</span>';
        echo '<h3 class="' . htmlspecialchars($h3Class) . '">' . htmlspecialchars($item['value']) . '</h3>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
}

function generateDashboardPlateformAnalysis($year, $data, $containerClass)
{
    echo '<div class="row col-lg-4 mx-auto mr-10 mt-0 ' . $containerClass . '">';
    echo '<div class="col-lg-12 col-12 dashboardSubtitle">' . htmlspecialchars($year) . '</div>';

    foreach ($data as $item) {
        echo '<div class="col-lg-6 col-12 mb-3 mb-lg-0">';
        echo '<div class="card h-100">';
        echo '<div class="card-body">';
        echo '<span>' . htmlspecialchars($item['label']) . '</span>';
        echo '<h4 class="mb-0 fw-bold">' . htmlspecialchars($item['value']) . '</h4>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
}


?>