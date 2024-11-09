<?php
// Konfigurasi bot
$botToken = '7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI';
$apiUrl = "https://api.telegram.org/bot$botToken/";

// Fungsi untuk mengirim pesan
function sendMessage($chatId, $message) {
    global $apiUrl;
    $url = $apiUrl . "sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}

// Fungsi untuk mengirim pesan dengan tombol inline
function sendMessageWithInlineKeyboard($chatId, $text, $keyboard) {
    global $apiUrl;
    $data = array(
        'chat_id' => $chatId,
        'text' => $text,
        'reply_markup' => json_encode(array('inline_keyboard' => $keyboard))
    );
    $url = $apiUrl . "sendMessage";
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context  = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// Fungsi untuk mengedit pesan
function editMessage($chatId, $messageId, $newText, $keyboard = null) {
    global $apiUrl;
    $data = array(
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => $newText,
    );

    if ($keyboard) {
        $data['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard));
    }

    $url = $apiUrl . "editMessageText";
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context  = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// Fungsi untuk menampilkan daftar profil Masyaikh
function showMasyaikhList($chatId, $messageId = null) {
    $text = "Pilih profil Masyaikh yang ingin Anda lihat:";
    $keyboard = array(
        array(
            array("text" => "Profil Syaikh 1", "callback_data" => "profil_syaikh_1"),
            array("text" => "Profil Syaikh 2", "callback_data" => "profil_syaikh_2")
        )
    );

    if ($messageId) {
        // Jika messageId ada, edit pesan
        editMessage($chatId, $messageId, $text, $keyboard);
    } else {
        // Jika tidak ada, kirim pesan baru
        sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }
}

// Fungsi untuk menangani pesan masuk
function handleMessage($update) {
    $message = $update->message;
    $chatId = $message->chat->id;
    $text = $message->text;
    $username = $message->chat->username;

    // Logika pemrosesan pesan
    if ($text == "/start") {
        if (!$username) {
            // Jika username belum diatur, kirim pesan dengan tombol untuk menuju ke settings
            $keyboard = array(
                array(
                    array("text" => "Set Username", "url" => "https://t.me/yourbot?start=settings")
                )
            );
            sendMessageWithInlineKeyboard($chatId, "Halo, selamat datang di bot saya!\nSilakan atur username Anda terlebih dahulu.", $keyboard);
        } else {
            // Tampilkan daftar profil Masyaikh
            showMasyaikhList($chatId);
        }
    } elseif ($text == "/help") {
        sendMessage($chatId, "Berikut adalah beberapa perintah yang tersedia:\n/start - Memulai percakapan\n/help - Menampilkan bantuan");
    } else {
        sendMessage($chatId, "Maaf, saya tidak mengerti perintah tersebut.");
    }
}

// Fungsi untuk menangani callback query dari tombol inline
function handleCallbackQuery($callbackQuery) {
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    // Memproses data callback
    if ($data == "profil_syaikh_1") {
        $newText = "Profil Syaikh 1:\nNama: Syaikh Ahmad\nDeskripsi: Syaikh Ahmad adalah seorang ulama besar...";
        $keyboard = array(
            array(
                array("text" => "Kembali", "callback_data" => "back_to_list")
            )
        );
        editMessage($chatId, $messageId, $newText, $keyboard);
    } elseif ($data == "profil_syaikh_2") {
        $newText = "Profil Syaikh 2:\nNama: Syaikh Ibrahim\nDeskripsi: Syaikh Ibrahim adalah seorang cendekiawan terkenal...";
        $keyboard = array(
            array(
                array("text" => "Kembali", "callback_data" => "back_to_list")
            )
        );
        editMessage($chatId, $messageId, $newText, $keyboard);
    } elseif ($data == "back_to_list") {
        // Tampilkan kembali daftar Masyaikh jika tombol "Kembali" ditekan
        showMasyaikhList($chatId, $messageId);
    } else {
        editMessage($chatId, $messageId, "Profil tidak ditemukan.");
    }
}

// Mendapatkan pembaruan dari Telegram
$update = json_decode(file_get_contents("php://input"));

if (isset($update->message)) {
    handleMessage($update);
} elseif (isset($update->callback_query)) {
    handleCallbackQuery($update->callback_query);
}
