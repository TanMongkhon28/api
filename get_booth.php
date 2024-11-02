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

// Route สำหรับดึงข้อมูลบูธทั้งหมด
$app->get('/booths', function (Request $request, Response $response) {
    $pdo = getConnection();

    $sql = "SELECT * FROM booth";
    $stmt = $pdo->query($sql);
    $booths = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($booths) {
        $response->getBody()->write(json_encode(["status" => "success", "booths" => $booths]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No booths found"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run Slim App
$app->run();
