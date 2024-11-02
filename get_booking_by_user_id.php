<?php
// เปิดการแสดงผลข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตั้งค่า CORS เพื่ออนุญาตการเข้าถึงจากภายนอก
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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

// รับ user_id ที่ต้องการจาก request (เช่นผ่าน JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$user_id = $data['user_id'] ?? null; // ตรวจสอบว่ามี user_id ถูกส่งมาหรือไม่

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "user_id is required"]);
    exit();
}

// เตรียม SQL query เพื่อดึงข้อมูลบูธที่ถูกจองโดย user_id และดึงข้อมูล details มาด้วย
$sql = "
    SELECT bookings.id AS booking_id, booth.*, bookings.status AS booking_status, 
           bookings.booking_date, bookings.payment_due_date, bookings.details, bookings.event_id
    FROM booth
    JOIN bookings ON booth.id = bookings.booth_id
    WHERE bookings.user_id = ? AND bookings.status != 'cancelled' AND bookings.status != 'expired';
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows > 0) {
    $booths = $result->fetch_all(MYSQLI_ASSOC); // ดึงข้อมูลทั้งหมดในรูปแบบ associative array
    echo json_encode(["status" => "success", "booths" => $booths]);
} else {
    echo json_encode(["status" => "error", "message" => "No booked booths found for this user"]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>