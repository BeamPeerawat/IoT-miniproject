<section id="sensors" class="card">
  <div class="card-header">
    <h2 class="card-title"><i class="fas fa-sensor"></i> สถานะเซนเซอร์ปัจจุบัน</h2>
    <span class="update-time">อัพเดทล่าสุด: <?= date('H:i:s') ?></span>
  </div>
  <div class="dashboard-grid">
    <div class="card">
      <h3>อุณหภูมิ</h3>
      <div class="sensor-value"><?= $latestSensor['temperature'] ?? '--' ?></div>
      <span class="sensor-unit">°C</span>
    </div>
    <div class="card">
      <h3>ความชื้น</h3>
      <div class="sensor-value"><?= $latestSensor['humidity'] ?? '--' ?></div>
      <span class="sensor-unit">%</span>
    </div>
    <div class="card">
      <h3>ความสว่าง</h3>
      <div class="sensor-value"><?= $latestSensor['light_level'] ?? '--' ?></div>
      <span class="sensor-unit">Lux</span>
    </div>
    <div class="card">
      <h3>การเคลื่อนไหว</h3>
      <div class="sensor-value"><?= ($latestSensor['motion_detected'] ?? false) ? 'ตรวจพบ' : 'ปกติ' ?></div>
    </div>
    <div class="card">
      <h3>ระดับเสียง</h3>
      <div class="sensor-value">
        <?= $latestSensor['sound_level'] ?? '--' ?>
        <span class="sensor-unit">dB</span>
      </div>
      <div class="sound-indicator">
        <?php if(($latestSensor['sound_level'] ?? 0) > 50): ?>
          <i class="fas fa-volume-up text-danger"></i> ตรวจพบเสียงดัง
        <?php else: ?>
          <i class="fas fa-volume-mute text-success"></i> ปกติ
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>