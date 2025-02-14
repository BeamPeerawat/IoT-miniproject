<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require __DIR__.'/../config/database.php';
require __DIR__.'/../config/telegram_config.php'; 

$response = ['status' => 'error', 'message' => 'Invalid request'];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $apiKey = $_GET['api_key'] ?? '';

    if ($apiKey !== $api_key) {
        throw new Exception('Unauthorized', 401);
    }

    switch ($method) {
        case 'GET':
            // ดึงข้อมูลอุปกรณ์ทั้งหมด
            $stmt = $pdo->query("SELECT * FROM devices");
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['status' => 'success', 'data' => $devices];
            break;

            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);
            
                // ตรวจสอบค่าที่จำเป็น
                if (!isset($data['action'], $data['device_id'], $data['status'])) {
                    throw new Exception('Missing required data', 400);
                }
            
                if ($data['action'] !== 'update') {
                    throw new Exception('Invalid action', 400);
                }
            
                // ตรวจสอบค่า device_id และ status
                if (!is_numeric($data['device_id']) || !in_array($data['status'], [0, 1])) {
                    throw new Exception('Invalid device_id or status', 400);
                }
            
                // ตรวจสอบว่าอุปกรณ์มีอยู่จริงหรือไม่
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM devices WHERE id = :id");
                $stmt->execute([':id' => $data['device_id']]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('Device not found', 404);
                }
            
                // อัปเดตสถานะอุปกรณ์
                $manualOverride = isset($data['manual_override']) ? (int)$data['manual_override'] : 0; // แปลงเป็น integer (0 หรือ 1)
                $stmt = $pdo->prepare("UPDATE devices 
                    SET status = :status, 
                        last_updated = NOW(), 
                        manual_override = :manual_override 
                    WHERE id = :id");
            
                $stmt->bindParam(':id', $data['device_id'], PDO::PARAM_INT);
                $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
                $stmt->bindParam(':manual_override', $manualOverride, PDO::PARAM_INT);
                $stmt->execute();
            
                $response = ['status' => 'success', 'message' => 'Device updated', 'manual_override' => $manualOverride];
                break;

        default:
            throw new Exception('Method not allowed', 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>