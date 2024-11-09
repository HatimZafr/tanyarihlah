<?php
// Konfigurasi bot
$botToken = '7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI';
$apiUrl = "https://api.telegram.org/bot$botToken/";
$cacheDir = __DIR__ . '/cache/';

// Pastikan direktori cache ada
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// Fungsi untuk mengelola state user
function getUserState($chatId) {
    global $cacheDir;
    $cacheFile = $cacheDir . 'user_' . $chatId . '.json';
    if (file_exists($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    return null;
}

function setUserState($chatId, $state) {
    global $cacheDir;
    $cacheFile = $cacheDir . 'user_' . $chatId . '.json';
    file_put_contents($cacheFile, json_encode($state));
}

function clearUserState($chatId) {
    global $cacheDir;
    $cacheFile = $cacheDir . 'user_' . $chatId . '.json';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

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

function editMessageText($chatId, $messageId, $newText, $keyboard = null) {
    global $apiUrl;
    $data = array(
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => $newText,
        'parse_mode' => 'HTML'
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
            array("text" => "Umum", "callback_data" => "umum")
        ),
        array(
            array("text" => "Kembali", "callback_data" => "back_to_start")
        )
    );

    if ($messageId) {
        editMessageCaption($chatId, $messageId, $text, $keyboard);
    } else {
        sendMessage($chatId, $text, $keyboard);
    }
}

function showMasyaikhList($chatId, $messageId) {
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

    // Cek state user
    $userState = getUserState($chatId);

    if ($text == "/start") {
        // Clear any existing state when starting new session
        clearUserState($chatId);
        
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
                array("text" => "Bertanya", "callback_data" => "show_category_soal")
            ),
            array(
                array("text" => "Biografi Masyaikh", "callback_data" => "show_masyaikh_list")
            ),
            array(
                array("text" => "Tentang Bot", "callback_data" => "about_bot")
            )
        );
        
        sendStartMessageWithPhoto($chatId, $photoUrl, $welcomeText, $keyboard);
    } else if ($userState && isset($userState['state']) && $userState['state'] === 'waiting_question') {
        // User sedang dalam mode bertanya
        $category = $userState['category'];
        
        // Proses pertanyaan
        $questionText = "✅ Pertanyaan Anda telah diterima\n\n" .
                       "Kategori: " . strtoupper($category) . "\n" .
                       "Pertanyaan: " . $text . "\n\n" .
                       "Pertanyaan Anda akan kami sampaikan kepada Masyaikh. " .
                       "Mohon bersabar menunggu jawabannya.";
        
        sendMessage($chatId, $questionText);
        
        // Tampilkan opsi untuk bertanya lagi atau kembali
        $keyboard = array(
            array(
                array("text" => "Tanya Lagi", "callback_data" => "ask_again_" . $category),
                array("text" => "Kembali ke Menu", "callback_data" => "back_to_start")
            )
        );
        
        sendMessage($chatId, "Pilih opsi selanjutnya:", $keyboard);
        
        // Clear state setelah pertanyaan diproses
        clearUserState($chatId);
    } else {
        sendMessage($chatId, "Silakan gunakan menu yang tersedia atau ketik /start untuk memulai.");
    }
}

function handleCallbackQuery($callbackQuery) {
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    $categories = array('aqidah', 'fiqh', 'tafsir', 'hadits', 'umum');
    if (in_array($data, $categories)) {
        // Set state user ke mode bertanya
        setUserState($chatId, array(
            'state' => 'waiting_question',
            'category' => $data
        ));
        
        $text = "Anda telah memilih kategori <b>" . strtoupper($data) . "</b>\n\n" .
                "Silakan ketik pertanyaan Anda sekarang.\n\n" .
                "Untuk kembali ke menu utama, silakan klik tombol di bawah.";
        
        $keyboard = array(
            array(
                array("text" => "Kembali ke Menu Utama", "callback_data" => "back_to_start")
            )
        );
        
        editMessageCaption($chatId, $messageId, $text, $keyboard);
        return;
    }

    if (strpos($data, 'ask_again_') === 0) {
        $category = substr($data, 10);
        setUserState($chatId, array(
            'state' => 'waiting_question',
            'category' => $category
        ));
        
        $text = "Silakan ajukan pertanyaan baru Anda untuk kategori <b>" . strtoupper($category) . "</b>";
        sendMessage($chatId, $text);
        return;
    }

    switch($data) {
        case "show_category_soal":
            showCategorySoal($chatId, $messageId);
            clearUserState($chatId);
            break;
            
        case "show_masyaikh_list":
            showMasyaikhList($chatId, $messageId);
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
            clearUserState($chatId);
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
                    array("text" => "Bertanya", "callback_data" => "show_category_soal")
                ),
                array(
                    array("text" => "Biografi Masyaikh", "callback_data" => "show_masyaikh_list")
                ),
                array(
                    array("text" => "Tentang Bot", "callback_data" => "about_bot")
                )
            );
            editMessageCaption($chatId, $messageId, $welcomeText, $keyboard);
            break;

        case "profil_syaikh_1":
        case "profil_syaikh_2":
            $text = "Profil Masyaikh sedang dalam tahap pengembangan.";
            $keyboard = array(
                array(
                    array("text" => "Kembali", "callback_data" => "show_masyaikh_list")
                )
            );
            editMessageCaption($chatId, $messageId, $text, $keyboard);
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