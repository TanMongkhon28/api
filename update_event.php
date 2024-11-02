<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Update event if PUT request is received
if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $data = json_decode(file_get_contents('php://input'), true);

    // Check required fields
    if (!isset($data['event_id']) || !isset($data['event_name']) || !isset($data['event_start_date']) || !isset($data['event_end_date'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $event_id = $data['event_id'];
    $event_name = $data['event_name'];
    $event_start_date = $data['event_start_date'];
    $event_end_date = $data['event_end_date'];

    // Update event data in the database
    $stmt = $conn->prepare("UPDATE events SET event_name = ?, event_start_date = ?, event_end_date = ? WHERE event_id = ?");
    $stmt->bind_param("sssi", $event_name, $event_start_date, $event_end_date, $event_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Event updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No event found with the given ID"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update event"]);
    }

    $stmt->close();
}

$conn->close();
?>
