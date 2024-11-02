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

// เตรียม SQL query สำหรับดึงข้อมูลการจองบูธทั้งหมด
$sql = "SELECT CONCAT(u.name, ' ', u.lastname) AS fullname, z.zone_name, b.price, b.booth_name, bk.status
        FROM bookings bk
        JOIN users u ON u.id = bk.user_id
        JOIN booth b ON b.id = bk.booth_id
        JOIN zones z ON z.id = b.zone_id
        WHERE bk.status = 'pending_payment' OR bk.status = 'pending'  OR bk.status = 'confirmed'";

$result = $conn->query($sql);

$booth_bookings = [];

if ($result->num_rows > 0) {
    // วนลูปดึงข้อมูลและเก็บไว้ใน array
    while($row = $result->fetch_assoc()) {
        $booth_bookings[] = [
            "fullname" => $row["fullname"],
            "zone_name" => $row["zone_name"],
            "price" => $row["price"],
            "booth_name" => $row["booth_name"],
            "status" => $row["status"]
        ];
    }
    
    // ส่งข้อมูลรายการจองบูธกลับในรูปแบบ JSON
    echo json_encode(["status" => "success", "booth_bookings" => $booth_bookings]);
} else {
    echo json_encode(["status" => "error", "message" => "No booth bookings found"]);
}

$conn->close();
?>
