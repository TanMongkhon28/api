<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามี zone_name หรือไม่
    if (!isset($data['zone_name'])) {
        echo json_encode(["status" => "error", "message" => "Missing required field 'zone_name'"]);
        exit();
    }

    $zone_name = $data['zone_name'];

    // สร้างคำสั่ง SQL ลบข้อมูล
    $stmt = $conn->prepare("DELETE FROM zones WHERE zone_name = ?");
    $stmt->bind_param("s", $zone_name);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Zone deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete zone: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
