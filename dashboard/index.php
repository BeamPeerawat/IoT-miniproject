<?php
require __DIR__.'/../config/database.php';
require 'models/sensor_model.php';
require 'models/device_model.php';
require 'models/activity_model.php';

$latestSensor = getLatestSensor($pdo);
$sensors = getSensorsLast10Minutes($pdo);
$devices = getAllDevices($pdo);
$activities = getRecentActivities($pdo);

include 'views/header.php';
include 'views/sensor_section.php';
include 'views/chart_section.php';
include 'views/device_section.php';
include 'views/activity_section.php';
include 'views/footer.php';
?>