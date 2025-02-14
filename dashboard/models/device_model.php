<?php
function getAllDevices($pdo) {
    return $pdo->query("SELECT * FROM devices")->fetchAll(PDO::FETCH_ASSOC);
}
?>