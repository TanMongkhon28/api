<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// Middleware เพื่อจัดการกับ CORS Headers
$app->add(function (Request $request, Response $response, $next) {
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    if ($request->getMethod() === 'OPTIONS') {
        return $response;
    }
    return $next($request, $response);
});

// Route สำหรับอัปเดต payment slip
$app->post('/update-payment-slip', function (Request $request, Response $response) {
    $servername = "151.106.124.154";
    $username = "u583789277_wag19";
    $password = "2567Inspire";
    $dbname = "u583789277_wag19";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        $data = ["status" => "error", "message" => "Connection failed: " . $conn->connect_error];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // ดึงข้อมูลจาก POST Request
    $data = $request->getParsedBody();
    $booking_id = $data['booking_id'] ?? null;

    if (!$booking_id || !isset($_FILES['payment_slip'])) {
        $data = ["status" => "error", "message" => "Required fields are missing"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบสถานะของการจอง
    $stmt = $conn->prepare("SELECT status FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        $data = ["status" => "error", "message" => "Booking not found"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    
    if ($booking['status'] === 'expired' || $booking['status'] === 'cancelled') {
        $data = ["status" => "error", "message" => "Cannot make a payment for a booking that is expired or cancelled"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // จัดการการอัปโหลดไฟล์
    $target_dir = __DIR__ . "/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $uploadedFile = $_FILES['payment_slip'];
    $target_file = $target_dir . basename($uploadedFile["name"]);

    if (move_uploaded_file($uploadedFile["tmp_name"], $target_file)) {
        $payment_slip_url = "https://yourdomain.com/uploads/" . basename($uploadedFile["name"]);
    } else {
        $data = ["status" => "error", "message" => "Error uploading file"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $stmt = $conn->prepare("UPDATE payment SET payment_slip = ?, payment_status = 'pending' WHERE booking_id = ?");
    if (!$stmt) {
        $data = ["status" => "error", "message" => "SQL error: " . $conn->error];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt->bind_param("si", $payment_slip_url, $booking_id);
    if ($stmt->execute()) {
        $data = ["status" => "success", "message" => "Payment slip updated successfully"];
    } else {
        $data = ["status" => "error", "message" => "Error updating payment: " . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// เริ่มต้น Slim App
$app->run();
