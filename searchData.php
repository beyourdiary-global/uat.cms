<?php
include "include/common.php";
include "include/common_variable.php";
include "include/connection.php";

// Get the search parameter and column name from the POST request
$searchText = mysqli_real_escape_string($connect, $_POST['searchText']);
$searchType = $_POST['searchType'];
$searchCol = $_POST['searchCol'];
$tblname = $_POST['tblname'];
$isFinance = $_POST['isFin'];

if ($isFinance) {
    $db = $finance_connect;
} else {
    $db = $connect;
}

// Build the query dynamically
$query = "SELECT $searchType FROM $tblname WHERE `$searchCol` = '$searchText' AND `status` = 'A' ";
$result = mysqli_query($db, $query);

if ($result) {
    // Fetch the result as an associative array
    $searchResult = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Return the search result as a JSON response
    echo json_encode($searchResult);
} else {
    // Handle the case where the query fails
    echo json_encode(['error' => 'Error executing query']);
}
?>
