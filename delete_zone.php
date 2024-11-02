<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// Middleware เพื่อรองรับ JSON body
$app->addBodyParsingMiddleware();

// Error Handling Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// ฟังก์ชันสำหรับสร้างการเชื่อมต่อ PDO
function getConnection() {
    $servername = "151.106.124.154";
    $username = "u583789277_wag19";
    $password = "2567Inspire";
    $dbname = "u583789277_wag19";

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]));
    }
}

// Route สำหรับการลบ zone โดยชื่อ
$app->post('/delete-zone', function (Request $request, Response $response) {
    $pdo = getConnection();

    // รับข้อมูล JSON ที่ส่งมาใน request body
    $data = $request->getParsedBody();

    // ตรวจสอบว่ามี zone_name หรือไม่
    if (!isset($data['zone_name'])) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Missing required field 'zone_name'"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $zone_name = $data['zone_name'];

    // เตรียม SQL statement เพื่อลบข้อมูล
    $stmt = $pdo->prepare("DELETE FROM zones WHERE zone_name = :zone_name");
    $stmt->bindParam(':zone_name', $zone_name);

    // ดำเนินการลบข้อมูลและตรวจสอบผลลัพธ์
    try {
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(["status" => "success", "message" => "Zone deleted successfully"]));
        } else {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "No zone found with that name"]));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Failed to delete zone: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
