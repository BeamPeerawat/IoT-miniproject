<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require __DIR__.'/../config/database.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $apiKey = $_GET['api_key'] ?? '';
    
    if ($apiKey !== $api_key) {
        throw new Exception('Unauthorized', 401);
    }

    switch ($method) {
        case 'POST':
            // อ่านข้อมูลจาก body ของ request
            $data = json_decode(file_get_contents("php://input"), true);
            
            // ตรวจสอบว่า JSON ถูกต้องหรือไม่
            if ($data === null) {
                throw new Exception('Invalid JSON received', 400);
            }

            // ตรวจสอบว่า key ทุกตัวมีค่า
            if (!isset($data['temperature'], $data['humidity'], $data['light_level'], $data['motion_detected'], $data['sound_level'])) {
                throw new Exception('Missing required data', 400);
            }

            // บันทึกข้อมูลเซนเซอร์ลงฐานข้อมูล
            $stmt = $pdo->prepare("
                INSERT INTO sensors 
                (temperature, humidity, light_level, motion_detected, sound_level)
                VALUES (:temp, :hum, :light, :motion, :sound)
            ");
            
            $stmt->execute([
                ':temp' => $data['temperature'],
                ':hum' => $data['humidity'],
                ':light' => $data['light_level'],
                ':motion' => $data['motion_detected'],
                ':sound' => $data['sound_level']
            ]);

            // เรียกใช้ automation.php เพื่ออัปเดตสถานะอุปกรณ์
            require __DIR__.'/automation.php';

            // ส่งข้อมูลที่บันทึกสำเร็จ
            $response = ['status' => 'success', 'message' => 'Data recorded'];
            break;

        case 'GET':
            // ดึงข้อมูลเซนเซอร์ทั้งหมดจากฐานข้อมูล
            $stmt = $pdo->query("SELECT * FROM sensors ORDER BY created_at DESC");
            $sensors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ส่งข้อมูลเซนเซอร์ทั้งหมด
            $response = ['status' => 'success', 'data' => $sensors];
            break;

        default:
            throw new Exception('Method not allowed', 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
