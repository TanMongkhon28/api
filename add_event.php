<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

// สร้าง App
$app = AppFactory::create();

// Middleware เพื่อให้ใช้งาน JSON ได้
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Error handling middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
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

// Route สำหรับเพิ่มข้อมูลอีเวนต์ใหม่
$app->post('/add-event', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $event_name = $data['event_name'] ?? null;
    $event_start_date = $data['event_start_date'] ?? null;
    $event_end_date = $data['event_end_date'] ?? null;

    if (!$event_name || !$event_start_date || !$event_end_date) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Required fields are missing"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $sql = "INSERT INTO events (event_name, event_start_date, event_end_date) VALUES (:event_name, :event_start_date, :event_end_date)";
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare($sql);

        // ผูกค่าพารามิเตอร์
        $stmt->bindParam(':event_name', $event_name);
        $stmt->bindParam(':event_start_date', $event_start_date);
        $stmt->bindParam(':event_end_date', $event_end_date);

        // ดำเนินการ insert ข้อมูล
        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(["status" => "success", "message" => "Event added successfully"]));
        } else {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Error adding event"]));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();

