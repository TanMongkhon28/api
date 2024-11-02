<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// เปิดการแสดงผลข้อผิดพลาด (ใช้สำหรับดีบัก)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// เชื่อมต่อฐานข้อมูล
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// ดึงข้อมูลจากตาราง zones ที่มี event_id = 1
$sql = "SELECT id, zone_name, zone_description, num_booths FROM zones";
$result = $conn->query($sql);

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows > 0) {
    $zones = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["status" => "success", "zones" => $zones]);
} else {
    echo json_encode(["status" => "error", "message" => "No zones found for event_id = 1"]);
}

// ปิดการเชื่อมต่อ
$conn->close();
?>