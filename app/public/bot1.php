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


function copyMessage($chatId, $threadId, $messageId) {
    global $apiUrl;
    
    $data = array(
        'chat_id' => $chatId,
        'message_thread_id' => $threadId,
        'from_chat_id' => $chatId,
        'message_id' => $messageId
    );
    
    $options = array(
        'http' => array(
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        )
    );
    
    $context = stream_context_create($options);
    return file_get_contents($apiUrl . "copyMessage", false, $context);
}


function answerCallbackQuery($callbackQueryId, $text) {
    global $apiUrl;
    
    $data = array(
        'callback_query_id' => $callbackQueryId,
        'text' => $text,
        'show_alert' => true
    );
    
    $options = array(
        'http' => array(
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        )
    );
    
    $context = stream_context_create($options);
    return file_get_contents($apiUrl . "answerCallbackQuery", false, $context);
}

function editMessageReplyMarkup($chatId, $messageId, $keyboard) {
    global $apiUrl;
    
    $data = array(
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'reply_markup' => json_encode($keyboard)
    );
    
    $options = array(
        'http' => array(
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        )
    );
    
    $context = stream_context_create($options);
    return file_get_contents($apiUrl . "editMessageReplyMarkup", false, $context);
}


function showCategorySoal($chatId, $messageId = null) {
    $text = "📜 Peraturan Sederhana Bertanya kepada Masyaikh:

1️⃣ Niat Baik: Bertanya untuk mencari ilmu, bukan untuk menguji.
2️⃣ Hormati: Gunakan bahasa yang sopan dan jelas.
3️⃣ Pertanyaan Singkat: Sampaikan langsung ke inti pertanyaan.
4️⃣ Akhiri dengan Terima Kasih: Ucapkan terima kasih dan doakan kebaikan.

Klik tombol Lanjutkan Untuk Bertanya";

    // Mini App URL
    $webAppUrl = "https://tanyarihlah.bohr.io/tr.html?chat_id=$chatId"; // Ganti dengan URL Web App Anda

    $keyboard = array(
        array(
            array(
                "text" => "Lanjutkan",
                "web_app" => array("url" => $webAppUrl)  // Menggunakan web_app untuk Mini App
            )
        ),
        array(
            array(
                "text" => "🔙 Kembali",
                "callback_data" => "back_to_start"
            )
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
            array("text" => "🔙 Kembali", "callback_data" => "back_to_start")
        )
    );

    editMessageCaption($chatId, $messageId, $text, $keyboard);
}

