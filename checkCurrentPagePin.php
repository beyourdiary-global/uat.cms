
<?php

function getPins($connect)
{
    if (isset($_SESSION['userid'])) {
        $resultUser = getData('*', "id = '" . $_SESSION['userid'] . "'", 'user', $connect);

        if ($resultUser != false) {
            $rowUser = $resultUser->fetch_assoc();

            $pinResult = getData('pins', "id = '" . $rowUser['access_id'] . "'", 'user_group', $connect);

            if ($pinResult !== false) {
                $pinArray = $pinResult->fetch_assoc();
                return $pinArray;
            }
        }
    }
    return '';
}

function getValuesByPinAssocIndex($data, $pin)
{
    // Extract individual entries
    $entries = explode('+', $data['pins']);

    foreach ($entries as $entry) {
        // Remove brackets and split by colon
        $split = explode(':', trim($entry, '[]'));

        if (count($split) == 2 && $split[0] == $pin) {
            // Values are in $split[1]
            $values = explode(',', $split[1]);
            return $values;
        }
    }

    return [];
}

function checkCurrentPin($connect, $currentPage)
{
    $result = getData('*', "", PIN, $connect);
    $actionMapping = [];

    while ($resultPin = $result->fetch_assoc()) {
        $actionMapping[$resultPin['id']] = $resultPin['name'];
    }

    $result = getData('*', "name = '$currentPage'", PIN_GRP, $connect);

    if ($result && $result->num_rows > 0) {
        $resultPin = $result->fetch_assoc();
        $currentPin = $resultPin['id'];

        $pinArray = getPins($connect);
        $result = getValuesByPinAssocIndex($pinArray, $currentPin);

        $result = array_map(function ($permission) use ($actionMapping) {
            return $actionMapping[$permission];
        }, $result);

        return $result;
    } else {
        return [];
    }
}


function isActionAllowed($action, $allowedActions)
{
    return in_array($action, $allowedActions);
}

?>