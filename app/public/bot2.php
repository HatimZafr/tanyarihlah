<?php
// Konfigurasi bot
$botToken = '7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI';
$apiUrl = "https://api.telegram.org/bot$botToken/";

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
            array("text" => "Umum", "callback_data" => "umum")),
        array(
            array("text" => "Kembali", "callback_data" => "back_to_start")
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
    $message = $update->message;
    $chatId = $message->chat->id;
    $text = $message->text;
    
    // Check if there's a reply to a message
    if (isset($message->reply_to_message)) {
        $replyToMessage = $message->reply_to_message;
        if (strpos($replyToMessage->text, '[KATEGORI:') !== false) {
            // Extract category from the original message
            preg_match('/\[KATEGORI:(.*?)\]/', $replyToMessage->text, $matches);
            $category = trim($matches[1]);
            
            // Process the question
            $questionText = "✅ Pertanyaan Anda telah diterima\n\n" .
                          "Kategori: " . strtoupper($category) . "\n" .
                          "Pertanyaan: " . $text . "\n\n" .
                          "Pertanyaan Anda akan kami sampaikan kepada Masyaikh. " .
                          "Mohon bersabar menunggu jawabannya.";
            
            sendMessage($chatId, $questionText);
            
            // Send another message allowing them to ask a new question
            $newQuestionText = "Untuk mengajukan pertanyaan baru, silakan pilih kategori kembali:";
            $keyboard = array(
                array(
                    array("text" => "Pilih Kategori Baru", "callback_data" => "show_category_soal")
                )
            );
            sendMessage($chatId, $newQuestionText, $keyboard);
            return;
        }
    }

    if ($text == "/start") {
        $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
        $welcomeText = "بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ

Bot Thalibul Ilmi Bertanya

Bot ini dibuat untuk membantu menyampaikan pertanyaan Anda kepada para masyaikh yang kami temui, insyaAllah. Setiap pertanyaan yang berasal dari rasa ingin tahu, insyaAllah akan dijawab oleh para ulama yang kompeten.

🔹 Cara Kerja Bot:

1️⃣ Mengumpulkan pertanyaan dari pengikut Program Rihlah Thalabul Ilmi untuk disampaikan langsung kepada masyaikh.
2️⃣ Menyajikan jawaban dari para masyaikh dalam bentuk audio dan ringkasan jawaban.
3️⃣ Selain itu, bot ini juga menampilkan jawaban dari pertanyaan-pertanyaan penting yang kami dapatkan selama belajar bersama para masyaikh.

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
        sendMessage($chatId, "Maaf, saya tidak mengerti perintah tersebut. Silakan gunakan /start untuk memulai.");
    }
}

function handleCallbackQuery($callbackQuery) {
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    $categories = array('aqidah', 'fiqh', 'tafsir', 'hadits', 'umum');
    if (in_array($data, $categories)) {
        $text = "Anda telah memilih kategori <b>" . strtoupper($data) . "</b>\n\n" .
                "Silakan ketik pertanyaan Anda sebagai balasan pesan ini.\n\n" .
                "[KATEGORI:" . $data . "]";
        
        $keyboard = array(
            array(
                array("text" => "Pilih Kategori Lain", "callback_data" => "show_category_soal")
            )
        );
        
        sendMessage($chatId, $text, $keyboard);
        return;
    }

    switch($data) {
        case "show_category_soal":
            showCategorySoal($chatId, $messageId);
            break;
            
        case "show_masyaikh_list":
            $text = "Antum Dapat Melihat Biografi Masyaikh secara Ringkas di sini, Namun Fitur ini Sedang Dalam Tahap Pengembangan.";
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
3️⃣ Selain itu, bot ini juga menampilkan jawaban dari pertanyaan-pertanyaan penting yang kami dapatkan selama belajar bersama para masyaikh.

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

// Entry point - menerima update dari Telegram
$update = json_decode(file_get_contents("php://input"));

if (isset($update->message)) {
    handleMessage($update);
} elseif (isset($update->callback_query)) {
    handleCallbackQuery($update->callback_query);
}
?>