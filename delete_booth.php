<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ข้อมูลการเชื่อมต่อฐานข้อมูล
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// ตรวจสอบคำขอว่าเป็น POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // อ่านข้อมูล JSON ที่ส่งเข้ามาใน request body
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามีค่า booth_name ถูกส่งมา
    if (!isset($data['booth_name'])) {
        echo json_encode(["status" => "error", "message" => "Booth name is required"]);
        exit();
    }

    // กำหนดค่า booth_name จากข้อมูลที่รับมา
    $booth_name = $data['booth_name'];

    // เตรียมคำสั่ง SQL เพื่อลบข้อมูลตาม booth_name ที่กำหนด
    $stmt = $conn->prepare("DELETE FROM booth WHERE booth_name = ?");
    $stmt->bind_param("s", $booth_name);

    // รันคำสั่ง query และตรวจสอบผลลัพธ์
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Booth deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No booth found with that name"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete booth"]);
    }

    // ปิด statement และ connection
    $stmt->close();
}

$conn->close();
?>
