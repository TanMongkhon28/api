<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// เริ่มการทำงานของ Slim App
require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// ตั้งค่า CORS Headers
$app->add(function (Request $request, Response $response, $next) {
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
    if ($request->getMethod() === 'OPTIONS') {
        return $response;
    }
    return $next($request, $response);
});

// Route สำหรับอัปเดตสถานะการชำระเงิน
$app->put('/update-payment-status', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    $booking_id = $data['booking_id'] ?? null;

    if (!$booking_id) {
        return $response->withJson(["status" => "error", "message" => "Booking ID is required"], 400);
    }

    // เชื่อมต่อฐานข้อมูล
    $servername = "151.106.124.154";
    $username = "u583789277_wag19";
    $password = "2567Inspire";
    $dbname = "u583789277_wag19";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        return $response->withJson(["status" => "error", "message" => "Connection failed: " . $conn->connect_error], 500);
    }

    // อัปเดตสถานะการชำระเงินเป็น 'failed'
    $sql = "UPDATE payment SET payment_status = 'failed' WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return $response->withJson(["status" => "error", "message" => "SQL error: " . $conn->error], 500);
    }

    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        $responseArray = ["status" => "success", "message" => "Payment status updated to failed"];
    } else {
        $responseArray = ["status" => "error", "message" => "Error updating payment status: " . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    // ส่ง JSON กลับไปยัง Client
    $response->getBody()->write(json_encode($responseArray));
    return $response->withHeader('Content-Type', 'application/json');
});

// เริ่มต้น Slim App
$app->run();
