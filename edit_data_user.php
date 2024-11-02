<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PDO;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// ฟังก์ชันสำหรับสร้างการเชื่อมต่อ PDO
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

// Route สำหรับการอัปเดตข้อมูลผู้ใช้
$app->post('/update-user', function (Request $request, Response $response) {
    $pdo = getConnection();
    $data = $request->getParsedBody();

    $user_id = $data['user_id'] ?? null;
    $prefix = $data['prefix'] ?? null;
    $name = $data['name'] ?? null;
    $lastname = $data['lastname'] ?? null;
    $phone = $data['phone'] ?? null;
    $email = $data['email'] ?? null;
    $new_password = $data['new_password'] ?? null;
    $confirm_password = $data['confirm_password'] ?? null;

    if (!$user_id || !$prefix || !$name || !$lastname || !$phone || !$email) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Required fields are missing"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่ามีอีเมลนี้อยู่ในฐานข้อมูลแล้วหรือไม่
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
    $stmt->execute(['email' => $email, 'user_id' => $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "This email is already in use"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // เตรียมคำสั่ง SQL สำหรับการอัปเดตข้อมูลผู้ใช้
    if ($new_password && $confirm_password) {
        if ($new_password !== $confirm_password) {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Passwords do not match"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET prefix = :prefix, name = :name, lastname = :lastname, phone = :phone, email = :email, password = :password WHERE id = :user_id";
        $params = [
            'prefix' => $prefix,
            'name' => $name,
            'lastname' => $lastname,
            'phone' => $phone,
            'email' => $email,
            'password' => $hashedPassword,
            'user_id' => $user_id
        ];
    } else {
        $sql = "UPDATE users SET prefix = :prefix, name = :name, lastname = :lastname, phone = :phone, email = :email WHERE id = :user_id";
        $params = [
            'prefix' => $prefix,
            'name' => $name,
            'lastname' => $lastname,
            'phone' => $phone,
            'email' => $email,
            'user_id' => $user_id
        ];
    }

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($params)) {
        $response->getBody()->write(json_encode(["status" => "success", "message" => "User information updated successfully"]));
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Error updating user information"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
