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
    $botToken = '7831202681:AAHpc3DpAGLJEfbM3o9WC2NoUmdnHZ3xDyU';
    $webhookUrl = 'https://tanyarihlah.bohr.io/bot1.php';
    $url = "https://api.telegram.org/bot$botToken/setWebhook?url=" . urlencode($webhookUrl);
    $response = file_get_contents($url);

    echo "<p>Webhook URL yang di-set: <strong>$webhookUrl</strong></p>";
    echo "<p>Response dari Telegram API: <strong>$response</strong></p>";
    ?>

</body>
</html>
