<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// ฟังก์ชันสร้างการเชื่อมต่อ PDO
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

// Route สำหรับดึงข้อมูลการจองบูธของผู้ใช้ตาม user_id
$app->post('/get-user-bookings', function (Request $request, Response $response) {
    $pdo = getConnection();
    $data = $request->getParsedBody();
    $user_id = $data['user_id'] ?? null;

    if (!$user_id) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "user_id is required"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // SQL query สำหรับดึงข้อมูลการจองบูธของผู้ใช้
    $sql = "
        SELECT bookings.id AS booking_id, booth.*, bookings.status AS booking_status, 
               bookings.booking_date, bookings.payment_due_date, bookings.details, bookings.event_id
        FROM booth
        JOIN bookings ON booth.id = bookings.booth_id
        WHERE bookings.user_id = :user_id 
          AND bookings.status != 'cancelled' 
          AND bookings.status != 'expired';
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $booths = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($booths) {
        $response->getBody()->write(json_encode(["status" => "success", "booths" => $booths]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No booked booths found for this user"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
