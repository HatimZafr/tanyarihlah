<?php

// Token Bot Telegram yang didapat dari BotFather
$botToken = "7741226853:AAE202WW1QArryvCvofS3wvDAjmPE4E1bdg";
// ID chat yang akan menerima pesan (misalnya ID admin atau grup)
$forwardChatId = "5376369200";

// URL API Telegram untuk mengirimkan pesan
$apiUrl = "https://api.telegram.org/bot$botToken/";

// Fungsi untuk mengirimkan request ke API Telegram
function sendMessage($chatId, $message) {
    global $apiUrl;
    $url = $apiUrl . "sendMessage?chat_id=$chatId&text=" . urlencode($message);
    file_get_contents($url);
}

// Fungsi untuk meneruskan pesan
function forwardMessage($chatId, $fromChatId, $messageId) {
    global $apiUrl;
    $url = $apiUrl . "forwardMessage?chat_id=$chatId&from_chat_id=$fromChatId&message_id=$messageId";
    file_get_contents($url);
}

// Fungsi untuk membalas pesan
function replyToMessage($chatId, $message, $replyMessageId) {
    global $apiUrl;
    $url = $apiUrl . "sendMessage?chat_id=$chatId&text=" . urlencode($message) . "&reply_to_message_id=$replyMessageId";
    file_get_contents($url);
}

// Mendapatkan data dari webhook Telegram
$update = json_decode(file_get_contents("php://input"), true);

if (isset($update['message'])) {
    $message = $update['message'];
    
    // Jika pesan bukan terusan, kita akan meneruskannya
    if (isset($message['text'])) {
        $text = $message['text'];
        $fromChatId = $message['chat']['id'];
        $messageId = $message['message_id'];
        
        // Jika pesan ini adalah balasan dari admin
        if (isset($message['reply_to_message']) && isset($message['reply_to_message']['forward_from'])) {
            // Cek jika pesan ini adalah balasan terhadap pesan yang diteruskan
            $originalMessageId = $message['reply_to_message']['message_id'];
            $forwardFromChatId = $message['reply_to_message']['forward_from']['id'];
            
            // Mengirimkan balasan ke pengirim asli (forwardFromChatId)
            replyToMessage($forwardFromChatId, $text, $originalMessageId);
        } else {
            // Meneruskan pesan ke chat admin atau grup lain
            forwardMessage($forwardChatId, $fromChatId, $messageId);
            
            // Kirim konfirmasi jika perlu
            sendMessage($fromChatId, "Pesan Anda telah diteruskan kepada admin.");
        }
    }
}
?>
