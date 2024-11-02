<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// �Դ����ʴ���ͼԴ��Ҵ����Ѻ��ôպѡ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// �����š���������͡Ѻ�ҹ������
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// ��Ǩ�ͺ�����������
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// SQL query ���ʹ֧�����ż�������ѧ�������Թ
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
    // �֧�����Ũҡ���Ѿ���������ŧ� array
    while($row = $result->fetch_assoc()) {
        $pending_bookings[] = [
            "id" => $row["user_id"], // ����¹�� user_id �����¡�ҡ booking_id
            "name" => $row["name"],
            "lastname" => $row["lastname"],
            "phone" => $row["phone"],
            "email" => $row["email"],
            "booth_name" => $row["booth_name"],
            "status" => $row["status"],
            "zone_name" => $row["zone_name"],
            "booking_id" => $row["booking_id"] // ���� booking_id �����
        ];
    }
    
    // �觢������� JSON
    echo json_encode(["status" => "success", "pending_bookings" => $pending_bookings]);

} else {
    echo json_encode(["status" => "error", "message" => "No pending bookings found"]);
}

$conn->close();
?>
