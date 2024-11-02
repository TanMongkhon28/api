<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// ฟังก์ชันการเชื่อมต่อฐานข้อมูล
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

// CORS middleware
$app->add(function (Request $request, Response $response, callable $next) {
    $response = $response->withHeader('Access-Control-Allow-Origin', '*')
                         ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
                         ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
                         
    if ($request->getMethod() === 'OPTIONS') {
        return $response;
    }

    return $next($request, $response);
});

// Route สำหรับดึงข้อมูล Event
$app->get('/events', function (Request $request, Response $response) {
    $pdo = getConnection();

    $sql = "SELECT event_id, event_name, event_start_date, event_end_date FROM events";
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($events) {
        $response->getBody()->write(json_encode(["status" => "success", "events" => $events]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No events found"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run Slim App
$app->run();
