<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เปิดการแสดงข้อผิดพลาดสำหรับการดีบัก
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ข้อมูลการเชื่อมต่อกับฐานข้อมูล
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// SQL query เพื่อดึงข้อมูลผู้ใช้ที่ยังไม่ชำระเงิน
$sql = "SELECT u.id AS user_id, u.name, u.lastname, u.phone, u.email, 
               b.booth_name, bk.status, z.zone_name, bk.id AS booking_id
        FROM users u 
        JOIN bookings bk ON u.id = bk.user_id 
        JOIN booth b ON bk.booth_id = b.id 
        JOIN zones z ON b.zone_id = z.id 
        WHERE bk.status = 'pending_payment'";

$result = $conn->query($sql);

$pending_bookings = [];

if ($result->num_rows > 0) {
    // ดึงข้อมูลจากผลลัพธ์และเพิ่มลงใน array
    while($row = $result->fetch_assoc()) {
        $pending_bookings[] = [
            "id" => $row["user_id"], // เปลี่ยนเป็น user_id เพื่อแยกจาก booking_id
            "name" => $row["name"],
            "lastname" => $row["lastname"],
            "phone" => $row["phone"],
            "email" => $row["email"],
            "booth_name" => $row["booth_name"],
            "status" => $row["status"],
            "zone_name" => $row["zone_name"],
            "booking_id" => $row["booking_id"] // เพิ่ม booking_id ที่นี่
        ];
    }
    
    // ส่งข้อมูลเป็น JSON
    echo json_encode(["status" => "success", "pending_bookings" => $pending_bookings]);

} else {
    echo json_encode(["status" => "error", "message" => "No pending bookings found"]);
}

$conn->close();
?>
