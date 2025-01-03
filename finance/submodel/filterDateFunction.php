<?php

function generateDateQuery($groupOption3, $groupOption4, $sqlNode) {
    $sqlQuery = "";

    if ($groupOption4 == "monthly") {
        // Split the string by 'to' to get the start and end date ranges
        list($start, $end) = explode('to', $groupOption3);

        // Extract the year and month from the start and end dates
        $startYear = substr($start, 0, 4); // First 4 characters are the year
        $startMonth = substr($start, 5, 2); // Last 2 characters are the month
        $endYear = substr($end, 0, 4);     // First 4 characters are the year
        $endMonth = substr($end, 5, 2);    // Last 2 characters are the month

        // Check if the start and end year and month are the same
        if ($startYear === $endYear && $startMonth === $endMonth) {
            // If the same year and month, use an exact match for the query
            $sqlQuery = "YEAR(`$sqlNode`) = $startYear AND MONTH(`$sqlNode`) = $startMonth";
        } else {
            // If different year or month, use a BETWEEN clause
            $sqlQuery = "YEAR(`$sqlNode`) = $startYear AND MONTH(`$sqlNode`) BETWEEN $startMonth AND $endMonth";
        }
    } elseif ($groupOption4 == "yearly") {
        list($startYear, $endYear) = explode('to', $groupOption3);
        // Check if the start and end years are the same
        if ($startYear === $endYear) {
            // If the same year, use an exact match for the query
            $sqlQuery = "YEAR(`$sqlNode`) = $startYear";
        } else {
            // If different years, use a BETWEEN clause
            $sqlQuery = "YEAR(`$sqlNode`) BETWEEN $startYear AND $endYear";
        }
    } elseif ($groupOption4 == "daily") {
        // For daily queries, use an exact date match
        $sqlQuery = "`$sqlNode` = '$groupOption3'";
    } elseif ($groupOption4 == "weekly") {
        // Split the string by 'to' to get the start and end date ranges
        list($startDate, $endDate) = explode('to', $groupOption3);
        
        // Format the result into the required format
        $sqlQuery = "`$sqlNode` BETWEEN '$startDate' AND '$endDate'";    
    }

    return $sqlQuery;
}

?>