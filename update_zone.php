<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id']) || !isset($data['zone_name']) || !isset($data['zone_description']) || !isset($data['num_booths'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $id = $data['id'];
    $zone_name = $data['zone_name'];
    $zone_description = $data['zone_description'];
    $num_booths = $data['num_booths'];

    $stmt = $conn->prepare("UPDATE zones SET zone_name = ?, zone_description = ?, num_booths = ? WHERE id = ?");
    $stmt->bind_param("ssii", $zone_name, $zone_description, $num_booths, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Zone updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update zone: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
