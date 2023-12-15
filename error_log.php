<?php
// Open the file
$filePath = 'error_log'; // Replace with the actual path to your file
$filePathFinance = 'finance/error_log'; // Replace with the actual path to your file

// Read the content of the file
$fileContent = file_get_contents($filePath);
$fileContentFinance = file_get_contents($filePathFinance);

// Display the content
echo nl2br($fileContent);
echo nl2br($fileContentFinance);


?>