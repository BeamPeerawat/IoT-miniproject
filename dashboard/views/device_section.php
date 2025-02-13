<section id="devices" class="card">
  <div class="card-header">
    <h2 class="card-title"><i class="fas fa-plug"></i> ควบคุมอุปกรณ์</h2>
  </div>
  <div class="dashboard-grid">
    <?php foreach ($devices as $device): ?>
      <div class="device-card">
        <div class="device-info">
          <h3><?= htmlspecialchars($device['name']) ?></h3>
          <p class="device-location"><?= htmlspecialchars($device['location']) ?></p>
          <div class="device-status">
            <i class="fas fa-circle <?= $device['status'] ? 'text-success' : 'text-muted' ?>"></i>
            <span><?= $device['status'] ? 'เปิดใช้งาน' : 'ปิดอยู่' ?></span>
          </div>
        </div>

        <div class="device-controls">
          <!-- ปุ่มเปิด/ปิดอุปกรณ์ -->
          <button 
            class="toggle-btn <?= $device['manual_override'] ? 'manual-mode' : '' ?>"
            data-id="<?= $device['id'] ?>"
            data-status="<?= $device['status'] ?>"
            <?= $device['manual_override'] ? 'disabled' : '' ?>
          >
            <?= $device['status'] ? 'ปิด' : 'เปิด' ?>
          </button>

          <!-- ปุ่มสลับโหมด (อัตโนมัติ/มือ) -->
          <button 
            class="mode-btn"
            data-id="<?= $device['id'] ?>"
            data-mode="<?= $device['manual_override'] ? 'auto' : 'manual' ?>"
            data-manual-override="<?= $device['manual_override'] ?>"
          >
            <?= $device['manual_override'] ? 'โหมดอัตโนมัติ' : 'โหมดมือ' ?>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
