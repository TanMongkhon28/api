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

// Route สำหรับอัปเดตข้อมูลบูธ
$app->put('/update-booth', function (Request $request, Response $response) {
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

    $id = $data['id'] ?? null;
    $booth_name = $data['booth_name'] ?? null;
    $booth_size = $data['booth_size'] ?? null;
    $status = $data['status'] ?? null;
    $price = $data['price'] ?? null;
    $image_url = $data['image_url'] ?? null;
    $zone_id = $data['zone_id'] ?? null;

    if (!$id || !$booth_name || !$booth_size || !$status || !$price || !$zone_id) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Required fields are missing or invalid"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $sql = "UPDATE booth SET booth_name=?, booth_size=?, status=?, price=?, image_url=?, zone_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "SQL preparation failed: " . $conn->error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt->bind_param("sssisis", $booth_name, $booth_size, $status, $price, $image_url, $zone_id, $id);

    if ($stmt->execute()) {
        $response->getBody()->write(json_encode(["status" => "success", "message" => "Booth information updated successfully"]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Error updating booth information: " . $stmt->error]));
    }

    $stmt->close();
    $conn->close();

    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
