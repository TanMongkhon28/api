<?php
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

// รับข้อมูลที่จำเป็นจาก request (ผ่าน JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$booking_id = $data['booking_id'] ?? null;

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบหรือไม่
if (!$booking_id) {
    echo json_encode(["status" => "error", "message" => "Booking ID is required"]);
    exit();
}

// เตรียม SQL query เพื่อเปลี่ยนสถานะการชำระเงินเป็น "paid"
$sql = "UPDATE payment SET payment_status = 'failed' WHERE booking_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
}

// Bind ค่าที่จะใช้ใน query (booking_id)
$stmt->bind_param("i", $booking_id);

// Execute query
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Payment status updated to failed"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating payment status: " . $stmt->error]);
}

// ปิดการเชื่อมต่อฐานข้อมูล
$stmt->close();
$conn->close();
?>
