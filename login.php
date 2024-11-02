<?php
header('Access-Control-Allow-Origin: *'); // อนุญาตการเข้าถึงจากทุกที่ (เปลี่ยน * เป็นโดเมนที่ต้องการอนุญาต)
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// เปิดการแสดงผลข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // เริ่มต้น session

// เชื่อมต่อฐานข้อมูล
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";
;

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// อ่านข้อมูล JSON ที่ส่งมา
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// รับข้อมูลจาก JSON ที่ส่งมา
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

// ตรวจสอบว่ามีการส่งข้อมูล email และ password หรือไม่
if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Email and password are required"]);
    exit();
}

// เตรียม SQL เพื่อตรวจสอบ email
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $user = $result->fetch_assoc();
    
    // ตรวจสอบรหัสผ่าน
    if (password_verify($password, $user['password'])) {
        // ถ้ารหัสผ่านถูกต้อง เก็บข้อมูลผู้ใช้ใน session
        $_SESSION['user'] = $user;

        // ส่ง response กลับเป็น JSON เมื่อเข้าสู่ระบบสำเร็จ
        echo json_encode(["status" => "success", "message" => "Login successful", "user" => $user]);
    } else {
        // ถ้ารหัสผ่านไม่ถูกต้อง
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
    }
} else {
    // ถ้าไม่พบ email นี้ในฐานข้อมูล
    echo json_encode(["status" => "error", "message" => "Email not found"]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>