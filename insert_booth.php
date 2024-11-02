<?php
// เปิดการแสดงผลข้อผิดพลาด

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // เริ่ม session

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

// อ่านข้อมูล JSON ที่ส่งมา
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// ตรวจสอบว่าข้อมูล JSON ถูกส่งมาครบถ้วนหรือไม่
if (!$data) {
    die(json_encode(["status" => "error", "message" => "Invalid JSON input"]));
}

// รับข้อมูลจาก JSON
$booth_name = $data['booth_name'] ?? null;
$booth_size = $data['booth_size'] ?? null;
$status = $data['status'] ?? null;
$price = $data['price'] ?? null;
$image_url = $data['image_url'] ?? null;
$zone_id = $data['zone_id'] ?? null;

// ตรวจสอบว่ามีข้อมูลครบหรือไม่
if (!$booth_name || !$booth_size || !$status || !$price || !$zone_id) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// เตรียม SQL สำหรับการเพิ่มข้อมูลบูธ
$sql = "INSERT INTO booth (booth_name, booth_size, status, price, image_url, zone_id) VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["status" => "error", "message" => "SQL preparation failed: " . $conn->error]));
}

// ใช้ตัวแปรที่ได้จาก JSON
$stmt->bind_param("sssssi", $booth_name, $booth_size, $status, $price, $image_url, $zone_id);

// ดำเนินการ query
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Booth information added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error adding booth information: " . $stmt->error]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
