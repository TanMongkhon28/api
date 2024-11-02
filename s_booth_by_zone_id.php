<?php
// เพิ่มการตั้งค่า CORS ที่ด้านบนสุดของไฟล์
header('Access-Control-Allow-Origin: *'); // อนุญาตให้ทุกโดเมนเข้าถึงได้
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // อนุญาตการใช้ POST, GET และ OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'); // อนุญาต Content-Type, Authorization, และ X-Requested-With
header('Content-Type: application/json');

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

// รับค่า zone_id จาก JSON request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// ตรวจสอบการแปลง JSON และการรับ zone_id
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit();
}

$zone_id = $data['zone_id'] ?? null;

// ตรวจสอบว่า zone_id ถูกส่งมาหรือไม่
if (!$zone_id || !is_numeric($zone_id)) {
    echo json_encode(["status" => "error", "message" => "Invalid or missing zone_id"]);
    exit();
}

// เตรียม SQL query เพื่อดึงข้อมูลบูธตาม zone_id
$sql = "SELECT * FROM booth WHERE zone_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare SQL: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $zone_id);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่ามีข้อมูลบูธหรือไม่
if ($result->num_rows > 0) {
    $booths = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["status" => "success", "booths" => $booths]);
} else {
    echo json_encode(["status" => "error", "message" => "No booths found for this zone"]);
}

$stmt->close();
$conn->close();
?>