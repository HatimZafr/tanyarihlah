<?php
// Konfigurasi bot
$botToken = '8098224140:AAHQhrpK2yQHdvgHQ7zrpAeup-qv_6gBMew';
$apiUrl = "https://api.telegram.org/bot$botToken/";

session_start();

// Pesan dalam berbagai bahasa
$messages = [
    'en' => [
        'welcome' => "In the name of Allah, the Most Gracious, the Most Merciful\n\nBot Thalibul Ilmi Bertanya\n\nThis bot helps you ask questions to the scholars we meet, inshaAllah.",
        'help' => "Here are the available commands:\n/start - Start the bot\n/help - Show help",
        'about' => "This bot helps you get information about the scholars and ask them questions.",
        'category_rules' => "📜 Simple Rules for Asking Scholars:\n1️⃣ Good Intent: Ask for knowledge, not to test.\n2️⃣ Respect: Use polite and clear language.\n3️⃣ Ask Briefly: Get to the point.\n4️⃣ End with Thanks: Say thank you and pray for good."
    ],
    'id' => [
        'welcome' => "بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ\n\nBot Thalibul Ilmi Bertanya\n\nBot ini membantu Anda mengajukan pertanyaan kepada para masyaikh yang kami temui, insyaAllah.",
        'help' => "Berikut adalah beberapa perintah yang tersedia:\n/start - Memulai percakapan\n/help - Menampilkan bantuan",
        'about' => "Bot ini membantu Anda mendapatkan informasi tentang masyaikh dan bertanya kepada mereka.",
        'category_rules' => "📜 Peraturan Sederhana Bertanya kepada Masyaikh:\n1️⃣ Niat Baik: Bertanya untuk mencari ilmu, bukan untuk menguji.\n2️⃣ Hormati: Gunakan bahasa yang sopan dan jelas.\n3️⃣ Pertanyaan Singkat: Sampaikan langsung ke inti pertanyaan.\n4️⃣ Akhiri dengan Terima Kasih: Ucapkan terima kasih dan doakan kebaikan."
    ],
    'ar' => [
        'welcome' => "بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ\n\nبوت طالب العلم للسؤال\n\nيتم مساعدتك لطرح الأسئلة على العلماء الذين نلتقي بهم، إن شاء الله.",
        'help' => "إليك بعض الأوامر المتاحة:\n/start - بدء المحادثة\n/help - عرض المساعدة",
        'about' => "هذا البوت يساعدك في الحصول على معلومات عن العلماء وطرح الأسئلة عليهم.",
        'category_rules' => "📜 القواعد البسيطة لطرح الأسئلة على العلماء:\n1️⃣ نية طيبة: اسأل طلبًا للعلم، وليس للاختبار.\n2️⃣ الاحترام: استخدم لغة مهذبة وواضحة.\n3️⃣ اسأل بإيجاز: توجه مباشرة إلى السؤال.\n4️⃣ أنهِ بالشكر: قل شكرًا وادعُ بالخير."
    ]
];

// Fungsi untuk mengatur bahasa
function setLanguage($chatId, $language) {
    $_SESSION['user_language'][$chatId] = $language;
}

function getLanguage($chatId) {
    return $_SESSION['user_language'][$chatId] ?? 'en'; // Default ke bahasa Inggris
}

// Mengirim pesan dengan pilihan bahasa
function sendMessage($chatId, $text, $keyboard = null) {
    global $apiUrl, $messages;

    // Mengambil bahasa pengguna
    $language = getLanguage($chatId);

    // Pilih pesan berdasarkan bahasa pengguna
    $messageText = $messages[$language]['welcome'];  // Misalnya pesan selamat datang
    
    $data = array(
        'chat_id' => $chatId,
        'text' => $messageText,
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

// Menampilkan pilihan bahasa
function showLanguageSelection($chatId) {
    $keyboard = array(
        array(
            array("text" => "🇬🇧 English", "callback_data" => "set_language_en"),
            array("text" => "🇮🇩 Bahasa Indonesia", "callback_data" => "set_language_id"),
            array("text" => "🇸🇦 العربية", "callback_data" => "set_language_ar")
        )
    );

    $text = "Please select your language / يرجى اختيار لغتك / Silakan pilih bahasa Anda";

    sendMessage($chatId, $text, $keyboard);
}

// Menangani perintah callback (termasuk pilihan bahasa)
function handleCallbackQuery($callbackQuery) {
    $chatId = $callbackQuery->message->chat->id;
    $messageId = $callbackQuery->message->message_id;
    $data = $callbackQuery->data;

    // Tangani pilihan bahasa
    if (strpos($data, 'set_language_') === 0) {
        $language = str_replace('set_language_', '', $data);
        setLanguage($chatId, $language);
        sendMessage($chatId, "Language has been set to " . strtoupper($language), null);
    }
}

// Menangani pesan dari pengguna
function handleMessage($update) {
    $message = $update->message;
    $chatId = $message->chat->id;
    $text = $message->text;

    if ($text == "/start") {
        // Pilih bahasa pertama kali
        showLanguageSelection($chatId);
    } elseif ($text == "/help") {
        sendMessage($chatId, "Here are the available commands:\n/start - Start the bot\n/help - Show help");
    } else {
        sendMessage($chatId, "Afwan, Perintah tersebut tidak Tersedia. Silakan gunakan /start untuk memulai.");
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