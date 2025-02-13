<?php
function sendTelegramNotification($message) {
    global $telegram_config;
    
    $url = "https://api.telegram.org/bot{$telegram_config['bot_token']}/sendMessage";
    
    $data = [
        'chat_id' => $telegram_config['chat_id'],
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
?>