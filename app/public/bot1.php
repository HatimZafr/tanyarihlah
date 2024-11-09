<?php
// Konfigurasi bot
$botToken = '7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI';
$apiUrl = "https://api.telegram.org/bot$botToken/";

// Variabel global untuk menyimpan status kategori aktif per pengguna
$userStatus = [];

function sendStartMessageWithPhoto($chatId, $photoUrl, $text, $keyboard) {
    global $apiUrl;
    $data = array(
        'chat_id' => $chatId,
        'photo' => $photoUrl,
        'caption' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(array('inline_keyboard' => $keyboard))
    );
    $url = $apiUrl . "sendPhoto";
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    return json_decode($response, true);
}

function sendMessage($chatId, $text, $keyboard = null) {
    global $apiUrl;
    $data = array(
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
    );
    
    if ($keyboard) {
        $data['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard));
    }
    
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context = stream_context_create($options);
    file_get_contents($apiUrl . "sendMessage", false, $context);
}

function editMessageCaption($chatId, $messageId, $newCaption, $keyboard = null) {
    global $apiUrl;
    $data = array(
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'caption' => $newCaption,
        'parse_mode' => 'HTML'
    );

    if ($keyboard) {
        $data['reply_markup'] = json_encode(array('inline_keyboard' => $keyboard));
    }

    $url = $apiUrl . "editMessageCaption";
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

function showCategorySoal($chatId, $messageId = null) {
    global $userStatus;
    $userStatus[$chatId] = null;  // Reset status kategori saat kembali ke menu kategori

    $text = "Pilih Kategori Pertanyaan Antum:";
    $keyboard = array(
        array(
            array("text" => "Aqidah", "callback_data" => "aqidah"),
            array("text" => "Fiqh", "callback_data" => "fiqh")
        ),
        array(
            array("text" => "Tafsir", "callback_data" => "tafsir"),
            array("text" => "Hadits", "callback_data" => "hadits")
        ),
        array(
            array("text" => "Umum", "callback_data" => "umum")
        ),
        array(
            array("text" => "Kembali ke Menu Utama", "callback_data" => "back_to_start")
        )
    );

    editMessageCaption($chatId, $messageId, $text, $keyboard);
}

function showMasyaikhList($chatId, $messageId = null) {
    $text = "Pilih profil Masyaikh yang ingin Anda lihat:";
    $keyboard = array(
        array(
            array("text" => "Profil Syaikh 1", "callback_data" => "profil_syaikh_1"),
            array("text" => "Profil Syaikh 2", "callback_data" => "profil_syaikh_2")
        ),
        array(
            array("text" => "Kembali", "callback_data" => "back_to_start")
        )
    );

    editMessageCaption($chatId, $messageId, $text, $keyboard);
}


function handleMessage($update) {
    global $userStatus;
    $message = $update->message;
    $chatId = $message->chat->id;
    $text = $message->text;

    if ($text == "/start") {
        $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
        $welcomeText = "بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ
Bot Thalibul Ilmi Bertanya

Bot ini dibuat untuk membantu menyampaikan pertanyaan Anda kepada para masyaikh yang kami temui, insyaAllah. Setiap pertanyaan yang berasal dari rasa ingin tahu, insyaAllah akan dijawab oleh para ulama yang kompeten.

🔹 Cara Kerja Bot:

1️⃣ Mengumpulkan pertanyaan dari pengikut Program Rihlah Thalabul Ilmi untuk disampaikan langsung kepada masyaikh.
2️⃣ Menyajikan jawaban dari para masyaikh dalam bentuk audio dan ringkasan jawaban.

❓ Silakan kirim pertanyaan Anda melalui bot ini, dan kami akan bantu menyampaikannya.

Semoga Allah memberikan kita ilmu yang bermanfaat.";
        
        $keyboard = array(
            array(
                array("text" => "Bertanya", "callback_data" => "show_category_soal")),
            array(
                array("text" => "Biografi Masyaikh", "callback_data" => "show_masyaikh_list")),
            array(
                array("text" => "Tentang Bot", "callback_data" => "about_bot")
            )
        );
        
        $response = sendStartMessageWithPhoto($chatId, $photoUrl, $welcomeText, $keyboard);
        return $response['result']['message_id'];
    } elseif ($text == "/help") {
        sendMessage($chatId, "Berikut adalah beberapa perintah yang tersedia:\n/start - Memulai percakapan\n/help - Menampilkan bantuan");
    } else {
        if (isset($userStatus[$chatId])) {
            $category = strtoupper($userStatus[$chatId]);
            $questionText = "✅ Pertanyaan Anda telah diterima\n\n" .
                            "Kategori: $category\n" .
                            "Pertanyaan: $text\n\n" .
                            "Pertanyaan Anda akan kami sampaikan kepada Masyaikh.";
            sendMessage($chatId, $questionText);
            showCategorySoal($chatId);
        } else {
            sendMessage($chatId, "Silakan pilih kategori pertanyaan dengan menekan tombol 'Bertanya' pada menu.");
        }
    }
}

function handleCallbackQuery($callbackQuery) {
    global $userStatus;
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    $categories = array('aqidah', 'fiqh', 'tafsir', 'hadits', 'umum');
    if (in_array($data, $categories)) {
        $userStatus[$chatId] = $data;
        $text = "Anda telah memilih kategori <b>" . strtoupper($data) . "</b>\n\n" .
                "Silakan ketik pertanyaan Anda sebagai balasan untuk pesan ini.";
        $keyboard = array(
            array(
                array("text" => "Kembali ke Kategori", "callback_data" => "show_category_soal")
            )
        );
        
        editMessageCaption($chatId, $messageId, $text, $keyboard);
        return;
    }

    switch($data) {
        case "show_category_soal":
            showCategorySoal($chatId, $messageId);
            break;
            
        case "show_masyaikh_list":
            $text = "Antum Dapat Melihat Biografi Masyaikh secara Ringkas di sini. Fitur ini Sedang Dalam Pengembangan.";
            $keyboard = array(
                array(
                    array("text" => "Kembali", "callback_data" => "back_to_start")
                )
            );
            editMessageCaption($chatId, $messageId, $text, $keyboard);
            break;
            
        case "about_bot":
            $text = "Bot ini dibuat untuk membantu Anda mendapatkan informasi tentang Masyaikh dan bertanya kepada mereka.";
            $keyboard = array(
                array(
                    array("text" => "Kembali", "callback_data" => "back_to_start")
                )
            );
            editMessageCaption($chatId, $messageId, $text, $keyboard);
            break;
            
        case "back_to_start":
            $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
            $welcomeText = "بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ

Bot Thalibul Ilmi Bertanya

Bot ini dibuat untuk membantu menyampaikan pertanyaan Anda kepada para masyaikh yang kami temui, insyaAllah. Setiap pertanyaan yang berasal dari rasa ingin tahu, insyaAllah akan dijawab oleh para ulama yang kompeten.

🔹 Cara Kerja Bot:

1️⃣ Mengumpulkan pertanyaan dari pengikut Program Rihlah Thalabul Ilmi untuk disampaikan langsung kepada masyaikh.
2️⃣ Menyajikan jawaban dari para masyaikh dalam bentuk audio dan ringkasan jawaban.

❓ Silakan kirim pertanyaan Anda melalui bot ini, dan kami akan bantu menyampaikannya.

Semoga Allah memberikan kita ilmu yang bermanfaat.";

$keyboard = array(
                array(
                    array("text" => "Bertanya", "callback_data" => "show_category_soal")),
                array(
                    array("text" => "Biografi Masyaikh", "callback_data" => "show_masyaikh_list")),
                array(
                    array("text" => "Tentang Bot", "callback_data" => "about_bot")
                )
            );
            editMessageCaption($chatId, $messageId, $welcomeText, $keyboard);
            break;
    }
}

// Entry point
$update = json_decode(file_get_contents("php://input"));

if (isset($update->message)) {
    handleMessage($update);
} elseif (isset($update->callback_query)) {
    handleCallbackQuery($update->callback_query);
}
?>
