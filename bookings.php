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

// Route สำหรับเพิ่มการจอง
$app->post('/book', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $pdo = getConnection();

    // ตรวจสอบข้อมูลที่ต้องการ
    $requiredFields = ['user_id', 'booth_id', 'event_id'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Required field $field is missing"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $user_id = $data['user_id'];
    $booth_id = $data['booth_id'];
    $event_id = $data['event_id'];
    $details = $data['details'] ?? '';

    try {
        // ตรวจสอบว่าบูธนี้ถูกจองหรือยัง
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booth_id = ? AND status != 'cancelled'");
        $stmt->execute([$booth_id]);
        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Booth already booked"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        // ตรวจสอบจำนวนการจองของผู้ใช้
        $stmt = $pdo->prepare("SELECT COUNT(*) AS booth_count FROM bookings WHERE user_id = ? AND status != 'cancelled' AND status != 'expired'");
        $stmt->execute([$user_id]);
        $booth_count = $stmt->fetchColumn();
        if ($booth_count >= 4) {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "You can only book up to 4 booths"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // ตรวจสอบสถานะบูธ
        $stmt = $pdo->prepare("SELECT status, zone_id, price FROM booth WHERE id = ?");
        $stmt->execute([$booth_id]);
        $boothData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($boothData['status'] == 'pending') {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Booth is currently in pending status and cannot be booked"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // ตรวจสอบ zone_id
        $zone_id = $boothData['zone_id'];
        $price = $boothData['price'];
        $stmt = $pdo->prepare("SELECT id FROM zones WHERE id = ?");
        $stmt->execute([$zone_id]);
        if ($stmt->rowCount() == 0) {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Invalid zone_id"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // ดึงข้อมูลวันที่เริ่มและสิ้นสุดงาน
        $stmt = $pdo->prepare("SELECT event_start_date, event_end_date FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $eventData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$eventData) {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Invalid event_id"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // คำนวณวันที่ต้องชำระเงินก่อนวันงาน 5 วัน
        $payment_due_date = date('Y-m-d', strtotime($eventData['event_start_date'] . ' - 5 days'));

        // เพิ่มการจองในตาราง bookings
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, booth_id, zone_id, event_id, price, booking_date, details, status, payment_due_date) 
                               VALUES (?, ?, ?, ?, ?, CURDATE(), ?, 'pending', ?)");
        $stmt->execute([$user_id, $booth_id, $zone_id, $event_id, $price, $details, $payment_due_date]);

        // เพิ่มข้อมูลการชำระเงิน
        $booking_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO payment (booking_id, payment_date, amount, payment_status) VALUES (?, CURDATE(), ?, '')");
        $stmt->execute([$booking_id, $price]);

        $response->getBody()->write(json_encode(["status" => "success", "message" => "Booking and payment added successfully", "payment_due_date" => $payment_due_date]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Run app
$app->run();
