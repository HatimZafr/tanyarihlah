<?php
// Konfigurasi bot
$botToken = '7386594817:AAGV5m2lqaRdprOjByO9nnALwzt-LgdA3kI';
$apiUrl = "https://api.telegram.org/bot$botToken/";
// Google Apps Script Web App URL (replace with your actual URL)
$googleScriptUrl = 'https://script.google.com/macros/s/AKfycbyP416urymrNmwmZWB-Mny7Fq12VV_Q12YeN8gvaJuPpGreiTK36gCYnbcYoOUAQf99nQ/exec';

// Fungsi untuk mengirim request ke Google Apps Script
function callGoogleScript($action, $chatId, $state = null, $category = null) {
    global $googleScriptUrl;
    $data = array(
        'action' => $action,
        'chatId' => $chatId,
        'state' => $state,
        'category' => $category
    );
    
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context = stream_context_create($options);
    $response = file_get_contents($googleScriptUrl, false, $context);
    
    if ($response === FALSE) {
        error_log("Error: Tidak dapat mengirim data ke Google Apps Script.");
    } else {
        $decodedResponse = json_decode($response, true);
        error_log("Response from Google Apps Script: " . print_r($decodedResponse, true));
    }
    return json_decode($response, true);
}
// Menyimpan state pengguna ke Google Sheets
function saveUserState($chatId, $state, $category) {
    callGoogleScript('save', $chatId, $state['state'], $state['category']);
}

// Mengambil state pengguna dari Google Sheets
function getUserState($chatId) {
    $response = callGoogleScript('get', $chatId);
    return isset($response) ? $response : null;
}

// Menghapus state pengguna dari Google Sheets
function deleteUserState($chatId) {
    callGoogleScript('delete', $chatId);
}

// [Fungsi sendStartMessageWithPhoto, editMessageCaption, dan sendMessage tetap sama seperti sebelumnya]
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

// Fungsi untuk mengirim pesan biasa
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

// Fungsi untuk mengedit caption gambar yang telah dikirim
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

// Fungsi untuk menampilkan daftar kategori soal
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

// Fungsi untuk menampilkan daftar Masyaikh
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


// Fungsi untuk menangani pesan masuk
function handleMessage($update) {
    $message = $update->message;
    $chatId = $message->chat->id;
    $text = $message->text;

    // Cek state pengguna
    $userState = getUserState($chatId);

    // Jika pengguna sedang dalam mode bertanya
    if ($userState && isset($userState['state']) && $userState['state'] === 'asking') {
        handleQuestion($chatId, $text, $userState['category']);
        return;
    }

    if ($text == "/start") {
        // Reset state pengguna
        if (getUserState($chatId) !== null) {
            deleteUserState($chatId);
        }        
        
        $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
        $welcomeText = "بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ

Bot Interaktif Thalibul Ilmi Bertanya

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
        // Jika tidak dalam mode bertanya dan bukan command valid
        if (!$userState) {
            sendMessage($chatId, "Maaf, saya tidak mengerti perintah tersebut. Silakan gunakan /start untuk memulai.");
        }
    }
}

// Fungsi untuk menangani callback query
function handleCallbackQuery($callbackQuery) {
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    // Handle kategori pertanyaan
    $categories = array('aqidah', 'fiqh', 'tafsir', 'hadits', 'umum');
    if (in_array($data, $categories)) {
        // Simpan state pengguna
        saveUserState($chatId, array(
            'state' => 'asking',
            'category' => $data
        ));
        
        $text = "Anda telah memilih kategori <b>" . strtoupper($data) . "</b>\n\n" .
                "Silakan ketik pertanyaan Anda sekarang. Pertanyaan akan disampaikan kepada Masyaikh sesuai dengan kategori yang dipilih.\n\n" .
                "Untuk membatalkan, tekan tombol 'Kembali ke Kategori' di bawah ini.";
        
        $keyboard = array(
            array(
                array("text" => "Kembali ke Kategori", "callback_data" => "show_category_soal")
            )
        );
        
        editMessageCaption($chatId, $messageId, $text, $keyboard);
        return;
    }

    // Handle other callbacks
    switch($data) {
        case "show_category_soal":
            // Reset state saat kembali ke pemilihan kategori
            deleteUserState($chatId);
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

Bot Interaktif Thalibul Ilmi Bertanya

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

// Fungsi untuk menangani pertanyaan
function handleQuestion($chatId, $question, $category) {
    // Validasi pertanyaan
    if (strlen($question) < 10) {
        sendMessage($chatId, "Mohon maaf, pertanyaan terlalu singkat. Silakan berikan pertanyaan yang lebih detail.");
        return;
    }

    // Format pertanyaan
    $formattedQuestion = "Kategori: " . strtoupper($category) . "\n" .
                        "Pertanyaan: " . $question . "\n" .
                        "Waktu: " . date('Y-m-d H:i:s') . "\n" .
                        "User ID: " . $chatId;

    // Di sini Anda bisa menambahkan kode untuk menyimpan pertanyaan ke database
    // atau mengirimkannya ke admin channel/group

    // Kirim konfirmasi ke pengguna
    $confirmationText = "✅ Pertanyaan Anda telah diterima!\n\n" .
                       "Kategori: <b>" . strtoupper($category) . "</b>\n\n" .
                       "Pertanyaan:\n" . $question . "\n\n" .
                       "InsyaAllah akan kami sampaikan kepada Masyaikh. " .
                       "Jawaban akan dikirimkan melalui bot ini atau channel kami.\n\n" .
                       "Untuk bertanya lagi, silakan pilih kategori kembali.";

    $keyboard = array(
        array(
            array("text" => "Tanya Lagi", "callback_data" => "show_category_soal")
        )
    );

    sendMessage($chatId, $confirmationText, $keyboard);

    // Hapus state setelah pertanyaan selesai diproses
    deleteUserState($chatId);
}

// Entry point - menerima update dari Telegram
$update = json_decode(file_get_contents("php://input"));

if (isset($update->message)) {
    handleMessage($update);
} elseif (isset($update->callback_query)) {
    handleCallbackQuery($update->callback_query);
}
?>