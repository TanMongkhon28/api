<?php
// ตั้งค่า CORS Headers
header("Access-Control-Allow-Origin: *"); // อนุญาตทุกโดเมน
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // ระบุวิธีการที่อนุญาต
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // ระบุ headers ที่อนุญาต

// ตรวจสอบการเรียก OPTIONS request สำหรับการ preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
$status = $data['status'] ?? null;

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบหรือไม่
if (!$booking_id || !$status) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// เตรียม SQL query เพื่ออัปเดตสถานะการจองในตาราง bookings โดยใช้ booking_id
$sql = "UPDATE bookings SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
}

// Bind ค่าที่จะใช้ใน query (status และ booking_id)
$stmt->bind_param("si", $status, $booking_id);

// Execute query เพื่อตรวจสอบสถานะการจอง
if ($stmt->execute()) {
    // ตรวจสอบว่าถ้าสถานะเป็น 'cancelled' ให้อัปเดตสถานะในตาราง payment เป็น 'failed'
    if ($status === 'cancelled') {
        // เตรียม SQL query สำหรับอัปเดตสถานะ payment เป็น 'failed'
        $updatePaymentSQL = "UPDATE payment SET payment_status = 'failed' WHERE booking_id = ?";
        $stmtUpdatePayment = $conn->prepare($updatePaymentSQL);
        if (!$stmtUpdatePayment) {
            die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
        }
        $stmtUpdatePayment->bind_param("i", $booking_id);
        if ($stmtUpdatePayment->execute()) {
            echo json_encode(["status" => "success", "message" => "Booking and payment status updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error updating payment: " . $stmtUpdatePayment->error]);
        }
        $stmtUpdatePayment->close();
    } else {
        echo json_encode(["status" => "success", "message" => "Booking status updated successfully"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Error updating booking: " . $stmt->error]);
}

// ปิดการเชื่อมต่อฐานข้อมูล
$stmt->close();
$conn->close();
?>