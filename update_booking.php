<?php
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

// Middleware เพื่อจัดการ CORS
$app->add(function (Request $request, Response $response, $next) {
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    return $next($request, $response);
});

// จัดการ OPTIONS request
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

// Route สำหรับอัปเดตสถานะการจอง
$app->post('/update-booking', function (Request $request, Response $response) {
    $servername = "151.106.124.154";
    $username = "u583789277_wag19";
    $password = "2567Inspire";
    $dbname = "u583789277_wag19";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $errorResponse = ["status" => "error", "message" => "Connection failed: " . $conn->connect_error];
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $data = json_decode($request->getBody()->getContents(), true);
    $booking_id = $data['booking_id'] ?? null;
    $status = $data['status'] ?? null;

    if (!$booking_id || !$status) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Required fields are missing"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        if ($status === 'cancelled') {
            $updatePaymentSQL = "UPDATE payment SET payment_status = 'failed' WHERE booking_id = ?";
            $stmtUpdatePayment = $conn->prepare($updatePaymentSQL);
            if (!$stmtUpdatePayment) {
                $response->getBody()->write(json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
            $stmtUpdatePayment->bind_param("i", $booking_id);
            if ($stmtUpdatePayment->execute()) {
                $response->getBody()->write(json_encode(["status" => "success", "message" => "Booking and payment status updated successfully"]));
            } else {
                $response->getBody()->write(json_encode(["status" => "error", "message" => "Error updating payment: " . $stmtUpdatePayment->error]));
            }
            $stmtUpdatePayment->close();
        } else {
            $response->getBody()->write(json_encode(["status" => "success", "message" => "Booking status updated successfully"]));
        }
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Error updating booking: " . $stmt->error]));
    }

    $stmt->close();
    $conn->close();
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
