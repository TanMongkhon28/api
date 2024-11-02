<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT"); // อนุญาต PUT ด้วย
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// ตรวจสอบว่าเป็น PUT request
if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // รับข้อมูล JSON จาก body ของ request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        die(json_encode(["status" => "error", "message" => "Invalid JSON input"]));
    }

    $id = $data['id'] ?? null;
    $booth_name = $data['booth_name'] ?? null;
    $booth_size = $data['booth_size'] ?? null;
    $status = $data['status'] ?? null;
    $price = $data['price'] ?? null;
    $image_url = $data['image_url'] ?? null;
    $zone_id = $data['zone_id'] ?? null;

    if (!$id || !$booth_name || !$booth_size || !$status || !$price || !$zone_id) {
        echo json_encode(["status" => "error", "message" => "Required fields are missing or invalid"]);
        exit();
    }

    // เตรียม SQL statement
    $sql = "UPDATE booth SET booth_name=?, booth_size=?, status=?, price=?, image_url=?, zone_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die(json_encode(["status" => "error", "message" => "SQL preparation failed: " . $conn->error]));
    }

    $stmt->bind_param("sssisis", $booth_name, $booth_size, $status, $price, $image_url, $zone_id, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Booth information updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating booth information: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
