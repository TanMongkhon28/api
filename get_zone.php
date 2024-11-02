<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// ตั้งค่า CORS
$app->add(function (Request $request, Response $response, callable $next) {
    $response = $response->withHeader('Access-Control-Allow-Origin', '*')
                         ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                         ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    if ($request->getMethod() === 'OPTIONS') {
        return $response;
    }
    return $next($request, $response);
});

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

// Route สำหรับดึงข้อมูลโซนจากตาราง `zones`
$app->get('/zones', function (Request $request, Response $response) {
    $pdo = getConnection();

    // SQL query เพื่อดึงข้อมูลโซน
    $sql = "SELECT id, zone_name, zone_description, num_booths FROM zones";
    $stmt = $pdo->query($sql);
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($zones) {
        $response->getBody()->write(json_encode(["status" => "success", "zones" => $zones]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "No zones found"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run Slim App
$app->run();
