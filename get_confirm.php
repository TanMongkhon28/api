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

// เตรียม SQL query สำหรับดึงข้อมูลการจองบูธที่มีสถานะ "pending" หรือ "pending_payment"
$sql = "SELECT CONCAT(u.name, ' ', u.lastname) AS fullname, u.phone, u.email,
               b.booth_name, z.zone_name, bk.status
        FROM users u 
        JOIN bookings bk ON u.id = bk.user_id 
        JOIN booth b ON bk.booth_id = b.id 
        JOIN zones z ON b.zone_id = z.id 
        WHERE bk.status IN ('Confirmed')";

$result = $conn->query($sql);

$bookings = [];

if ($result->num_rows > 0) {
    // วนลูปดึงข้อมูลและเก็บไว้ใน array
    while($row = $result->fetch_assoc()) {
        $bookings[] = [
            "fullname" => $row["fullname"],
            "phone" => $row["phone"],
	    "email" => $row["email"],
            "booth_name" => $row["booth_name"],
            "zone_name" => $row["zone_name"],
            "status" => $row["status"]
        ];
    }
    
    // ส่งข้อมูลการจองบูธกลับในรูปแบบ JSON
    echo json_encode(["status" => "success", "bookings" => $bookings]);
} else {
    echo json_encode(["status" => "error", "message" => "No bookings found for the specified statuses"]);
}

$conn->close();
?>
