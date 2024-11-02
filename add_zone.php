<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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
    if (!isset($data['zone_name']) || !isset($data['zone_description']) || !isset($data['event_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $zone_name = $data['zone_name'];
    $zone_description = $data['zone_description'];
    $event_id = $data['event_id'];

    $stmt = $conn->prepare("INSERT INTO zones (zone_name, zone_description, event_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $zone_name, $zone_description, $event_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Zone added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add zone: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
