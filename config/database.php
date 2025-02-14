<?php
$host = 'bahixnsz3iv7yk6dhi8s-mysql.services.clever-cloud.com';
$dbname = 'bahixnsz3iv7yk6dhi8s';
$user = 'uxwgnyyqzuoqyffe';
$pass = 'FlPcEI8TCG55oFNoKvdp';
$api_key = 'y1xneVWfBv';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>
