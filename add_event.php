<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เปิดการแสดงผลข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// เชื่อมต่อฐานข้อมูล
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// รับข้อมูล JSON ที่ส่งมาจาก Postman
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// ตรวจสอบข้อมูลที่จำเป็น
$event_name = $data['event_name'] ?? null;
$event_start_date = $data['event_start_date'] ?? null;
$event_end_date = $data['event_end_date'] ?? null;

if (!$event_name || !$event_start_date || !$event_end_date) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// เตรียม SQL query เพื่อเพิ่มข้อมูลลงในตาราง events
$sql = "INSERT INTO events (event_name, event_start_date, event_end_date) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
}

// ผูกค่าที่รับมาใน query
$stmt->bind_param("sss", $event_name, $event_start_date, $event_end_date);

// ดำเนินการ query
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Event added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error adding event: " . $stmt->error]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