function handleMessage($update) {
    $message = $update->message;
    $chatId = $message->chat->id;
    $text = $message->text;

    if ($text == "/start") {
        $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
        $welcomeText = "﷽ 

Bot Thalibul Ilmi Bertanya

Bot ini dibuat untuk membantu menyampaikan pertanyaan Antum kepada para masyaikh yang kami temui, insyaallah. Tulis pertanyaan Antum dan nantikan jawabannya di kanal @thalibulilmibertanya...

🔹 Fitur Bot:

Tanya Masyaikh
- Tanyakan permasalahan seputar agama untuk disampaikan kepada para masyaikh.
- Jawaban akan kami unggah di kanal @thalibulilmibertanya agar semua orang bisa mendapatkan faedahnya.

Tanya Admin
- Tanyakan permasalahan seputar serba-serbi Program Rihlah Thalibul Ilmi
- Pertanyaan akan kami jawab secara pribadi

Saran dan Masukan
- Bantu kami untuk terus berkembang dengan memberi saran dan masukan yang membangun

Kontak Kami
- Informasi seputar media dakwah para masyaikh

Mengingat banyaknya pertanyaan yang masuk, mohon maaf jika kami terlambat merespon ya ... Jika Antum punya pertanyaan yang butuh jawaban cepat, kami sarankan untuk bertanya kepada asatidzah setempat secara langsung..

Semoga kami bisa terus mengembangkan layanan kami menjadi lebih baik ...";
        
$keyboard = array(
    array(
        array("text" => "❓ Tanya Masyaikh", "callback_data" => "show_category_soal")
    ),
    array(
        array("text" => "💬 Tanya Admin", "url" => "https://t.me/RihlahThalabulIlmiCS_bot")
    ),
    array(
        array("text" => "💡 Saran dan Masukan", "url" => "https://t.me/RihlahThalabulIlmiCS_bot")
    ),
    array(
        array("text" => "📞 Kontak Kami", "callback_data" => "about_bot")
    )
);

        
        $response = sendStartMessageWithPhoto($chatId, $photoUrl, $welcomeText, $keyboard);
        return $response['result']['message_id'];
    } elseif ($text == "/help") {
        sendMessage($chatId, "Berikut adalah beberapa perintah yang tersedia:\n/start - Memulai percakapan\n/help - Menampilkan bantuan");
    } else {
        // Check if this message is in response to a category selection
        if (isset($message->reply_to_message) && 
            isset($message->reply_to_message->photo) && 
            strpos($message->reply_to_message->caption, '[AKTIF-KATEGORI:') !== false) {
            
            // Extract category from the caption
            preg_match('/\[AKTIF-KATEGORI:(.*?)\]/', $message->reply_to_message->caption, $matches);
            if (isset($matches[1])) {
                $category = trim($matches[1]);
                
                // Process the question
                $questionText = "✅ Pertanyaan Anda telah diterima\n\n" .
                              "Kategori: " . strtoupper($category) . "\n" .
                              "Pertanyaan: " . $text . "\n\n" .
                              "Pertanyaan Anda akan kami sampaikan kepada Masyaikh. " .
                              "Mohon bersabar menunggu jawabannya.";
                
                sendMessage($chatId, $questionText);
                
                // Reset the message to category selection
                showCategorySoal($chatId, $message->reply_to_message->message_id);
                return;
            }
        }
        
        sendMessage($chatId, "Afwan, Perintah tersebut tidak Tersedia. Silakan gunakan /start untuk memulai.");
    }
}

