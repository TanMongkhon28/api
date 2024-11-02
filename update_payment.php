<?php
// เปิดการแสดงผลข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// จัดการคำขอ OPTIONS (Preflight Request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

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

// รับค่า booking_id จาก request
$booking_id = $_POST['booking_id'] ?? null;

// ตรวจสอบว่ามี booking_id และไฟล์หรือไม่
if (!$booking_id || !isset($_FILES['payment_slip'])) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// ตรวจสอบสถานะของ booking ก่อน
$sql_check_status = "SELECT status FROM bookings WHERE id = ?";
$stmt_check_status = $conn->prepare($sql_check_status);
$stmt_check_status->bind_param("i", $booking_id);
$stmt_check_status->execute();
$result = $stmt_check_status->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo json_encode(["status" => "error", "message" => "Booking not found"]);
    exit();
}

$status = $booking['status'];

// ไม่อนุญาตให้ชำระเงินถ้าสถานะเป็น 'expired' หรือ 'cancelled'
if ($status === 'expired' || $status === 'cancelled') {
    echo json_encode(["status" => "error", "message" => "Cannot make a payment for a booking that is expired or cancelled"]);
    exit();
}

// ตั้งค่าโฟลเดอร์ที่จะบันทึกไฟล์
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true); // สร้างไดเรกทอรีถ้ายังไม่มี
}

// ตั้งชื่อไฟล์และย้ายไฟล์ที่อัปโหลดไปยังโฟลเดอร์ที่กำหนด
$target_file = $target_dir . basename($_FILES["payment_slip"]["name"]);
if (move_uploaded_file($_FILES["payment_slip"]["tmp_name"], $target_file)) {
    $payment_slip_url = "https://yourdomain.com/" . $target_file; // สร้าง URL ของไฟล์
} else {
    echo json_encode(["status" => "error", "message" => "Error uploading file"]);
    exit();
}

// เตรียม SQL query เพื่ออัปเดต payment_slip และ payment_status ในตาราง payment โดยใช้ booking_id เป็นคีย์
$sql_update_payment = "UPDATE payment SET payment_slip = ?, payment_status = 'pending' WHERE booking_id = ?";
$stmt_update_payment = $conn->prepare($sql_update_payment);

if (!$stmt_update_payment) {
    die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
}

// Bind ค่าที่จะใช้ใน query (payment_slip_url และ booking_id)
$stmt_update_payment->bind_param("si", $payment_slip_url, $booking_id);

// Execute query
if ($stmt_update_payment->execute()) {
    echo json_encode(["status" => "success", "message" => "Payment slip updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating payment: " . $stmt_update_payment->error]);
}

// ปิดการเชื่อมต่อฐานข้อมูล
$stmt_check_status->close();
$stmt_update_payment->close();
$conn->close();
?>
