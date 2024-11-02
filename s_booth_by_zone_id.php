<?php
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

// CORS Middleware
$app->add(function (Request $request, Response $response, $next) {
    $response = $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    return $next($request, $response);
});

// การจัดการ Options preflight
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

// Route สำหรับดึงข้อมูลบูธโดยใช้ zone_id
$app->post('/get-booths', function (Request $request, Response $response) {
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
    $zone_id = $data['zone_id'] ?? null;

    if (!$zone_id || !is_numeric($zone_id)) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Invalid or missing zone_id"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $sql = "SELECT * FROM booth WHERE zone_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Failed to prepare SQL: " . $conn->error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt->bind_param("i", $zone_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booths = $result->fetch_all(MYSQLI_ASSOC);
        $response->getBody()->write(json_encode(["status" => "success", "booths" => $booths]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No booths found for this zone"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $stmt->close();
    $conn->close();
});

$app->run();
