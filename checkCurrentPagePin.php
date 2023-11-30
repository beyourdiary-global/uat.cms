
<?php

function getPins($connect)
{
    if ($_SESSION['userid']) {
        $resultUser = getData('*', "id = '" . $_SESSION['userid'] . "'", 'user', $connect);

        if ($resultUser != false) {
            $dataExisted = 1;
            $rowUser = $resultUser->fetch_assoc();

            $pinResult = getData('pins', "id = '" . $rowUser['access_id'] . "'", 'user_group', $connect);

            if ($pinResult !== false) {
                $pinArray = $pinResult->fetch_assoc();
            }
        }
    }
    return $pinArray;
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
    $result = getData('*', "name = '$currentPage'", PIN_GRP, $connect);
    $resultPin = $result->fetch_assoc();
    $currentPin = $resultPin['id'];

    $pinArray = getPins($connect);
    
    $result = getValuesByPinAssocIndex($pinArray, $currentPin);

    $actionMapping = [
        "8" => "Log out",
        "7" => "Log in",
        "6" => "Export",
        "5" => "Import",
        "4" => "add",
        "3" => "delete",
        "2" => "edit",
        "1" => "view"
    ];

    $result = array_map(function ($permission) use ($actionMapping) {
        return $actionMapping[$permission];
    }, $result);

    return $result;
}

function isActionAllowed($action, $allowedActions)
{
    return in_array($action, $allowedActions);
}

?>