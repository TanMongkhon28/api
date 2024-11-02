<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

// สร้างแอปพลิเคชัน Slim
$app = AppFactory::create();

// Middleware เพื่อรองรับการรับค่า JSON
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Error Handling Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// ตั้งค่าการเชื่อมต่อฐานข้อมูลด้วย PDO
$servername = "151.106.124.154";
$username = "u583789277_wag19";
$password = "2567Inspire";
$dbname = "u583789277_wag19";

function getConnection() {
    global $servername, $username, $password, $dbname;
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]));
    }
}

// Route สำหรับเพิ่มข้อมูลโซน
$app->post('/add-zone', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    // ตรวจสอบว่าข้อมูลที่ต้องการมีครบหรือไม่
    if (!isset($data['zone_name']) || !isset($data['zone_description']) || !isset($data['event_id'])) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Missing required fields"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $zone_name = $data['zone_name'];
    $zone_description = $data['zone_description'];
    $event_id = $data['event_id'];

    // SQL สำหรับเพิ่มข้อมูลโซน
    $sql = "INSERT INTO zones (zone_name, zone_description, event_id) VALUES (:zone_name, :zone_description, :event_id)";
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare($sql);

        // ผูกค่าพารามิเตอร์
        $stmt->bindParam(':zone_name', $zone_name);
        $stmt->bindParam(':zone_description', $zone_description);
        $stmt->bindParam(':event_id', $event_id);

        // ดำเนินการ insert ข้อมูล
        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(["status" => "success", "message" => "Zone added successfully"]));
        } else {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Failed to add zone"]));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
