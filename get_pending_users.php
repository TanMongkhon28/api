<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

// เตรียม SQL query สำหรับดึงข้อมูลผู้ใช้จากการจองที่มีสถานะ 'pending' และข้อมูลบูธที่จอง
$sql = "SELECT u.id, u.name, u.lastname, u.phone, u.email, 
               b.booth_name, bk.status, z.zone_name
        FROM users u 
        JOIN bookings bk ON u.id = bk.user_id 
        JOIN booth b ON bk.booth_id = b.id 
        JOIN zones z ON b.zone_id = z.id 
        WHERE bk.status = 'pending'";

$result = $conn->query($sql);

$pending_bookings = [];

if ($result->num_rows > 0) {
    // วนลูปดึงข้อมูลและเก็บไว้ใน array
    while($row = $result->fetch_assoc()) {
        $pending_bookings[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "lastname" => $row["lastname"],
            "phone" => $row["phone"],
            "email" => $row["email"],
            "booth_name" => $row["booth_name"],
            "status" => $row["status"], // Updated column name here
            "zone_name" => $row["zone_name"]
        ];
    }
    
    // ส่งข้อมูลการจองที่ยังไม่ชำระเงินกลับในรูปแบบ JSON
    echo json_encode(["status" => "success", "pending_bookings" => $pending_bookings]);

} else {
    echo json_encode(["status" => "error", "message" => "No pending bookings found"]);
}

$conn->close();
?>
