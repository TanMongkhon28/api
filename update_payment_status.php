<?php
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

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

$app->put('/update-payment-status', function (Request $request, Response $response) {
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

    $data = json_decode($request->getBody()->getContents(), true);

    if (!isset($data['booking_id'], $data['payment_status'])) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Missing required fields"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $booking_id = $data['booking_id'];
    $payment_status = $data['payment_status'];

    $stmt = $conn->prepare("UPDATE payment SET payment_status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $payment_status, $booking_id);

    if ($stmt->execute()) {
        $response->getBody()->write(json_encode(["status" => "success", "message" => "Payment status updated successfully"]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Failed to update payment status: " . $stmt->error]));
    }

    $stmt->close();
    $conn->close();

    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
