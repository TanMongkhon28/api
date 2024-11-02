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
                         ->withHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                         ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    if ($request->getMethod() === 'OPTIONS') {
        return $response;
    }
    return $next($request, $response);
});

// ฟังก์ชันสำหรับเชื่อมต่อฐานข้อมูลด้วย PDO
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

// Route สำหรับเพิ่มข้อมูลบูธ
$app->post('/add-booth', function (Request $request, Response $response) {
    $pdo = getConnection();

    // อ่านข้อมูล JSON จากคำขอ
    $data = $request->getParsedBody();

    // ตรวจสอบว่ามีข้อมูลครบถ้วน
    $requiredFields = ['booth_name', 'booth_size', 'status', 'price', 'zone_id'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "$field is required"]));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    // เตรียม SQL สำหรับการเพิ่มข้อมูลบูธ
    $sql = "INSERT INTO booth (booth_name, booth_size, status, price, image_url, zone_id) 
            VALUES (:booth_name, :booth_size, :status, :price, :image_url, :zone_id)";
    $stmt = $pdo->prepare($sql);

    // เพิ่มข้อมูลบูธ
    try {
        $stmt->execute([
            ':booth_name' => $data['booth_name'],
            ':booth_size' => $data['booth_size'],
            ':status' => $data['status'],
            ':price' => $data['price'],
            ':image_url' => $data['image_url'] ?? null,
            ':zone_id' => $data['zone_id']
        ]);
        $response->getBody()->write(json_encode(["status" => "success", "message" => "Booth information added successfully"]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Error adding booth information: " . $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run Slim App
$app->run();
