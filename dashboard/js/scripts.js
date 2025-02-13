// กราฟแสดงข้อมูล
const ctx = document.getElementById('sensorChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_column($sensors, 'created_at')) ?>,
    datasets: [
      {
        label: 'อุณหภูมิ (°C)',
        data: <?= json_encode(array_column($sensors, 'temperature')) ?>,
        borderColor: '#4CAF50',
        tension: 0.1
      },
      {
        label: 'ความชื้น (%)',
        data: <?= json_encode(array_column($sensors, 'humidity')) ?>,
        borderColor: '#2196F3',
        tension: 0.1
      },
      {
        label: 'ความสว่าง (Lux)',
        data: <?= json_encode(array_column($sensors, 'light_level')) ?>,
        borderColor: '#FFC107',
        tension: 0.1
      },
      {
        label: 'การเคลื่อนไหว',
        data: <?= json_encode(array_column($sensors, 'motion_detected')) ?>,
        borderColor: '#F44336',
        tension: 0.1
      },
      {
        label: 'ระดับเสียง (dB)',
        data: <?= json_encode(array_column($sensors, 'sound_level')) ?>,
        borderColor: '#9C27B0',
        tension: 0.1
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: { beginAtZero: true }
    }
  }
});

// ควบคุมอุปกรณ์
document.querySelectorAll('.toggle-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const deviceId = btn.dataset.id;
    const newStatus = btn.dataset.status === '1' ? 0 : 1;

    try {
      const response = await fetch(`/ECP4N/07_iot/smart-home/api/devices.php?api_key=y1xneVWfBv`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          device_id: deviceId,
          status: newStatus,
          action: 'update'
        })
      });

      if (response.ok) {
        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ!',
          text: 'ปรับสถานะอุปกรณ์เรียบร้อย',
          timer: 1500,
          showConfirmButton: false
        }).then(() => location.reload());
      }
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด!',
        text: 'ไม่สามารถปรับสถานะอุปกรณ์ได้'
      });
    }
  });
});

// อัพเดทข้อมูลอัตโนมัติ
setInterval(() => {
  fetch(window.location.href)
    .then(res => res.text())
    .then(html => {
      const parser = new DOMParser();
      const newDoc = parser.parseFromString(html, 'text/html');
      
      // อัพเดทข้อมูลเซนเซอร์
      document.querySelector('#sensors .dashboard-grid').innerHTML = 
        newDoc.querySelector('#sensors .dashboard-grid').innerHTML;
      
      // อัพเดทประวัติการทำงาน
      document.querySelector('#activity .activity-log').innerHTML = 
        newDoc.querySelector('#activity .activity-log').innerHTML;
    });
}, 10000);