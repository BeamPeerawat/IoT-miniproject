<?php
function getLatestSensor($pdo) {
    return $pdo->query("SELECT * FROM sensors ORDER BY created_at DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}

function getSensorsLast10Minutes($pdo) {
    return $pdo->query("SELECT * FROM sensors WHERE created_at >= NOW() - INTERVAL 10 MINUTE ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
}
?>