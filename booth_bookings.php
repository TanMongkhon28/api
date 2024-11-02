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

// กำหนดการเชื่อมต่อฐานข้อมูลด้วย PDO
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

// Route สำหรับดึงข้อมูลการจองบูธทั้งหมด
$app->get('/booth-bookings', function (Request $request, Response $response) {
    $pdo = getConnection();

    // เตรียม SQL query สำหรับดึงข้อมูลการจองบูธ
    $sql = "SELECT CONCAT(u.name, ' ', u.lastname) AS fullname, 
                   z.zone_name, 
                   b.price, 
                   b.booth_name, 
                   bk.status
            FROM bookings bk
            JOIN users u ON u.id = bk.user_id
            JOIN booth b ON b.id = bk.booth_id
            JOIN zones z ON z.id = b.zone_id
            WHERE bk.status IN ('pending_payment', 'pending', 'confirmed')";

    try {
        $stmt = $pdo->query($sql);
        $booth_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($booth_bookings)) {
            $response->getBody()->write(json_encode([
                "status" => "success",
                "booth_bookings" => $booth_bookings
            ]));
        } else {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "No booth bookings found"
            ]));
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Database query failed: " . $e->getMessage()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Run app
$app->run();
