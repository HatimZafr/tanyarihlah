<?php
// Konfigurasi bot
$botToken = '7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI';
$apiUrl = "https://api.telegram.org/bot$botToken/";

// Fungsi untuk mengirim pesan dengan foto
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

// Fungsi untuk mengirim pesan tanpa foto
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

// Fungsi untuk mengedit caption pesan
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

// Fungsi untuk menampilkan kategori soal
function showCategorySoal($chatId, $messageId = null) {
    $text = "Pilih Kategori Pertanyaan Anda:";
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

// Fungsi untuk menangani callback query
function handleCallbackQuery($callbackQuery) {
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    $categories = array('aqidah', 'fiqh', 'tafsir', 'hadits', 'umum');

    // Jika data callback adalah kategori, simpan kategori dan arahkan pengguna untuk memberi pertanyaan
    if (in_array($data, $categories)) {
        $text = "Anda telah memilih kategori <b>" . strtoupper($data) . "</b>\n\n" .
                "Silakan ketik pertanyaan Anda sebagai balasan untuk pesan ini.\n\n";

        // Kirim pesan kepada pengguna dengan force reply untuk meminta pertanyaan
        $keyboard = array(
            array(
                array("text" => "Kembali ke Kategori", "callback_data" => "show_category_soal")
            )
        );
        
        // Force reply markup untuk memastikan pengguna dapat memberikan pertanyaan
        $replyMarkup = json_encode(array('force_reply' => true));

        // Edit caption untuk memberi tahu pengguna bahwa mereka perlu mengirimkan pertanyaan
        editMessageCaption($chatId, $messageId, $text, $keyboard);
        return;
    }

    switch($data) {
        case "show_category_soal":
            showCategorySoal($chatId, $messageId);
            break;
            
        case "back_to_start":
            // kembali ke menu utama
            $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
            $welcomeText = "Selamat datang di Bot Thalibul Ilmi!";
            $keyboard = array(
                array(
                    array("text" => "Bertanya", "callback_data" => "show_category_soal")),
                array(
                    array("text" => "Biografi Masyaikh", "callback_data" => "show_masyaikh_list")),
                array(
                    array("text" => "Tentang Bot", "callback_data" => "about_bot")
                )
            );
            sendStartMessageWithPhoto($chatId, $photoUrl, $welcomeText, $keyboard);
            break;
    }
}

// Fungsi untuk menangani pesan
function handleMessage($update) {
    $message = $update->message;
    $chatId = $message->chat->id;
    $text = $message->text;

    // Cek apakah ini adalah balasan yang meminta pertanyaan dengan force reply
    if (isset($message->reply_to_message) && 
        isset($message->reply_to_message->caption) && 
        strpos($message->reply_to_message->caption, "Silakan ketik pertanyaan Anda") !== false) {

        // Ambil kategori dari callback_data di reply yang diterima
        $category = getCategoryFromCallback($message->reply_to_message->reply_markup);

        if (!$category) {
            sendMessage($chatId, "Maaf, kategori tidak ditemukan. Silakan pilih kategori terlebih dahulu.");
            return;
        }

        // Tentukan ID grup admin dan ID thread berdasarkan kategori
        $chat_id_group_admin = '2177994977';  // Ganti dengan ID grup admin Anda
        $idtopic = getThreadIdForCategory($category); // Fungsi untuk mendapatkan thread ID berdasarkan kategori

        // Kirim pertanyaan ke grup admin di thread yang sesuai
        $forward_params = [
            'chat_id' => $chat_id_group_admin,
            'from_chat_id' => $chatId, // ID chat dari pesan yang diteruskan
            'message_id' => $message->message_id, // ID pesan yang diteruskan
            'message_thread_id' => $idtopic, // ID thread tempat pesan akan diteruskan
        ];

        // Meneruskan pesan ke grup admin
        $response_forward = file_get_contents("https://api.telegram.org/bot7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI/forwardMessage?" . http_build_query($forward_params));

        // Mengkonfirmasi kepada pengguna bahwa pertanyaan mereka telah diteruskan
        sendMessage($chatId, "✅ Pertanyaan Anda telah diteruskan kepada Masyaikh. Mohon bersabar menunggu jawabannya.");
        
        return;
    }

    // Handle /start, /help, etc.
    if ($text == "/start") {
        $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
        $welcomeText = "Selamat datang di Bot Thalibul Ilmi!";
        $keyboard = array(
            array(
                array("text" => "Bertanya", "callback_data" => "show_category_soal")),
            array(
                array("text" => "Biografi Masyaikh", "callback_data" => "show_masyaikh_list")),
            array(
                array("text" => "Tentang Bot", "callback_data" => "about_bot")
            )
        );

        sendStartMessageWithPhoto($chatId, $photoUrl, $welcomeText, $keyboard);
    }elseif ($text == "/help") {
        sendMessage($chatId, "Berikut adalah beberapa perintah yang tersedia:\n/start - Memulai percakapan\n/help - Menampilkan bantuan");
    }
}

// Mendapatkan kategori dari callback_data
function getCategoryFromCallback($replyMarkup) {
    $buttons = json_decode($replyMarkup, true);
    if (isset($buttons['inline_keyboard'])) {
        foreach ($buttons['inline_keyboard'] as $row) {
            foreach ($row as $button) {
                if (isset($button['callback_data']) && in_array($button['callback_data'], ['aqidah', 'fiqh', 'tafsir', 'hadits', 'umum'])) {
                    return $button['callback_data'];
                }
            }
        }
    }
    return null;
}

// Mendapatkan ID thread berdasarkan kategori
function getThreadIdForCategory($category) {
    $threads = array(
        'aqidah' => 3,
        'fiqh' => 6,
        'tafsir' => 8,
        'hadits' => 9,
        'umum' => 11
    );

    return isset($threads[$category]) ? $threads[$category] : 0; // 0 jika kategori tidak ditemukan
}

// Mendapatkan update terbaru
$update = json_decode(file_get_contents('php://input'));

if (isset($update->callback_query)) {
    handleCallbackQuery($update->callback_query);
} elseif (isset($update->message)) {
    handleMessage($update);
}
?>
