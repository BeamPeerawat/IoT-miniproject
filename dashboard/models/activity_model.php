<?php
function getRecentActivities($pdo) {
    return $pdo->query("
        (SELECT 'sensor' AS type, created_at, 
            CONCAT('อุณหภูมิ: ', temperature, '°C, ความชื้น: ', humidity, '%, แสง: ', light_level, ' Lux, เสียง: ', sound_level, ' dB, การเคลื่อนไหว: ', IF(motion_detected, 'ตรวจพบ', 'ปกติ')) AS description 
        FROM sensors ORDER BY created_at DESC LIMIT 5)
        UNION
        (SELECT 'sound' AS type, created_at, 
            CONCAT('ตรวจพบเสียงดัง: ', sound_level, ' dB') AS description 
        FROM sensors WHERE sound_level > 50 ORDER BY created_at DESC LIMIT 5)
        UNION
        (SELECT 'device' AS type, last_updated AS created_at, 
            CONCAT('เปลี่ยนสถานะ ', name, ' เป็น ', IF(status, 'เปิด', 'ปิด')) AS description 
        FROM devices ORDER BY last_updated DESC LIMIT 5)
        ORDER BY created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>