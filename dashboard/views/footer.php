</div>
<footer>
  <p>© 2023 Smart Home Dashboard</p>
  <p>Developed by [Your Name]</p>
</footer>

<!-- นำโค้ด JavaScript มาไว้ตรงนี้ -->
<script>
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

// ฟังก์ชันสลับโหมด
const toggleTheme = () => {
  const currentTheme = document.documentElement.getAttribute('data-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  
  // ตั้งค่าโหมดใหม่
  document.documentElement.setAttribute('data-theme', newTheme);
  
  // บันทึกโหมดใน localStorage
  localStorage.setItem('theme', newTheme);
  
  // อัปเดตไอคอน
  const moonIcon = document.querySelector('.fa-moon');
  const sunIcon = document.querySelector('.fa-sun');
  if (newTheme === 'dark') {
    moonIcon.style.display = 'none';
    sunIcon.style.display = 'inline-block';
  } else {
    sunIcon.style.display = 'none';
    moonIcon.style.display = 'inline-block';
  }
};

// ตั้งค่าโหมดเริ่มต้นจาก localStorage
const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);

// ตั้งค่าไอคอนเริ่มต้น
if (savedTheme === 'dark') {
  document.querySelector('.fa-moon').style.display = 'none';
  document.querySelector('.fa-sun').style.display = 'inline-block';
} else {
  document.querySelector('.fa-sun').style.display = 'none';
  document.querySelector('.fa-moon').style.display = 'inline-block';
}

// เพิ่ม Event Listener ให้ปุ่มสลับโหมด
document.getElementById('theme-toggle').addEventListener('click', toggleTheme);

// ควบคุมอุปกรณ์
// ควบคุมโหมด
document.querySelectorAll('.mode-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const deviceId = btn.dataset.id;
    const newMode = btn.dataset.mode === 'manual' ? 1 : 0; // ใช้ 0 และ 1 แทน boolean

    try {
      const response = await fetch(`https://botxai.com/ECP4N/07_iot/smart-home/api/devices.php?api_key=y1xneVWfBv`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          device_id: deviceId,
          action: 'update',
          status: btn.closest('.device-card').querySelector('.toggle-btn').dataset.status,
          manual_override: newMode
        })
      });

      if (response.ok) {
        location.reload();
      } else {
        const errorData = await response.json();
        throw new Error(errorData.message || 'HTTP error: ' + response.status);
      }
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด!',
        text: error.message
      });
    }
  });
});

// ควบคุมเปิดปิด
document.querySelectorAll('.toggle-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const deviceId = btn.dataset.id;
    const newStatus = btn.dataset.status === '1' ? 0 : 1; // สลับสถานะ (1 → 0, 0 → 1)

    try {
      const response = await fetch(`https://botxai.com/ECP4N/07_iot/smart-home/api/devices.php?api_key=y1xneVWfBv`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          device_id: deviceId,
          action: 'update',
          status: newStatus,
          manual_override: 1 // ตั้งค่าเป็นโหมดมือเมื่อคลิก
        })
      });

      if (response.ok) {
        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ!',
          text: 'ปรับสถานะอุปกรณ์เรียบร้อย',
        }).then(() => location.reload()); // รีเฟรชหน้าเว็บ
      } else {
        const errorData = await response.json();
        throw new Error(errorData.message || 'HTTP error: ' + response.status);
      }
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด!',
        text: error.message
      });
    }
  });
});

// อัพเดทข้อมูลอัตโนมัติทุก 10 วินาที
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
</script>

</body>
</html>