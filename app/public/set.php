<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Webhook Telegram</title>
</head>
<body>
    <h1>Set Webhook Telegram</h1>

    <?php
    $botToken = '7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI';
    $webhookUrl = 'https://tanyarihlah.bohr.io/bot4.php';
    $url = "https://api.telegram.org/bot$botToken/setWebhook?url=" . urlencode($webhookUrl);
    $response = file_get_contents($url);

    echo "<p>Webhook URL yang di-set: <strong>$webhookUrl</strong></p>";
    echo "<p>Response dari Telegram API: <strong>$response</strong></p>";
    ?>

</body>
</html>