function handleCallbackQuery($callbackQuery) {
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    $categories = array('aqidah', 'fiqh', 'tafsir', 'hadits', 'umum');
    if (in_array($data, $categories)) {
        $text = "Anda telah memilih kategori <b>" . strtoupper($data) . "</b>\n\n" .
                "Silakan ketik pertanyaan Anda sebagai balasan untuk pesan ini.\n\n";
        
        $keyboard = array(
            array(
                array("text" => "Kembali ke Kategori", "callback_data" => "show_category_soal")
            )
        );
        
        editMessageCaption($chatId, $messageId, $text, $keyboard);
        return;
    }

    // Handle action_accept
    if ($data === 'action_accept') {
        $message = $callbackQuery->message;
        $chatId = $message->chat->id;
        $messageId = $message->message_id;
    
        // ID thread tujuan untuk copy
        $targetThreadId = "152"; // Replace with the target thread ID
    
        // Copy the message to the target thread and check if the response is valid
        $copiedMessage = copyMessage($chatId, $targetThreadId, $messageId);
    
        // Check if copyMessage was successful
        if ($copiedMessage === false) {
            // Handle error (e.g., display error message or log error)
            answerCallbackQuery($callbackQuery->id, "Gagal menyalin pesan. Silakan coba lagi.");
            return;
        }
    
        // Decode the JSON response from the Telegram API
        $copiedMessageObj = json_decode($copiedMessage);
    
        // Check if the response was successfully decoded and contains a message_id
        if ($copiedMessageObj && isset($copiedMessageObj->result->message_id)) {
            // Get the message_id from the valid response
            $copiedMessageId = $copiedMessageObj->result->message_id;
    
            // Continue the process as usual
            $copiedMessageLink = "https://t.me/c/{$chatId}/{$copiedMessageId}";
    
            // Confirm callback to the user
            answerCallbackQuery($callbackQuery->id, "Pertanyaan telah disetujui dan disalin");
    
            // Create an inline button for the copied message
            $copiedKeyboard = array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => 'Lihat Pesan Disalin',
                            'url' => $copiedMessageLink
                        )
                    )
                )
            );
    
            // Add the inline button to the copied message in the target thread
            editMessageReplyMarkup($chatId, $copiedMessageId, $copiedKeyboard);
    
            // Update the inline button in the original message to indicate "Sudah Disetujui"
            $keyboard = array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => '✅ Sudah Disetujui',
                            'callback_data' => 'already_accepted'
                        ),
                        $callbackQuery->message->reply_markup->inline_keyboard[0][1] // Keep the "Jawab Pertanyaan" button
                    )
                )
            );
    
            // Update the inline button in the original message
            editMessageReplyMarkup($chatId, $messageId, $keyboard);
    
        } else {
            // Handle cases where message_id was not found or an error occurred
            answerCallbackQuery($callbackQuery->id, "Gagal mengambil ID pesan yang disalin. Mungkin ada masalah dengan API Telegram.");
        }
    }
    
    

    switch($data) {
        
        case "show_category_soal":
            showCategorySoal($chatId, $messageId);
            break;

        
            
        case "show_masyaikh_list":
            $text = "Antum Dapat Melihat Biografi Masyaikh secara Ringkas di sini, Namun Fitur ini Sedang Dalam Tahap Pengembangan.";
            $keyboard = array(
                array(
                    array("text" => "🔙 Kembali", "callback_data" => "back_to_start")
                )
            );
            editMessageCaption($chatId, $messageId, $text, $keyboard);
            break;
            
        case "about_bot":
            $text = "Bot ini dibuat untuk membantu Antum mendapatkan informasi tentang Masyaikh dan bertanya kepada mereka.";
            $keyboard = array(
                array(
                    array("text" => "🔙 Kembali", "callback_data" => "back_to_start")
                )
            );
            editMessageCaption($chatId, $messageId, $text, $keyboard);
            break;
            
        case "back_to_start":
            $photoUrl = "https://tanyarihlah.bohr.io/images/image.png";
            $welcomeText = "﷽ 

Bot Thalibul Ilmi Bertanya

Bot ini dibuat untuk membantu menyampaikan pertanyaan Antum kepada para masyaikh yang kami temui, insyaallah. Tulis pertanyaan Antum dan nantikan jawabannya di kanal @thalibulilmibertanya...

🔹 Fitur Bot:

Tanya Masyaikh
- Tanyakan permasalahan seputar agama untuk disampaikan kepada para masyaikh.
- Jawaban akan kami unggah di kanal @thalibulilmibertanya agar semua orang bisa mendapatkan faedahnya.

Tanya Admin
- Tanyakan permasalahan seputar serba-serbi Program Rihlah Thalibul Ilmi
- Pertanyaan akan kami jawab secara pribadi

Saran dan Masukan
- Bantu kami untuk terus berkembang dengan memberi saran dan masukan yang membangun

Kontak Kami
- Informasi seputar media dakwah para masyaikh

Mengingat banyaknya pertanyaan yang masuk, mohon maaf jika kami terlambat merespon ya ... Jika Antum punya pertanyaan yang butuh jawaban cepat, kami sarankan untuk bertanya kepada asatidzah setempat secara langsung..

Semoga kami bisa terus mengembangkan layanan kami menjadi lebih baik ...";
        
$keyboard = array(
    array(
        array("text" => "❓ Tanya Masyaikh", "callback_data" => "show_category_soal")
    ),
    array(
        array("text" => "💬 Tanya Admin", "url" => "https://t.me/RihlahThalabulIlmiCS_bot")
    ),
    array(
        array("text" => "💡 Saran dan Masukan", "url" => "https://t.me/RihlahThalabulIlmiCS_bot")
    ),
    array(
        array("text" => "📞 Kontak Kami", "callback_data" => "about_bot")
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