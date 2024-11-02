<?php
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// เริ่มต้นแอปพลิเคชัน Slim
$app = AppFactory::create();

// กำหนดเส้นทาง API สำหรับการเข้าสู่ระบบ
$app->post('/login', function (Request $request, Response $response) {
    $servername = "151.106.124.154";
    $username = "u583789277_wag19";
    $password = "2567Inspire";
    $dbname = "u583789277_wag19";

    // เชื่อมต่อฐานข้อมูล
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // อ่านข้อมูล JSON ที่ส่งมา
    $data = json_decode($request->getBody()->getContents(), true);
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$email || !$password) {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Email and password are required"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบผู้ใช้ในฐานข้อมูล
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $response->getBody()->write(json_encode(["status" => "success", "message" => "Login successful", "user" => $user]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["status" => "error", "message" => "Invalid password"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    } else {
        $response->getBody()->write(json_encode(["status" => "error", "message" => "Email not found"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

// เริ่มต้นใช้งาน Slim
$app->run();
