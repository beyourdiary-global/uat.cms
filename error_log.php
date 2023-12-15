<?php
// Open the file
$filePath = 'error_log'; // Replace with the actual path to your file

// Get the MIME type
$mime = mime_content_type($filePath);

// Display the result
echo "The file type is: $mime";
?>