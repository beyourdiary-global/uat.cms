<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'];
    $destination_url = $_POST['destination_url'];

    $uploads = [];
    for ($i = 0; $i < 4; $i++) {
        $fileKey = 'image' . ($i + 1);
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $targetDir = 'uploads/';
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
            $fileName = uniqid() . '_' . basename($_FILES[$fileKey]['name']);
            $targetFile = $targetDir . $fileName;
            move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFile);
            $uploads[] = $targetFile;
        } else {
            $uploads[] = null;
        }
    }

    // Generate shorten key
    $key_base = 'blogger' . time() . rand();
    $shorten_key = md5($key_base);

    // DB connection
    $pdo = new PDO('mysql:host=127.0.0.1:3306;dbname=beyourdi_cms', 'beyourdi_cms', 'Byd1234@Global');
    $stmt = $pdo->prepare("INSERT INTO shorten (shorten_key, image1, image2, image3, image4, description, destination_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $shorten_key,
        $uploads[0], $uploads[1], $uploads[2], $uploads[3],
        $description,
        $destination_url
    ]);

    $shortened_url = "https://cms.beyourdiary.com/blog/preview.php?blog=" . $shorten_key;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload & Shorten</title>
    <script>
        function copyLink() {
            var copyText = document.getElementById("shortenUrl");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Copied: " + copyText.value);
        }
    </script>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <label>Short Description:</label><br>
        <textarea name="description" required></textarea><br>

        <label>Destination URL:</label><br>
        <input type="url" name="destination_url" required><br>

        <label>Upload 4 Images:</label><br>
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <input type="file" name="image<?= $i ?>"><br>
        <?php endfor; ?>

        <button type="submit">Generate Short URL</button>
    </form>

    <?php if (!empty($shortened_url)): ?>
        <h3>Your Shortened URL</h3>
        <input type="text" id="shortenUrl" value="<?= $shortened_url ?>" readonly style="width: 100%;">
        <button onclick="copyLink()">Copy URL</button>
    <?php endif; ?>
</body>
</html>
