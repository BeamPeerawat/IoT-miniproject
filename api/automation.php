<?php
require __DIR__.'/../config/database.php';
require __DIR__.'/../config/telegram_config.php';
require __DIR__.'/../helpers/telegram_notify.php';

// กำหนดค่าคงที่
define('TEMP_HIGH', 31.0);      // อุณหภูมิสูงสุด
define('TEMP_LOW', 31.0);       // อุณหภูมิต่ำสุด
define('LIGHT_THRESHOLD', 2000);  // ค่าแสงน้อย
define('MOTION_THRESHOLD', 3000); // ค่าการเคลื่อนไหว
define('SOUND_THRESHOLD', 3000);  // ค่าเสียง

class HomeAutomation {
    private $pdo;
    private $sensor;
    private $devices;
    private $newStatus = [];

    // กำหนด ID อุปกรณ์เป็นค่าคงที่
    const DEVICE_LIGHT = 1; // ไฟนอกบ้าน
    const DEVICE_INDOOR_LIGHT = 2; // ไฟในบ้าน
    const DEVICE_FAN = 3; // พัดลม

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function getSensorData() {
        $stmt = $this->pdo->prepare("SELECT * FROM sensors ORDER BY created_at DESC LIMIT 1");
        $stmt->execute();
        $this->sensor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$this->sensor) {
            throw new Exception("No sensor data available");
        }
    }

    private function getDevices() {
        $stmt = $this->pdo->prepare("SELECT * FROM devices");
        $stmt->execute();
        $this->devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function checkTemperature() {
        if ($this->sensor['temperature'] > TEMP_HIGH) {
            $this->newStatus[self::DEVICE_FAN] = 1; // เปิดพัดลม
            sendTelegramNotification(
                "🌡️ <b>แจ้งเตือนอุณหภูมิสูง</b>\n" .
                "อุณหภูมิ: {$this->sensor['temperature']}°C\n" .
                "การทำงาน: เปิดพัดลมอัตโนมัติ"
            );
        } elseif ($this->sensor['temperature'] < TEMP_LOW) {
            $this->newStatus[self::DEVICE_FAN] = 0; // ปิดพัดลม
        }
    }

    private function checkLight() {
        if ($this->sensor['light_level'] < LIGHT_THRESHOLD) {
            $this->newStatus[self::DEVICE_LIGHT] = 1; // เปิดไฟนอกบ้าน
            sendTelegramNotification(
                "💡 <b>แจ้งเตือนแสงสว่าง</b>\n" .
                "ระดับแสง: {$this->sensor['light_level']}\n" .
                "การทำงาน: เปิดไฟนอกบ้านอัตโนมัติ"
            );
        } else {
            $this->newStatus[self::DEVICE_LIGHT] = 0; // ปิดไฟนอกบ้าน
        }
    }

    private function checkMotion() {
        if ($this->sensor['motion_detected'] > MOTION_THRESHOLD) {
            $this->newStatus[self::DEVICE_INDOOR_LIGHT] = 1; // เปิดไฟในบ้าน
            sendTelegramNotification(
                "🚨 <b>แจ้งเตือนตรวจพบการเคลื่อนไหว</b>\n" .
                "ค่าการเคลื่อนไหว: {$this->sensor['motion_detected']}\n" .
                "การทำงาน: เปิดไฟในบ้านอัตโนมัติ"
            );
        } else {
            $this->newStatus[self::DEVICE_INDOOR_LIGHT] = 0; // ปิดไฟในบ้าน
        }
    }

    private function checkSound() {
        if ($this->sensor['sound_level'] > SOUND_THRESHOLD) {
            $this->newStatus[self::DEVICE_FAN] = 1; // เปิดพัดลม
            $this->newStatus[self::DEVICE_INDOOR_LIGHT] = 1; // เปิดไฟในบ้าน

            sendTelegramNotification(
                "📢 <b>แจ้งเตือนเสียงดัง</b>\n" .
                "ระดับเสียง: {$this->sensor['sound_level']}\n" .
                "การทำงาน: เปิดไฟและพัดลมอัตโนมัติ"
            );

            // ตั้งเวลาปิดอัตโนมัติ 5 วินาที
            $this->pdo->prepare(
                "UPDATE devices SET auto_off_time = DATE_ADD(NOW(), INTERVAL 5 SECOND) WHERE id IN (?, ?)"
            )->execute([self::DEVICE_FAN, self::DEVICE_INDOOR_LIGHT]);
        }
    }

    private function checkAutoOffDevices() {
        $stmt = $this->pdo->query("SELECT id FROM devices WHERE auto_off_time <= NOW()");
        $expiredDevices = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($expiredDevices as $id) {
            $this->newStatus[$id] = 0; // ปิดอุปกรณ์
            $this->pdo->prepare("UPDATE devices SET auto_off_time = NULL WHERE id = ?")->execute([$id]);

            sendTelegramNotification(
                "⏲️ <b>แจ้งเตือนปิดอัตโนมัติ</b>\n" .
                "อุปกรณ์ ID: $id ถูกปิดตามเวลาที่กำหนด"
            );
        }
    }

    private function updateDevices() {
        $stmt = $this->pdo->prepare("
            UPDATE devices 
            SET status = :status, 
                last_updated = NOW() 
            WHERE id = :id AND status != :status AND manual_override = 0
        ");
    
        foreach ($this->devices as $device) {
            // ตรวจสอบค่า manual_override เป็น 0 หรือ 1
            if (isset($this->newStatus[$device['id']]) && $device['manual_override'] == 0) {
                $stmt->execute([
                    ':status' => $this->newStatus[$device['id']],
                    ':id' => $device['id']
                ]);
            }
        }
    }

    public function run() {
        try {
            $this->pdo->beginTransaction();

            $this->getSensorData();
            $this->getDevices();

            // ตรวจสอบเงื่อนไขต่างๆ
            $this->checkTemperature();
            $this->checkLight();
            $this->checkMotion();
            $this->checkSound();
            $this->checkAutoOffDevices();

            // อัปเดตอุปกรณ์
            $this->updateDevices();

            $this->pdo->commit();

            return [
                'status' => 'success',
                'message' => 'Device states updated successfully',
                'updates' => $this->newStatus
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log("Automation error: " . $e->getMessage());

            sendTelegramNotification(
                "⚠️ <b>แจ้งเตือนข้อผิดพลาด</b>\n" .
                "ข้อผิดพลาด: {$e->getMessage()}"
            );

            return [
                'status' => 'error',
                'message' => 'Automation update failed: ' . $e->getMessage()
            ];
        }
    }
}

// รันระบบ
$automation = new HomeAutomation($pdo);
echo json_encode($automation->run());
?>
