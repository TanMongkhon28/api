<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// ฟังก์ชันการเชื่อมต่อฐานข้อมูล
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

// Route สำหรับดึงข้อมูลการจองบูธที่มีสถานะ 'Confirmed'
$app->get('/bookings/confirmed', function (Request $request, Response $response) {
    $pdo = getConnection();

    $sql = "SELECT CONCAT(u.name, ' ', u.lastname) AS fullname, u.phone, u.email,
                   b.booth_name, z.zone_name, bk.status
            FROM users u 
            JOIN bookings bk ON u.id = bk.user_id 
            JOIN booth b ON bk.booth_id = b.id 
            JOIN zones z ON b.zone_id = z.id 
            WHERE bk.status = 'Confirmed'";

    $stmt = $pdo->query($sql);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($bookings) {
        $response->getBody()->write(json_encode(["status" => "success", "bookings" => $bookings]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No bookings found for the specified statuses"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run Slim App
$app->run();
