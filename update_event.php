<?php
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

// Middleware สำหรับจัดการ CORS
$app->add(function (Request $request, Response $response, $next) {
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    return $next($request, $response);
});

// Route สำหรับอัปเดตข้อมูล event
$app->put('/update-event', function (Request $request, Response $response) {
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

    $event_id = $data['event_id'] ?? null;
    $event_name = $data['event_name'] ?? null;
    $event_start_date = $data['event_start_date'] ?? null;
    $event_end_date = $data['event_end_date'] ?? null;

    if (!$event_id || !$event_name || !$event_start_date || !$event_end_date) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Missing required fields"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $conn->prepare("UPDATE events SET event_name = ?, event_start_date = ?, event_end_date = ? WHERE event_id = ?");
    $stmt->bind_param("sssi", $event_name, $event_start_date, $event_end_date, $event_id);

    if ($stmt->execute()) {
        $message = ($stmt->affected_rows > 0) ? "Event updated successfully" : "No event found with the given ID";
        $status = ($stmt->affected_rows > 0) ? "success" : "error";
        $response->getBody()->write(json_encode(["status" => $status, "message" => $message]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Failed to update event"]));
    }

    $stmt->close();
    $conn->close();

    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
