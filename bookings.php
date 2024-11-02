<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// �Դ���͹حҵ CORS
header('Access-Control-Allow-Origin: *'); // ������Ш� origin �� 'http://localhost:4200'
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ��Ǩ�ͺ�����ͧ��Ẻ OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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

// รับข้อมูลที่จำเป็นสำหรับการจอง
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$user_id = $data['user_id'] ?? null;
$booth_id = $data['booth_id'] ?? null;
$event_id = $data['event_id'] ?? null; // เพิ่มฟิลด์ event_id
$details = $data['details'] ?? null;

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบหรือไม่
if (!$user_id || !$booth_id || !$event_id) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// ตรวจสอบว่าบูธไอดีนี้ถูกจองไปแล้วหรือไม่
$sql = "SELECT id FROM bookings WHERE booth_id = ? AND status != 'cancelled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booth_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Booth already booked"]);
    exit();
}

// ตรวจสอบว่าผู้ใช้คนนี้ได้จองบูธไปแล้วกี่บูธ (ไม่เกิน 4 บูธ)
$sql = "SELECT COUNT(*) AS booth_count FROM bookings WHERE user_id = ? AND status != 'cancelled' AND status != 'expired'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$booth_count = $row['booth_count'];

if ($booth_count >= 4) {
    echo json_encode(["status" => "error", "message" => "You can only book up to 4 booths"]);
    exit();
}

// ตรวจสอบสถานะของบูธที่ต้องการจอง (ห้ามจองถ้าบูธนี้สถานะเป็น pending)
$sql = "SELECT status, zone_id, price FROM booth WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booth_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['status'] == 'pending') {
    echo json_encode(["status" => "error", "message" => "Booth is currently in pending status and cannot be booked"]);
    exit();
}

$zone_id = $row['zone_id']; // ดึง zone_id จาก booth
$price = $row['price'];  // ดึงราคา

// ตรวจสอบว่า zone_id ที่ดึงจากตาราง booth มีอยู่ในตาราง zones หรือไม่
$sql = "SELECT id FROM zones WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $zone_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "Invalid zone_id"]);
    exit();
}

// ดึงข้อมูลวันที่เริ่มและวันที่สิ้นสุดงานจากตาราง events โดยใช้ event_id
$sql = "SELECT event_start_date, event_end_date FROM events WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event_data = $result->fetch_assoc();

if (!$event_data) {
    echo json_encode(["status" => "error", "message" => "Invalid event_id"]);
    exit();
}

$event_start_date = $event_data['event_start_date'];
$event_end_date = $event_data['event_end_date'];

// คำนวณวันที่ต้องชำระเงินก่อนงานเริ่ม 5 วัน
$payment_due_date = date('Y-m-d', strtotime($event_start_date . ' - 5 days'));

// ตรวจสอบว่ามีเวลาชำระเงินก่อนงานเริ่ม 5 วันหรือไม่
$days_before_event = (strtotime($event_start_date) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);

if ($days_before_event <= 5) {
    echo json_encode(["status" => "error", "message" => "You must pay at least 5 days before the event starts"]);
    exit();
}

// เพิ่มการจองใหม่ในตาราง bookings พร้อมบันทึกราคา, zone_id, event_id, และ payment_due_date
$sql = "INSERT INTO bookings (user_id, booth_id, zone_id, event_id, price, booking_date, details, status, payment_due_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
}

$booking_date = date('Y-m-d');
$stmt->bind_param("iiisssss", $user_id, $booth_id, $zone_id, $event_id, $price, $booking_date, $details, $payment_due_date);

// ดำเนินการเพิ่มการจอง
if ($stmt->execute()) {
    // ดึง booking_id ที่เพิ่งถูกเพิ่ม
    $booking_id = $stmt->insert_id;

    // เพิ่มข้อมูลการชำระเงินลงในตาราง payment
    $sql = "INSERT INTO payment (booking_id, payment_date, amount, payment_status) VALUES (?, CURDATE(), ?, '')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $booking_id, $price);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Booking and payment added successfully", "payment_due_date" => $payment_due_date]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding payment: " . $stmt->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Error adding booking: " . $stmt->error]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
