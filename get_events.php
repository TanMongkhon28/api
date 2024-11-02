<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");

$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// สร้างคำสั่ง SQL สำหรับดึงข้อมูล Event
$sql = "SELECT event_id, event_name, event_start_date, event_end_date FROM events";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    echo json_encode(["status" => "success", "events" => $events]);
} else {
    echo json_encode(["status" => "error", "message" => "No events found"]);
}

$conn->close();
?>
