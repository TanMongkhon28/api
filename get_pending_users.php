<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// กำหนด CORS Middleware
$app->add(function (Request $request, Response $response, callable $next) {
    $response = $response->withHeader('Access-Control-Allow-Origin', '*')
                         ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                         ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    if ($request->getMethod() === 'OPTIONS') {
        return $response;
    }
    return $next($request, $response);
});

// ฟังก์ชันเชื่อมต่อฐานข้อมูลด้วย PDO
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

// Route สำหรับดึงข้อมูลการจองที่สถานะ `pending`
$app->get('/pending-bookings', function (Request $request, Response $response) {
    $pdo = getConnection();

    $sql = "SELECT u.id, u.name, u.lastname, u.phone, u.email, 
                   b.booth_name, bk.status, z.zone_name
            FROM users u 
            JOIN bookings bk ON u.id = bk.user_id 
            JOIN booth b ON bk.booth_id = b.id 
            JOIN zones z ON b.zone_id = z.id 
            WHERE bk.status = 'pending'";

    $stmt = $pdo->query($sql);
    $pending_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($pending_bookings) {
        $response->getBody()->write(json_encode(["status" => "success", "pending_bookings" => $pending_bookings]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No pending bookings found"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run Slim App
$app->run();
