<?php
// เริ่ม session
session_start(); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


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

// รับข้อมูล JSON ที่ส่งมา
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// รับข้อมูลจาก JSON
$prefix = $data['prefix'];
$name = $data['name'];
$lastname = $data['lastname'];
$phone = $data['phone'];
$email = $data['email'];
$password = $data['password'];
$confirmPassword = $data['confirm_password'] ?? '';

// ตรวจสอบว่ารหัสผ่านกับยืนยันรหัสผ่านตรงกันหรือไม่
if ($password !== $confirmPassword) {
    echo json_encode(["status" => "error_password_mismatch"]);
    exit();
}

// ตรวจสอบว่ามีอีเมลนี้อยู่ในฐานข้อมูลหรือไม่
$checkEmailSQL = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($checkEmailSQL);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error_email_exists"]);
    exit();
} else {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (prefix, name, lastname, phone, email, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $prefix, $name, $lastname, $phone, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>
