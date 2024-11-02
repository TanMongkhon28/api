<?php
session_start(); // เริ่ม session

header("Access-Control-Allow-Origin: *"); // อนุญาตทุกโดเมน (สำหรับการทดสอบ)
header("Access-Control-Allow-Methods: POST, OPTIONS"); // อนุญาตเฉพาะ POST และ OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // อนุญาตเฉพาะบาง header

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

// รับข้อมูลจาก JSON ที่ส่งมา
$user_id = $data['user_id'] ?? null;
$prefix = $data['prefix'] ?? null;
$name = $data['name'] ?? null;
$lastname = $data['lastname'] ?? null;
$phone = $data['phone'] ?? null;
$email = $data['email'] ?? null;
$new_password = $data['new_password'] ?? null;
$confirm_password = $data['confirm_password'] ?? null;

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบหรือไม่
if (!$user_id || !$prefix || !$name || !$lastname || !$phone || !$email) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// ตรวจสอบว่าอีเมลนี้มีอยู่ในฐานข้อมูลแล้วหรือไม่ (แต่ต้องไม่ใช่ของผู้ใช้คนนี้เอง)
$checkEmailSQL = "SELECT * FROM users WHERE email = ? AND id != ?";
$stmt = $conn->prepare($checkEmailSQL);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This email is already in use"]);
    exit();
}

// เตรียม SQL สำหรับการแก้ไขข้อมูลผู้ใช้
if ($new_password && $confirm_password) {
    if ($new_password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit();
    }

    // แฮชรหัสผ่านใหม่
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    $sql = "UPDATE users SET prefix = ?, name = ?, lastname = ?, phone = ?, email = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $prefix, $name, $lastname, $phone, $email, $hashedPassword, $user_id);
} else {
    $sql = "UPDATE users SET prefix = ?, name = ?, lastname = ?, phone = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $prefix, $name, $lastname, $phone, $email, $user_id);
}

// ดำเนินการ query
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "User information updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating user information: " . $stmt->error]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
