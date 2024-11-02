<?php
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

// การตั้งค่าแสดงข้อผิดพลาดและการเชื่อมต่อ CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ตั้งค่า Options preflight request
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->post('/booth-details', function (Request $request, Response $response) {
    $servername = "151.106.124.154";
    $username = "u583789277_wag19";
    $password = "2567Inspire";
    $dbname = "u583789277_wag19";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $data = json_decode($request->getBody()->getContents(), true);
    $booth_id = $data['booth_id'] ?? null;

    if (!$booth_id) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "booth_id is required"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // SQL query
    $sql = "SELECT * FROM booth WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booth_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booth = $result->fetch_assoc();
        $response->getBody()->write(json_encode(["status" => "success", "booth" => $booth]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Booth not found"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $stmt->close();
    $conn->close();
});

$app->run();
