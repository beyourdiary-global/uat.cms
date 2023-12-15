<?php
// Open the file
$filePath = 'error_log'; // Replace with the actual path to your file

// Read the content of the file
$fileContent = file_get_contents($filePath);

// Display the content
echo nl2br($fileContent);
?>