<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// ฟังก์ชันเชื่อมต่อฐานข้อมูลด้วย PDO
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

// CORS Middleware
$app->add(function (Request $request, Response $response, callable $next) {
    $response = $response->withHeader('Access-Control-Allow-Origin', '*')
                         ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
                         ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if ($request->getMethod() === 'OPTIONS') {
        return $response;
    }

    return $next($request, $response);
});

// Route สำหรับดึงข้อมูลสมาชิก
$app->get('/members', function (Request $request, Response $response) {
    $pdo = getConnection();

    $sql = "SELECT id, prefix, name, lastname, phone, email FROM users";
    $stmt = $pdo->query($sql);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($members) {
        $response->getBody()->write(json_encode(["status" => "success", "members" => $members]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No members found"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run Slim App
$app->run();
