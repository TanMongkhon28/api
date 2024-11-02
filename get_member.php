<?php
// เปิดการแสดงผลข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
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

// เตรียม SQL query เพื่อดึงข้อมูลสมาชิก
$sql = "SELECT id, name, lastname, phone, email, prefix FROM users";
$result = $conn->query($sql);

$members = [];

if ($result->num_rows > 0) {
    // วนลูปดึงข้อมูลและเก็บไว้ใน array
    while($row = $result->fetch_assoc()) {
        $members[] = [
            "id" => $row["id"],
            "prefix" => $row["prefix"],
            "name" => $row["name"],
            "lastname" => $row["lastname"],
            "phone" => $row["phone"],
            "email" => $row["email"]
            
        ];
    }
    
    // ส่งข้อมูลสมาชิกกลับในรูปแบบ JSON
    echo json_encode(["status" => "success", "members" => $members]);

} else {
    echo json_encode(["status" => "error", "message" => "No members found"]);
}

$conn->close();
?>
