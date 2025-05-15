<?php
if (!isset($_GET['blog'])) {
    http_response_code(400);
    echo "Missing shorten key.";
    exit;
}

$shorten_key = $_GET['blog'];

$pdo = new PDO('mysql:host=127.0.0.1:3306;dbname=beyourdi_cms', 'beyourdi_cms', 'Byd1234@Global');
$stmt = $pdo->prepare("SELECT * FROM shorten WHERE shorten_key = ?");
$stmt->execute([$shorten_key]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    http_response_code(404);
    echo "Invalid shorten URL.";
    exit;
}

$image = $data['image1'] ?: $data['image2'] ?: $data['image3'] ?: $data['image4'] ?: 'default.jpg';
$description = htmlspecialchars($data['description']);
$redirect_url = $data['destination_url'];
$full_url = "https://cms.beyourdiary.com/blog/preview.php?blog=" . $shorten_key;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting...</title>

    <!-- ✅ Open Graph Tags -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Check this out!">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:image" content="https://cms.beyourdiary.com/<?= $image ?>">
    <meta property="og:url" content="<?= $full_url ?>">

    <!-- Optional for Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Check this out!">
    <meta name="twitter:description" content="<?= $description ?>">
    <meta name="twitter:image" content="https://cms.beyourdiary.com/<?= $image ?>">

    <script>
        setTimeout(() => {
            window.location.href = "<?= $redirect_url ?>";
        }, 3000);
    </script>
</head>
<body>
    <p>Redirecting to <a href="<?= $redirect_url ?>"><?= $redirect_url ?></a>...</p>
</body>
</html>
