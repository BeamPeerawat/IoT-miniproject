<section id="activity" class="card">
  <div class="card-header">
    <h2 class="card-title"><i class="fas fa-history"></i> ประวัติการทำงานล่าสุด</h2>
    <button class="refresh-btn" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
  </div>
  <div class="activity-log">
    <?php foreach ($activities as $log): ?>
      <div class="log-item <?= $log['type'] ?>">
        <div class="log-info">
          <?php switch($log['type']):
            case 'sensor': ?>
              <i class="fas fa-thermometer-half text-primary"></i>
            <?php break; ?>
            <?php case 'device': ?>
              <i class="fas fa-power-off text-success"></i>
            <?php break; ?>
            <?php case 'sound': ?>
              <i class="fas fa-volume-up text-danger"></i>
            <?php break; ?>
          <?php endswitch; ?>
          
          <div class="log-details">
            <div class="log-description"><?= htmlspecialchars($log['description']) ?></div>
            <div class="log-type"><?= strtoupper($log['type']) ?></div>
          </div>
        </div>
        <div class="log-time">
          <i class="fas fa-clock"></i>
          <?= date('H:i:s', strtotime($log['created_at'])) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>